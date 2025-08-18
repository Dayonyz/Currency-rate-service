<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class K6StressTestService
{
    /**
     * @param array $tokens
     * @param array $pageSizes
     * @param string $baseUrl
     * @param int $rps
     * @param string $filename
     */
    public static function generateStressTestFile(
        array $tokens,
        array $pageSizes,
        string $baseUrl = 'http://localhost:8080',
        int $rps = 1500,
        string $filename = 'load_test.js'
    ): void {
        $tokensJson = json_encode($tokens, JSON_PRETTY_PRINT);
        $pageSizesJson = json_encode($pageSizes, JSON_PRETTY_PRINT);

        $content = <<<JS
import http from 'k6/http';
import { sleep } from 'k6';
import { check } from 'k6';

const tokens = $tokensJson;

const sizes = $pageSizesJson;

export const options = {
    stages: [
        { duration: '1m', target: $rps },
        { duration: '5m', target: $rps },
        { duration: '1m', target: 0 },
    ],
    thresholds: {
         http_req_duration: ['p(90)<3000'],
    },
};

export default function () {
    let token = tokens[Math.floor(Math.random() * tokens.length)];
    
    const item = sizes[Math.floor(Math.random() * sizes.length)];
    const page = Math.floor(Math.random() * item.maxPage) + 1;
    
    let res1 = http.get("{$baseUrl}/api/currency/rate/EUR/USD", {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer \${token}`
        },
    });
    
    check(res1, { 'status is 200': (r) => r.status === 200 });
    
    let res2 = http.get(`{$baseUrl}/api/currency/rates/EUR/USD/\${item.size}/1`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer \${token}`
            },
        });
    
    check(res2, { 'status is 200': (r) => r.status === 200 });
        
    sleep(0.01);
    
    let res3 = http.get("{$baseUrl}/api/currency/rate/EUR/USD", {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer \${token}`
        },
    });
    
    check(res3, { 'status is 200': (r) => r.status === 200 });
    
    let res4 = http.get(`{$baseUrl}/api/currency/rates/EUR/USD/\${item.size}/\${page}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer \${token}`
            },
        });
    
    check(res4, { 'status is 200': (r) => r.status === 200 });
}
JS;

        File::put(base_path($filename), $content);
    }
}
