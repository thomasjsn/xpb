<?php

namespace App\Console\Commands;

use App\Paste;
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

        foreach ($files as $hash)
        {
            $hash = str_replace('!', '/', $hash);

            if (! in_array($hash, ['.', '..', '.gitignore'])) {
                if (! Redis::exists($hash)) {
                    $results = Paste::cleanup($hash);

                    $this->info(
                        sprintf('%s expired, deleted: %s', $hash, implode(', ', array_keys($results)))
                    );
                }
            } 
        }

    }
}
