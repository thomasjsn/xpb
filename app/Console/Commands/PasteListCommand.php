<?php

namespace App\Console\Commands;

use App\Paste;
use Illuminate\Console\Command;

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
                    'ttl' => ! is_null($paste->ttl) ? $paste->ttl->diffInDays() : '-',
                    'retention' => ! is_null($paste->ttl) ? $paste->retention->diffInDays() : '-',
                    'timestamp' => $paste->timestamp->diffForHumans(),
                    'hits' => $paste->hits
                ];
            } 
        }

        $headers = ['Hash', 'Length', 'Size', 'MIME', 'TTL (d)', 'Retention', 'Timestamp', 'Hits'];
        $this->table($headers, $pastes);
    }
}
