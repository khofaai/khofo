<?php

namespace Khofo\vendor\Commands;

class KhofoCores extends KhofoCommands {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Khofo:cores';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'list all cores';
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
        $modules = [];
        foreach (core_modules() as $key => $module) {
            $modules[] = [
                "name" => $key,
                "installed" => $module['installed'] ? 'yes' : 'no',
                "created date" => $module['created_at']
            ];
        }

        $this->table(['name','installed','created date'],$modules);
    }
}
