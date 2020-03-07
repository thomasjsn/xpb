<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class StatsController extends Controller
{
    public function index()
    {
        $dates = [
            date('Y-m', strtotime('0 month')),
            date('Y-m', strtotime('-1 month')),
            date('Y-m', strtotime('-2 month')),
        ];

        $traffic = [];
        foreach($dates as $date) {
            $traffic[$date] = Helper::formatBytes(Redis::zscore('sys:traffic', $date));
        }

        $ignoreCount = 0;
        foreach (config('xpb.keys') as $sysKey) {
            if (Redis::exists($sysKey)) $ignoreCount++;
        }

        $stats = [
            'paste_count' => Redis::dbsize() - $ignoreCount,
            'link_count' => Redis::hlen('sys:shorturl'),
            'used_keys' => Redis::scard('sys:hashid'),
            'traffic' => $traffic
        ];

        $content = json_encode($stats, JSON_PRETTY_PRINT);
        $syntax = 'json';

        return response(view('paste', compact('content', 'syntax')));
    }
}