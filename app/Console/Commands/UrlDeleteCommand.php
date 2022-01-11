<?php

namespace App\Console\Commands;

use App\ShortUrl;
use Illuminate\Console\Command;

class UrlDeleteCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "url:del
                            {hash : Hash key to delete}
                            {--release : Release hash key}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete URL";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hash = $this->argument('hash');

        $shortUrl = ShortUrl::find($hash);
        $results = $shortUrl->delete($this->option('release'));

        $this->info('Deleted: ' . implode(', ', array_keys($results)));
    }
}
