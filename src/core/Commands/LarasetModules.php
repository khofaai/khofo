<?php

namespace Khofaai\Laraset\core\Commands;

use Laraset;

class LarasetModules extends LarasetCommands {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laraset:modules';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'list all modules';
    
    /**
     * @inheritdoc
     */
    public function handle()
    {
        $modules = [];

        foreach (Laraset::modules() as $key => $module) {
            $modules[] = [
                "name" => $key,
                "installed" => $module['installed'] ? 'yes' : 'no',
                "created date" => $module['created_at']
            ];
        }

        $this->table(['name','installed','created date'],$modules);
    }
}
