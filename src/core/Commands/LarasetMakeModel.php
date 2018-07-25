<?php

namespace Khofaai\Laraset\core\Commands;

use File;

class LarasetMakeModel extends LarasetCommands {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laraset:make:model
    {name : model name}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function _construct() 
    {
        $this->moduleName = $this->choice('For Which Module ?',$this->modulesName());
    }
    
    /**
     * @inheritdoc
     */
    public function handle()
    {
        try {

            if (!$this->init()) {
                return false;
            }
            $this->_construct();
            
            if (File::exists($this->modulePath)) {
                
                $this->comment("[".$this->moduleName.'] model already exist !');
                return false;
            }
            if($this->createFile()){

                $this->info('<options=bold;fg=cyan>['.$this->moduleName.']<bg=black;fg=cyan> controller created successfully');
            }
        } catch (Exception $e) {

            $this->error('somthing went wrong !');
        }
    }

    private function createFile() {

        $path = $this->_src.$this->moduleName.'/Database/Models/'.$this->moduleNameUpper.'.php';
        
        if (File::exists($path)) {
            $this->comment("[ ".$this->moduleNameUpper.' ] controller already exist !');
            return false;
        }

        $content = $this->getStubFileContent('model');
        $content = str_replace(['DumpModuleName','DumpModelName'], [$this->moduleName,$this->moduleNameUpper], $content);

        $this->makeFile($path,$content);

        return true;
    }
}
