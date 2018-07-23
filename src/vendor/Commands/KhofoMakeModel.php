<?php

namespace Khofo\vendor\Commands;

class KhofoMakeModel extends KhofoCommands
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Khofo:make:model
    {name : model name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $module_name;

    public function self_construct()
    {
        $this->module_name = $this->choice('For Which Module ?', $this->modulesName());
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try {
            if (!$this->super_construct()) {
                return false;
            }
            $this->self_construct();

            if (file_exists($this->module_path)) {

                $this->comment("[" . $this->_name . '] model already exist !');
                return false;
            }
            if ($this->createFile()) {

                $this->info('<options=bold;fg=cyan>[' . $this->_name . ']<bg=black;fg=cyan> controller created successfully');
            }
        } catch (Exception $e) {

            $this->error('somthing went wrong !');
        }
    }

    private function createFile()
    {
        $path = $this->_src . $this->module_name . '/Database/Models/' . $this->_Uname . '.php';
        if (file_exists($path)) {

            $this->comment("[ " . $this->_Uname . ' ] controller already exist !');
            return false;
        }
        $content = $this->generateModel($this->module_name);

        $this->makeFile($path, $content);

        return true;
    }

}
