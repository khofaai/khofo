<?php

namespace Khofaai\Laraset\core\Commands;

use Illuminate\Console\Command;

use Laraset;
use File;

abstract class LarasetCommands extends Command
{
	/**
	 * This Core name
	 * 
	 * @var string
	 */
	protected $coreNamespace = "laraset";
	/**
	 * project base path 
	 * 
	 * @var String
	 */
	protected $basePath;
	/**
	 * [$modulePath description]
	 * @var Strnig
	 */
	protected $modulePath;
	/**
	 * contain all command options
	 * @var array
	 */
	protected $commandOptions = [];
	/**
	 * Module name
	 * @var String
	 */
	protected $moduleName;
	/**
	 * Module name with first Letter uppercase
	 * @var String
	 */
	protected $moduleNameUpper;
	/**
	 * modules folder base path
	 * @var String
	 */
	protected $baseSrc;

	public function __construct() 
	{
		$this->basePath = app_path($this->coreNamespace).'/';
		parent::__construct();
	}
	/**
	 * Execute the console command.
	 */
	abstract function handle();

	/**
	 * Init module name in case is allowed
	 * 
	 * @return Boolean
	 */
	protected function init() 
	{	
        $this->initName();
        if (in_array(strtolower($this->moduleName), $this->notAllowedNames())) {
            $this->info('<options=bold;fg=yellow>['.strtolower($this->moduleName).']<bg=black;fg=yellow> name is reserved ! please choose another one');
            return false;
        }
        return true;
	}

	/**
	 * Create File
	 * 
	 * @param  String $path
	 * @param  String $content
	 * @return void
	 */
	protected function makeFile($path,$content) 
	{
		File::put($path,$content);
	}

	/**
	 * return not allowed names
	 * 
	 * @return Array
	 */
	protected function notAllowedNames()
	{
		return [
			'admin'
		];
	}

	/**
	 * Initial module name
	 * 
	 * @return void
	 */
	protected function initName() 
	{
		$this->moduleName = $this->formatName($this->argument('name'));
		$this->moduleNameUpper = ucfirst(camel_case($this->moduleName));
		
		$this->baseSrc =  $this->basePath.'modules/';
		$this->modulePath = $this->baseSrc.$this->moduleName;
	}

	/**
	 * set option name to $commandOptions
	 * 
	 * @param String $option
	 * @return void
	 */
	protected function setOption($option) 
	{
		$optionValue = $this->option($option);
		$this->commandOptions[$option] = ($optionValue == 'default' ? false : (is_null($optionValue) ? true : $optionValue));
	}

	/**
	 * get option value
	 * 
	 * @param  String $option option name
	 * @return Boolean/String ( String in case the option has value )
	 */
	protected function getOption($option)
	{
		return $this->commandOptions[$option];
	}

	/**
	 * Set all command options to $commandOptions
	 * 
	 * @param Array/String $option ( Array in case multiple options )
	 */
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

	/**
	 * Remplace - with _
	 * 
	 * @param  String $name
	 * @return String
	 */
	protected function formatName($name) 
	{
		return str_replace('-', '_', $name); 
	}

	/**
	 * Create Directory
	 * 
	 * @param  String $path
	 * @return void
	 */
	protected function makeDir($path) 
	{
		File::makeDirectory( $path, 0777, true, true );
	}

	/**
	 * Create directories
	 *
	 * @param  array $directories
	 * @return void
	 */
	protected function makeDirectories($directories) 
	{
		$this->makeDir($this->modulePath);
		foreach ($directories as $directory) {
			$this->makeDir($this->modulePath . '/' . $directory);
		}
	}

	/**
	 * get Laraset Modules name
	 * 
	 * @return qrray
	 */
	protected function modulesName() 
	{
		return array_keys(Laraset::modules());
	}

	/**
	 * get Instance Signature
	 * 
	 * @return string
	 */
	public function getSignature() 
	{
		return $this->signature;
	}

	/**
	 * Get instance description
	 *
	 * @return string
	 */
	public function getDescription() 
	{
		return $this->description;
	}

	/**
	 * Get given stub file name
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function getStubFileContent($name) 
	{
		return File::get(Laraset::getStub($name));
	}
}
