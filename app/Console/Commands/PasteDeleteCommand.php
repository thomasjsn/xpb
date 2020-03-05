<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class PasteDeleteCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "paste:del {hash : Hash key to delete} {--release : Release hash key}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete paste";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hash = $this->argument('hash');

        if (file_exists(storage_path('app/'.$hash))) {
            if(unlink(storage_path('app/'.$hash))) {
                $this->info('Content file deleted.');
            }
        }

        if(Redis::del($hash)) {
            $this->info('Paste metadata deleted.');
        }

        if($this->option('release') && Redis::srem('meta:hashid', $hash)) {
            $this->info('Hash key released.');
        }
    }
}
