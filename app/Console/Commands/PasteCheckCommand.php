<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class PasteCheckCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "paste:check";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check for expired content files";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = scandir(storage_path('app'));

        foreach ($files as $file)
        {
            if (!in_array($file, ['.', '..', '.gitignore'])) {
                if (Redis::exists($file)) {
                    $this->info('Found metadata for content file: ' . $file);
                } else {
                    $this->error('Content expired, deleting file: ' . $file);
                    unlink(storage_path('app/'.$file));
                }
            } 
        }
    }
}
