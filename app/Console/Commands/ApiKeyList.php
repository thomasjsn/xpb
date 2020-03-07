<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class ApiKeyList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apikey:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all API keys';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hgetall = Redis::hgetall('sys:apikey');

        $apikeys = array_map(function ($key, $comment) {
            return ['key' => $key, 'comment' => $comment];
        }, array_keys($hgetall), $hgetall);

        $this->table(['API key', 'Comment'], $apikeys);
    }

}

