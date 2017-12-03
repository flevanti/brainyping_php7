<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB as DB;

class scr extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scr {filename : php file to run} {--verb : info about the script to run} {args?* : optional arguments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run custom script in a bootstrapped environment';

    /**
     * The string used to split key/value for arguments
     *
     * @var string
     */
    protected $arguments_delimiter = '=';

    /**
     * Base folder for custom scripts to include.
     * This will force all scripts to be in the same folder.
     *
     * @var string
     */
    protected $base_scripts_location = "App/Scripts";

    /**
     * provide information about the script we want to run
     *
     * @var bool
     */
    protected $verb = false;

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
        $this->verb = $this->option('verb');
        $this->e("Welcome to run script command!");
        $args = $this->getSplittedArguments();
        $filename = $this->argument('filename');
        $filename_full_path = base_path($this->base_scripts_location . DIRECTORY_SEPARATOR . $filename);
        $this->e("So you want to run $filename with these arguments!!!");
        $this->e(print_r($args, true), "line");
        $this->e("Script full path is $filename_full_path");

        $this->printMemInfo();

        if (file_exists($filename_full_path)) {

            $this->e("Including script $filename_full_path");

            require($filename_full_path);

            $this->e("Script completed its mission!");

        } else {
            $this->e("Script $filename_full_path not found", "error");
        }
        $this->printMemInfo();

        $this->e("Bye");


    }

    /**
     * Return arguments splitted using defined delimiter
     *
     * If delimiter is found array element is key[delimiter]value
     * If delimiter is not found array element is argument=true
     *
     * only first delimiter found is used to split argument string
     *
     * @return array
     */
    protected function getSplittedArguments()
    {
        $args = [];
        foreach ($this->argument('args') as $argument) {
            $ret = explode($this->arguments_delimiter, $argument, 2);
            $args[$ret[0]] = $ret[1] ?? true;
        }
        return $args;
    }

    /**
     * Output some statistics about memory
     */
    protected function printMemInfo()
    {
        if (!$this->verb) {
            return;
        }
        $mem = round((memory_get_usage(true) / 1024 / 1024), 2);
        $mem_peak = round((memory_get_peak_usage(true) / 1024 / 1024), 2);
        $this->e("Some info about memory...");
        $this->e("memory usage is $mem MB");
        $this->e("memory peak usage is $mem_peak MB");
    }

    /**
     * Output text on screen base on verb and the level
     *
     * @param $txt
     * @param null $level
     */
    protected function e($txt, $level = null)
    {
        $level = $level ?? 'info';
        if ($this->verb || $level == 'error')

            switch (true) {
                case ($level == 'error'):
                    $this->error($txt);
                    break;
                case ($level == 'warning'):
                    $this->warn($txt);
                    break;
                case ($level == 'info'):
                    $this->info($txt);
                    break;
                default:
                    $this->line($txt);
            }
    }
}
