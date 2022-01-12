<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ApiKeyAdd::class,
        Commands\ApiKeyDelete::class,
        Commands\ApiKeyEdit::class,
        Commands\ApiKeyList::class,
        Commands\PasteCheckCommand::class,
        Commands\PasteDeleteCommand::class,
        Commands\PasteListCommand::class,
        Commands\UrlDeleteCommand::class,
        Commands\UrlListCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('paste:check')->daily();
    }
}
