<?php

namespace App\Console\Commands;

use App\ShortUrl;
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
        $hgetall = Redis::hgetall('sys:shorturl');
        $shortUrls = [];

        foreach ($hgetall as $hash => $url)
        {
            $shortUrl = ShortUrl::Find($hash);

            $shortUrls[] = [
                'hash' => $shortUrl->hash,
                'url' => $shortUrl->content,
                'hits' => $shortUrl->hits
            ];
        }

        $headers = ['Hash', 'URL', 'Hits'];
        $this->table($headers, $shortUrls);
    }
}
