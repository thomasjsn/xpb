<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ApiKeyDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apikey:del
                            {api_key : API key to remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete specified API key';

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

        Redis::hdel('sys:apikey', $key);

        $this->info($key . ' removed');
    }

}

