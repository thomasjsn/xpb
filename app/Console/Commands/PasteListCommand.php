<?php

namespace App\Console\Commands;

use App\Paste;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class PasteListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "paste:list";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "List all pasts";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = scandir(storage_path('app'));
        $pastes = [];

        foreach ($files as $hash)
        {
            if (! in_array($hash, ['.', '..', '.gitignore'])) {
                $paste = Paste::find(str_replace('!', '/', $hash));

                if (is_null($paste)) {
                    continue;
                }

                $pastes[] = [
                    'hash' => $paste->hash,
                    'length' => $paste->length,
                    'size' => $paste->size,
                    'mime' => $paste->mime ?? '-',
                    'ttl' => round(Redis::ttl($paste->hash) / (3600*24), 1),
                    'retention' => round($paste->ttl / (3600*24), 1),
                    'timestamp' => Carbon::createFromTimestamp($paste->timestamp)->diffForHumans(),
                    'hits' => $paste->hits
                ];
            } 
        }

        $headers = ['Hash', 'Length', 'Size', 'MIME', 'TTL (d)', 'Retention', 'Timestamp', 'Hits'];
        $this->table($headers, $pastes);
    }
}
