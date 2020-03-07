<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ApiKeyAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apikey:add
                            {comment : Key description or owner}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and add new API key';

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
        $key = bin2hex(random_bytes(16));

        Redis::hset('sys:apikey', $key, $this->argument('comment'));

        $this->info($key);
    }

}

