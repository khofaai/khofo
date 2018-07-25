<?php

namespace Khofaai\Laraset\core\Commands;

use Laraset;
use File;
use Filesystem;

class LarasetMigrate extends LarasetCommands
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'laraset:migrate
	{--sync=default}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'migrate from Core modules';

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
		$this->setCommandOption('sync');
		$this->loadMigrationPaths();
		if ($this->getOption('sync')) {
			$this->mappingMigrationDirectories();
		}
		$this->call('migrate');
	}

	protected function loadMigrationPaths() 
	{
		$corePath = Laraset::base('modules');
		$migration_structure = Laraset::dirStructure($corePath,'Database/Migrations');

		foreach ( $migration_structure['directories'] as $migration ) {
			
			$this->fetchMigrationFiles($migration);
		}
	}

	protected function fetchMigrationFiles($files) {

		foreach ($files['files'] as $file) {
			
			$this->copyFileToMigrationDir($files['path'],$file);
		}
	}

	protected function copyFileToMigrationDir($path,$file) {
		
		File::copy($path.$file,$this->migrationDirectory().'/'.$file);
	}

	protected function migrationDirectory() {

		$migrationDir = database_path('migrations');
		
		if (!is_dir($migrationDir)) {
		
			mkdir($migrationDir);
		}
		return $migrationDir;
	}

    protected function mappingMigrationDirectories() {

        $tree = dir_structure(core_path('modules'),'Database');

        foreach ($tree['directories'] as $element) {

            $path = $element['directories']['Migrations']['path'];
            
            unset($element['directories']['Migrations']['path'],$element['directories']['Migrations']['directories']);
            if (!empty($element['directories']['Migrations']['files'])) {

                $files[$path] = array_values($element['directories']['Migrations']['files']);
            }
        }

        $migration_tree = dir_structure(database_path('migrations'));

        foreach ($migration_tree['files'] as $file) {

            foreach ($files as $path => $local_files) {

                foreach ($local_files as $key => $old_file) {
                    
                    if (strpos($file,$old_file) !== false) {

                        $this->synchFile($path,$old_file);
                    }
                }
            }
        }
    }

    protected function synchFile($path,$filename) {
        
        $migrationPath = database_path('migrations').'/'.$filename;
        $moduleMigrationPath = $path.$filename;
        
        if (md5(file_get_contents($moduleMigrationPath)) != md5(file_get_contents($migrationPath))) {

            if (file_exists($migrationPath)) {
				Filesystem::delete($migrationPath);
            }
            File::copy($moduleMigrationPath,$migrationPath);
            $this->info('   Migration file [ '.$filename.' ] synchronized successfully !');
        }
    }
}