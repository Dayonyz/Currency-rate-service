<?php

namespace App\Auth;

use App\Jobs\TokenUpdateLastUsedAtJob;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Events\TokenAuthenticated;
use Laravel\Sanctum\Guard;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\TransientToken;

class SanctumCacheGuard extends Guard
{
    public function __construct(AuthFactory $auth, $expiration = null, $provider = null)
    {
        parent::__construct($auth, $expiration, $provider);
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request): mixed
    {
        foreach (Arr::wrap(config('sanctum.guard', 'web')) as $guard) {
            if ($user = $this->auth->guard($guard)->user()) {
                return $this->supportsTokens($user)
                    ? $user->withAccessToken(new TransientToken)
                    : $user;
            }
        }

        if ($token = $this->getTokenFromRequest($request)) {
            $model = Sanctum::$personalAccessTokenModel;

            $accessToken = $model::findToken($token);

            if (! $this->isValidAccessToken($accessToken) ||
                ! $this->supportsTokens($accessToken->tokenable)) {
                return null;
            }

            event(new TokenAuthenticated($accessToken));

            TokenUpdateLastUsedAtJob::dispatch($accessToken->getRawOriginal(), now()->toDateTimeString());

            return $accessToken->tokenable->withAccessToken(
                $accessToken
            );
        }

        return null;
    }
}
