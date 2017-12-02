<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $args = $this->getSplittedArguments();
        $this->line(
          "So you want to run ".$this->argument(
            'filename'
          )." with these arguments!!!"
        );
        $this->line(print_r($args, true));
        $mem = memory_get_usage(true) / 1024 / 1024;
        $mem_peak = memory_get_peak_usage(true) / 1024 / 1024;
        $this->warn("memory usage is $mem MB");
        $this->warn("memory peak usage is $mem_peak MB");

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

}
