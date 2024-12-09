<?php

namespace App\Console\Commands;

use App\Services\ScrapeFitExpert;
use Illuminate\Console\Command;

class ScrapeWorkouts extends Command
{
    /**
     * The ScrapeMealsFitExpert service instance.
     *
     * @var ScrapeFitExpert
     */
    protected $scraper;

    /**
     * Create a new command instance.
     *
     * @param ScrapeFitExpert $scraper
     * @return void
     */
    public function __construct(ScrapeFitExpert $scraper)
    {
        parent::__construct();

        $this->scraper = $scraper;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-workouts';

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
        $this->scraper->scrapeWorkouts();
        $this->info('Workouts have been scraped and saved successfully.');
    }
}
