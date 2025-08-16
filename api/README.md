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
- 640 RPS for Redis cache 
```
  █ THRESHOLDS 

    http_req_duration
    ✓ 'p(90)<3000' p(90)=1.33s


  █ TOTAL RESULTS 

    checks_total.......: 269188  640.912905/s
    checks_succeeded...: 100.00% 269188 out of 269188
    checks_failed......: 0.00%   0 out of 269188

    ✓ status is 200

    HTTP
    http_req_duration..............: avg=1.07s min=6.2ms med=1.02s max=9.24s  p(90)=1.33s p(95)=2.12s
      { expected_response:true }...: avg=1.07s min=6.2ms med=1.02s max=9.24s  p(90)=1.33s p(95)=2.12s
    http_req_failed................: 0.00%  0 out of 269188
    http_reqs......................: 269188 640.912905/s

    EXECUTION
    iteration_duration.............: avg=4.29s min=39ms  med=4.16s max=17.74s p(90)=5.87s p(95)=10.1s
    iterations.....................: 67297  160.228226/s
    vus............................: 2      min=2           max=800
    vus_max........................: 800    min=800         max=800

    NETWORK
    data_received..................: 1.2 GB 2.7 MB/s
    data_sent......................: 54 MB  129 kB/s




running (7m00.0s), 000/800 VUs, 67297 complete and 0 interrupted iterations
default ✓ [======================================] 000/800 VUs  7m0s
```
- 806 RPS for Apc cache, but with lower latency 
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



