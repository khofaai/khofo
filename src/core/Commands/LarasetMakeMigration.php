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
	 * selected module name
	 * 
	 * @var String
	 */
	protected $selectedModule;
	/**
	 * Class name
	 * 
	 * @var String
	 */
	protected $className;

	/**
	 * init $className & $selectedModule values
	 * 
	 * @return [type] [description]
	 */
    protected function _construct() 
    {
        $this->selectedModule = $this->choice('For Which Module ?',$this->modulesName());
		$this->className = 'Create'.ucfirst($this->moduleName).'Table';
    }

	/**
	 * @inheritdoc
	 */
	public function handle() 
	{	
		if (!$this->init()) {
            return false;
        }
		$this->_construct();
		$modulePath = Laraset::modulePath($this->selectedModule);
		if (!class_exists_in_directory($modulePath.'/Database/Migrations/',$this->className)) {
			$migration_class = date('Y_m_d_his').'_create_'.$this->moduleName.'_table.php';
			$content = 	str_replace(
							['DumpMigrationName','DumpTableName','DumpTableName'], 
							[$this->className,$this->moduleName,$this->moduleName], 
							$this->getStubFileContent('migration')
						);
			$this->makeFile($modulePath.'/Database/Migrations/'.$migration_class,$content);
			$this->info('<options=bold;fg=green>['.$this->className.']<bg=black;fg=green>  migration created successfully !');
		} else {
			
			$this->warn($this->className.' class already Exist');
		}
	}
}