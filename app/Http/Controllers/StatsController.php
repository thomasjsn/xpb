<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class StatsController extends Controller
{
    public function show()
    {
        $traffic = [];
        for ($x = 0; $x >= -2; $x--) {
            $date = date('Y-m', strtotime($x . ' month'));
            $traffic[$date] = Helper::formatBytes(Redis::zscore('sys:traffic', $date));
        }

        $ignoreCount = 0;
        foreach (config('xpb.keys') as $sysKey) {
            if (Redis::exists($sysKey)) $ignoreCount++;
        }

        $stats = [
            'counts' => [
                'pastes' => Redis::dbsize() - $ignoreCount,
                'short_urls' => Redis::hlen('sys:shorturl'),
            ],
            'keys' => [ 'depleted' => Redis::scard('sys:hashid') ],
            'traffic' => $traffic
        ];

        $content = json_encode($stats, JSON_PRETTY_PRINT);
        $syntax = 'json';

        return response(view('paste', compact('content', 'syntax')))
            ->header('X-Robots-Tag', 'noindex');
    }
}
