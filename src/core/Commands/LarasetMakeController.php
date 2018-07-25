<?php

namespace Khofaai\Laraset\core\Commands;

use File;

class LarasetMakeController extends LarasetCommands {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laraset:make:controller
    {name : controller name}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new Controller for you Laraset module';
    /**
     * selected module name
     * 
     * @var String
     */
    protected $selectedModule;
    
    /**
     * set selected module name
     * 
     * @return String
     */
    private function initSelectedModuleName() 
    {
        $this->selectedModule = $this->choice('For Which Module ?',$this->modulesName());
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
            $this->initSelectedModuleName();
            if (file_exists($this->modulePath)) {
                $this->comment("[".$this->moduleName.'] controller already exist !');
                return false;
            }
            if ($this->createFile()) {
                $this->info('<options=bold;fg=cyan>['.$this->moduleName.']<bg=black;fg=cyan> controller created successfully');
            }
        } catch (Exception $e) {
            $this->error('something went wrong !');
        }
    }
    /**
     * Create Controller File
     * 
     * @return Boolean
     */
    private function createFile() 
    {
        $path = $this->baseSrc.$this->selectedModule.'/Controllers/'.ucfirst($this->moduleName).'.php';
        if (File::exists($path)) {
            $this->comment("[ ".$this->moduleName.' ] controller already exist !');
            return false;
        }
        $content = str_replace(['DumpModuleName','DumpName'], [$this->selectedModule,$this->moduleName], $this->getStubFileContent('controller'));
        $this->makeFile($path,$content);
        return true;
    }
}
