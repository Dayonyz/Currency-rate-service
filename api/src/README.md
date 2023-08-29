# Currency Rates Service

## Backend Deploy

```
cd api
make build
make chmod
make env
make ssh

composer install --prefer-dist --no-dev -o
php artisan migrate
php artisan storage:link
php artisan db:seed
php artisan app:fetch-currency-rates --currency=EUR --base=USD --M=04 --Y=2023
```

After DB seeding you can find test Bearer token
```
src/storage/app/public/test-plain-token.txt
```

### Optional
Feel free to register at [Open Exchange Rates](https://openexchangerates.org) to get own APP ID and paste it into .env
```
OER_KEY_ID=6462db151ba245988cbe9a5dfc6e7172
```
Disabling or Enabling repository cache in .env
```
REPOSITORY_CACHE=true
```
Run scheduler for daily update EUR/USD rate

```
php artisan schedule:work
```

Test User credentials
```
test@example.com
password
```
Available host

[http://127.0.0.1:8080](http://127.0.0.1:8080)

Stress testing
- Exit form container (exit)
- Install utility [Tsung](http://tsung.erlang-projects.org/user_manual/index.html)

```
cd api/src
tsung -f stress-test.xml -l ./tsung start 
cd tsung/{report_dir}
/opt/local/lib/tsung/bin/tsung_stats.pl
```

Optimization

- Apcu Opcache php extensions used
- Removing unused services and web routes
- Repository caching
- throttle:100000,1
- php artisan optimize

Tsung Reports

api/src/tsung/20230828-2028/report.html

api/src/tsung/20230828-2209/report.html