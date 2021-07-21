<?php

namespace App\Console\Commands;

use App\Draw;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StopDraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stop-draw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop draw';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $draw = Draw::where('date_finish', '<', date('Y-m-d H:i:s'))->where('published_at', '<>', '')->get();


        $logTxt = print_r($draw, true);

        Log::channel('test')->info($logTxt);
    }
}
