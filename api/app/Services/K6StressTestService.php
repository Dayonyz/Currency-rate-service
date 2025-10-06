<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class K6StressTestService
{
    /**
     * @param array $tokens
     * @param array $pageSizes
     ** @param string $baseUrl
     * @param int $vus
     * @param string $filename
     */
    public static function generateStressTestFile(
        array $tokens,
        array $pageSizes,
        string $baseUrl = 'http://127.0.0.1:8080',
        int $vus = 1800,
        string $filename = 'load_test.js'
    ): void {
        $tokensJson = json_encode($tokens, JSON_PRETTY_PRINT);
        $pageSizesJson = json_encode($pageSizes, JSON_PRETTY_PRINT);

        $content = <<<JS
import http from 'k6/http';
import { sleep, group, check } from 'k6';
import { vu } from 'k6/execution';

const tokens = $tokensJson;
const sizes = $pageSizesJson;

export const options = {
    scenarios: {
        contacts: {
            executor: 'ramping-vus',
            startVUs: 0,
            stages: [
                { duration: '1m', target: $vus },
                { duration: '5m', target: $vus },
                { duration: '1m', target: 0 },
            ],
            gracefulRampDown: '30s',
        },
    },
    thresholds: {
         http_req_duration: ['p(90)<3000'],
         checks: ['rate>0.95'],
    },
};

export default function () {
    const tokenIndex = (vu.idInTest + vu.iterationInScenario) % tokens.length;
    const token = tokens[tokenIndex];
    
    const item = sizes[Math.floor(Math.random() * sizes.length)];
    const page = Math.floor(Math.random() * item.maxPage) + 1;
    const startPaginator = sizes[0];
    
    const headers = {
        'Accept': 'application/json',
        'Authorization': `Bearer \${token}`
    };
    
        group('User Journey - Login', function () {
        let res1 = http.get(`{$baseUrl}/api/currency/rate/EUR/USD`, { headers });
        check(res1, { 'User Journey - Login: GET current rate status is 200': (r) => r.status === 200 });

        let res2 = http.get(`{$baseUrl}/api/currency/rates/EUR/USD/\${startPaginator.size}/1`, { headers });
        check(res2, { 'User Journey - Login: GET rates first paginator, page 1 status is 200': (r) => r.status === 200 });
    });
    
    sleep(0.2);
    
    group('User Journey - Random paginator: first page', function () {
        let res3 = http.get(`{$baseUrl}/api/currency/rate/EUR/USD`, { headers });
        check(res3, { 'User Journey - Random paginator: GET current rate status is 200': (r) => r.status === 200 });

        let res4 = http.get(`{$baseUrl}/api/currency/rates/EUR/USD/\${item.size}/1`, { headers });
        check(res4, { 'User Journey - Random paginator: GET rates random paginator, page 1 status is 200': (r) => r.status === 200 });
    });
    
    sleep(0.2);

    group('User Journey - Random paginator: random page', function () {
        let res5 = http.get(`{$baseUrl}/api/currency/rate/EUR/USD`, { headers });
        check(res5, { 'User Journey - Random paginator: GET current rate status is 200': (r) => r.status === 200 });

        let res6 = http.get(`{$baseUrl}/api/currency/rates/EUR/USD/\${item.size}/\${page}`, { headers });
        check(res6, { 'User Journey - Random paginator: GET rates random paginator, random page status is 200': (r) => r.status === 200 });
    });
    
    sleep(0.02);
}
JS;

        File::put(base_path($filename), $content);
    }
}
