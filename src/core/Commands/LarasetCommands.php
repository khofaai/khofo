<?php

namespace Khofaai\Laraset\core\Commands;

use Illuminate\Console\Command;

use Laraset;
use File;

class LarasetCommands extends Command
{
	protected $coreNamespace = "laraset";
	protected $basePath;
	protected $modulePath;
	protected $commandOptions;
	protected $moduleName;
	protected $moduleNameUpper;
	protected $baseSrc;

	public function __construct() 
	{
		$this->basePath = app_path($this->coreNamespace).'/';
		parent::__construct();
	}

	protected function super_construct() 
	{	
        $this->initName();

        if ( in_array( strtolower($this->moduleName), $this->notAllowedNames() ) ) {
            
            $this->info('<options=bold;fg=yellow>['.strtolower($this->moduleName).']<bg=black;fg=yellow> name is reserved ! please choose another one');
            return false;
        }

        return true;
	}

	protected function makeFile($path,$content) 
	{
		File::put($path,$content);
	}

	protected function notAllowedNames() 
	{
		return [
			'admin'
		];
	}

	protected function initName() 
	{
		$this->moduleName = $this->formatName($this->argument('name'));
		$this->moduleNameUpper = ucfirst(camel_case($this->moduleName));
		
		$this->baseSrc =  $this->basePath.'modules/';
		$this->modulePath = $this->baseSrc.$this->moduleName;
	}

	protected function setOption($option) 
	{
		$option_val = $this->option($option);
		$this->commandOptions[$option] = $option_val == 'default' ? false : (is_null($option_val) ? true : $option_val);
	}

	protected function getOption($option)
	{
		return $this->commandOptions[$option];
	}

	protected function setCommandOption($option) 
	{
		if (is_array($option)) {
			foreach ($option as $opt) {
				$this->setOption($opt);
			}
		} else {
			$this->setOption($opt);
		}
	}

	protected function formatName($name) 
	{
		return str_replace('-', '_', $name); 
	}

	protected function makeDirectory($directory = '') 
	{
		File::makeDirectory(
			$this->modulePath.($directory == '' ? '' : '/'.$directory),
			0777,
			true,
			true
		);
	}

	protected function modulesName() 
	{
		return array_keys(Laraset::modules());
	}

	public function getSignature() 
	{
		return $this->signature;
	}

	public function getDescription() 
	{
		return $this->description;
	}

	protected function getStubFileContent($name) 
	{
		return File::read(Laraset::getStub($name));
	}
}