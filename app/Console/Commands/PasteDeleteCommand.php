<?php

namespace App\Console\Commands;

use App\Paste;
use Illuminate\Console\Command;

class PasteDeleteCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "paste:del
                            {hash : Hash key to delete}
                            {--release : Release hash key}";

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

        $paste = Paste::find($hash);
        $results = $paste->delete($this->option('release'));

        $this->info('Deleted: ' . implode(', ', array_keys($results)));
    }
}
