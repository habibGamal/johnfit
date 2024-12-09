<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScrapeFitExpert;

class ScrapeMeals extends Command
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
    protected $signature = 'app:scrape-meals';

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
        $this->scraper->scrapeMeals();
        $this->info('Meals have been scraped and saved successfully.');
    }
}
