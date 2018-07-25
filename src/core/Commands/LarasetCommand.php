<?php

namespace Khofaai\Laraset\core\Commands;

use Symfony\Component\Console\Helper\TableSeparator;
use Laraset;

class LarasetCommand extends LarasetCommands {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laraset';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'listing all laraset nodules commands';
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
     * Diplay all existing Modules as Symfony Console Table
     *
     * @return mixed
     */
    public function handle()
    {   
        $directory = Laraset::dirStructure(dirname(__FILE__));
        $files = $directory['files'];
        $commands = [];

        foreach ($files as $file) {
           
            $className = Laraset::getFilePhpClasses($directory['path'].$file)[0];
            $class = 'Khofaai\Laraset\core\Commands\\'.$className;
            
            $instance = new $class();
            $signature = $instance->getSignature();

            if (!is_null($signature)) {

                $signature = explode(PHP_EOL,trim($signature));
                $command =  $signature[0];
                unset( $signature[0] );

                $options = '// no options';

                if (count($signature) > 0) {

                    $options = $signature;
                    $options_str = '';
                    $set_option_label = false;

                    foreach ($options as $opt) {
                        
                        $opt = str_replace('}','',str_replace('{','',trim(str_replace('\t', '', $opt))));
                        $object = explode(':',$opt);
                            
                        if ($options_str != '') { 
                           
                            $options_str .= "\n"; 
                           
                            if (!$set_option_label) {
                                $options_str .= "\nOptions : \n\n";
                                $set_option_label = true;
                            }
                        }
                        
                        if (!isset($object[1])) {
                            $object[1] = ' // no description set';
                        }

                        if (strpos($object[0], '=default') !== false) {
                            $object[0] = explode('=',$object[0]);
                            $object[0] = $object[0][0].' [optional]';
                        }

                        $options_str .= trim($object[0]).' : '.trim($object[1]);
                    }

                    $options = $options_str;
                }
                
                if (count($commands) > 0) {
                    $commands[] = new TableSeparator();
                }

                $commands[] = [
                    'signature' => "\n".$command,
                    'description' => "\n".$instance->getDescription(),
                    'options' => "\n".$options
                ];
            }
        }

        $this->table(['command','description','options'],$commands);
    }
}
