<?php

namespace App\Console\Commands;

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

        foreach ($files as $file)
        {
            if (!in_array($file, ['.', '..', '.gitignore'])) {
                $meta = Redis::get($file);
                $meta_json = json_decode($meta);
                $length = filesize(storage_path('app/'.$file));

                $pastes[] = [
                    'hash' => $file,
                    'length' => $length,
                    'size_mb' => round($length / (1024*1024), 2),
                    'mime' => $meta_json->mime,
                    'ttl_d' => round(Redis::ttl($file) / (3600*24), 1) . " of " . round($meta_json->ttl / (3600*24), 1),
                    'timestamp' => Carbon::createFromTimestamp(filectime(storage_path('app/'.$file)))->diffForHumans(),
                    'hits' => Redis::zscore('meta:visits', $file)
                ];
            } 
        }

        $headers = ['Hash', 'Length', 'Size_MB', 'MIME', 'TTL_d', 'Timestamp', 'Hits'];
        $this->table($headers, $pastes);
    }
}
