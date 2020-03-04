<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UrlListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "url:list";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "List all URLs";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hgetall = Redis::hgetall('urls');

        $urls = array_map(function ($hash, $url) {
            $visits = Redis::zscore('urls:visits', $hash);

            return ['hash' => $hash, 'url' => $url, 'hits' => $visits];
        }, array_keys($hgetall), $hgetall);

        $headers = ['Hash', 'URL', 'Hits'];
        $this->table($headers, $urls);
    }
}
