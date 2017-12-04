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
    protected $signature = 'scr {filename : php file to run} {args?* : optional arguments}';

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
    protected $base_scripts_location = "app/Scripts";

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
        $this->e("Welcome to run script command!");
        $args = $this->getSplittedArguments();
        $filename = $this->argument('filename');
        $filename_full_path = base_path($this->base_scripts_location.DIRECTORY_SEPARATOR.$filename);
        $this->e("So you want to run $filename with these arguments!!!");
        $this->e(json_encode($args), "line");
        $this->e("Script full path is $filename_full_path");

        $this->printMemInfo();

        if (file_exists($filename_full_path)) {

            $this->e("Including script $filename_full_path");
            $this->e("----------------------------------------");
            $script_time_spent = microtime(true);

            $this->includeFileWrapper($filename_full_path, $args);

            $script_time_spent = round(microtime(true) - $script_time_spent, 2);
            $this->e("----------------------------------------");
            $this->e("Script completed its mission!");
            $this->e("It took $script_time_spent seconds");

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
        $mem = round((memory_get_usage(true) / 1024 / 1024), 2);
        $mem_peak = round((memory_get_peak_usage(true) / 1024 / 1024), 2);
        $this->e("Some info about memory...");
        $this->e("memory usage is $mem MB");
        $this->e("memory peak usage is $mem_peak MB");
    }

    /**
     * Output text on screen based on verb and the level
     *
     * @param $txt
     * @param null $level
     */
    protected function e($txt, $level = null)
    {
        if ($this->getOutput()->isVerbose() || $level == 'error') {

            $txt = date("d-m-Y H:i:s")."    $txt";

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

    /**
     * This is just a wrapper to include the file and make sure that no variables could be overwritten
     *
     * @param $filename_full_pat
     * @param $args
     */
    protected function includeFileWrapper($filename_full_path, $args)
    {
        require($filename_full_path);
    }


}
