<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('db:wipe');
        $this->call('migrate');
        $this->call('db:seed');
        $this->info('Database has been refreshed.');
        $this->call('app:scrape-meals');
        $this->call('app:scrape-workouts');
    }
}
