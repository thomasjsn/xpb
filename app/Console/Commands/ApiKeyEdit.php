<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ApiKeyEdit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apikey:edit
                            {api_key : API key to remove}
                            {comment : Key description or owner}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit specified API key';

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
        $key = $this->argument('api_key');

        Redis::hset('sys:apikey', $key, $this->argument('comment'));

        $this->info($key . ' updated');
    }

}

