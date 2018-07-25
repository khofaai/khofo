<?php

namespace Khofaai\Laraset\core\Commands;

use Laraset;

class LarasetMakeMigration extends LarasetCommands
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'laraset:make:migration 
	{name : migration name}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'make migration for specified module';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $module_name;
	protected $className;

	public function __construct() 
	{
		parent::__construct();
	}

    protected function self_construct() 
    {
        $this->module_name = $this->choice('For Which Module ?',$this->modulesName());
		$this->className = 'Create'.ucfirst($this->moduleName).'Table';
    }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() 
	{	
		if (!$this->super_construct()) {
            return false;
        }
		
		$this->self_construct();
		
		$module_path = Laraset::modulePath($this->module_name);
		if (!class_exists_in_directory($module_path.'/Database/Migrations/',$this->className)) {
			
			$migration_class = date('Y_m_d_his').'_create_'.$this->moduleName.'_table.php';
			$content = 	str_replace(
							['DumpMigrationName','DumpTableName','DumpTableName'], 
							[$this->className,$this->moduleName,$this->moduleName], 
							$this->getStubFileContent('migration')
						);
			$this->makeFile($module_path.'/Database/Migrations/'.$migration_class,$content);
			$this->info('<options=bold;fg=green>['.$this->className.']<bg=black;fg=green>  migration created successfully !');
		} else {
			
			$this->warn($this->className.' class already Exist');
		}
	}
}