# Currency Rates Service

## Backend Deploy

```
cd api
make build
make install
```

### There are test credentials in .env, and maybe they are currently invalid.
- Feel free to register at [Open Exchange Rates](https://openexchangerates.org) to get own OER_KEY_ID=

## Test User credentials
```
test0@example.com
password
```

## Optimization

- Redis/APC caches used for repository
- Opcache enabled
- Optimize docker-compose.yml and nginx settings
- Laravel caching: php artisan optimize

## Install before K6 utility on your environment and run for stress-test
```
cd api
k6 run load_test.js
```

### Macbook Pro M1 RAM 16Gb environment, Docker
### The final results that could be achieved with A/B tests was: 
- 838 RPS for Redis cache 
```
   █ THRESHOLDS 

    http_req_duration
    ✓ 'p(90)<3000' p(90)=1.02s


  █ TOTAL RESULTS 

    checks_total.......: 352084  838.285372/s
    checks_succeeded...: 100.00% 352084 out of 352084
    checks_failed......: 0.00%   0 out of 352084

    ✓ status is 200

    HTTP
    http_req_duration..............: avg=817.56ms min=4.79ms  med=912.23ms max=2.21s p(90)=1.02s p(95)=1.07s
      { expected_response:true }...: avg=817.56ms min=4.79ms  med=912.23ms max=2.21s p(90)=1.02s p(95)=1.07s
    http_req_failed................: 0.00%  0 out of 352084
    http_reqs......................: 352084 838.285372/s

    EXECUTION
    iteration_duration.............: avg=3.28s    min=34.68ms med=3.72s    max=5.89s p(90)=3.98s p(95)=4.13s
    iterations.....................: 88021  209.571343/s
    vus............................: 1      min=1           max=800
    vus_max........................: 800    min=800         max=800

    NETWORK
    data_received..................: 1.5 GB 3.6 MB/s
    data_sent......................: 71 MB  168 kB/s




running (7m00.0s), 000/800 VUs, 88021 complete and 0 interrupted iterations
default ✓ [======================================] 000/800 VUs  7m0s

```
- 806 RPS for Apc cache
```
   █ THRESHOLDS 

    http_req_duration
    ✓ 'p(90)<3000' p(90)=1.07s


  █ TOTAL RESULTS 

    checks_total.......: 338672  806.284131/s
    checks_succeeded...: 100.00% 338672 out of 338672
    checks_failed......: 0.00%   0 out of 338672

    ✓ status is 200

    HTTP
    http_req_duration..............: avg=850.21ms min=5.26ms  med=932.92ms max=2.66s p(90)=1.07s p(95)=1.13s
      { expected_response:true }...: avg=850.21ms min=5.26ms  med=932.92ms max=2.66s p(90)=1.07s p(95)=1.13s
    http_req_failed................: 0.00%  0 out of 338672
    http_reqs......................: 338672 806.284131/s

    EXECUTION
    iteration_duration.............: avg=3.41s    min=35.77ms med=3.83s    max=6.21s p(90)=4.15s p(95)=4.31s
    iterations.....................: 84668  201.571033/s
    vus............................: 1      min=1           max=800
    vus_max........................: 800    min=800         max=800

    NETWORK
    data_received..................: 1.4 GB 3.4 MB/s
    data_sent......................: 68 MB  162 kB/s




running (7m00.0s), 000/800 VUs, 84668 complete and 0 interrupted iterations
default ✓ [======================================] 000/800 VUs  7m0s
```



