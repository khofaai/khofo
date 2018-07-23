<?php

namespace Khofo\vendor\Commands;

use Symfony\Component\Console\Helper\TableSeparator;

class KhofoCommand extends KhofoCommands 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'khofo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'listing all khofo core commands';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $directory = dir_structure(dirname(__FILE__));
        $files = $directory['files'];

        $commands = [];

        foreach ($files as $file) {

            $className = file_get_php_classes($directory['path'] . $file)[0];

            $class = 'Khofo\vendor\Commands\\' . $className;

            $instance = new $class();
            $signature = $instance->getSignature();

            if (!is_null($signature)) {

                $signature = explode(PHP_EOL, trim($signature));
                $command = $signature[0];
                unset($signature[0]);

                $options = '// no options';

                if (count($signature) > 0) {

                    $options = $signature;
                    $options_str = '';
                    $set_option_label = false;

                    foreach ($options as $opt) {

                        $opt = str_replace('}', '', str_replace('{', '', trim(str_replace('\t', '', $opt))));
                        $object = explode(':', $opt);

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
                            $object[0] = explode('=', $object[0]);
                            $object[0] = $object[0][0] . ' [optional]';
                        }

                        $options_str .= trim($object[0]) . ' : ' . trim($object[1]);
                    }

                    $options = $options_str;
                }

                if (count($commands) > 0) {

                    $commands[] = new TableSeparator();
                }

                $commands[] = [
                    'signature' => "\n" . $command,
                    'description' => "\n" . $instance->getDescription(),
                    'options' => "\n" . $options
                ];
            }
        }

        $this->table(['command', 'description', 'options'], $commands);
    }
}
