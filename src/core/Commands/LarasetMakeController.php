<?php

namespace Khofaai\Laraset\core\Commands;

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
     * Create a new command instance.
     *
     * @return void
     */

    protected $module_name;

    public function __construct()
    {
        parent::__construct();
    }

    private function self_construct() 
    {
        $this->module_name = $this->choice('For Which Module ?',$this->modulesName());
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() 
    {    
        try {

            if ( !$this->super_construct() ) {
                return false;
            }
            $this->self_construct();
            
            if ( file_exists($this->modulePath) ) {
                
                $this->comment("[".$this->moduleName.'] controller already exist !');
                return false;
            }

            if( $this->createFile() ) {

                $this->info('<options=bold;fg=cyan>['.$this->moduleName.']<bg=black;fg=cyan> controller created successfully');
            }
        } catch (Exception $e) {

            $this->error('somthing went wrong !');
        }
    }

    private function createFile() {

        $path = $this->_src.$this->module_name.'/Controllers/'.ucfirst($this->moduleName).'.php';

        if (file_exists($path)) {

            $this->comment("[ ".$this->moduleName.' ] controller already exist !');
            return false;
        }

        $content = file_get_contents(laraset_get_stub('controller'));
        $content = str_replace(['DumpModuleName','DumpName'], [$this->module_name,$this->moduleName], $content);
        $this->makeFile($path,$content);

        return true;
    }
}
