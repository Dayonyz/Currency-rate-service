<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseConstants;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response|JsonResponse|RedirectResponse|ResponseConstants
    {
        if ($e instanceof \InvalidArgumentException) {
            return $this->sendErrorResponse($e->getMessage(), ResponseConstants::HTTP_BAD_REQUEST);
        }

        if ($e instanceof RepositoryNotFoundException) {
            return $this->sendErrorResponse($e->getMessage(), ResponseConstants::HTTP_NOT_FOUND);
        }

        return parent::render($request, $e);
    }

    /**
     * @param  string  $message
     * @param  int  $status
     * @return JsonResponse
     */
    private function sendErrorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => [
                $message
            ],
        ], $status);
    }
}
