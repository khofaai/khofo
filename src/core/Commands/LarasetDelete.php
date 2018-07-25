<?php

namespace Khofaai\Laraset\core\Commands;

use Laraset;
use File;

class LarasetDelete extends LarasetCommands
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'laraset:delete';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'delete module';

	/**
	 * [$path take path to current Module]
	 * @var [type]
	 */
	protected $path;

	/**
	 * init module name
	 * 
	 * @return Boolean
	 */
	public function _construct() 
	{
		$modules = $this->modulesName();
		if ($modules) {
			$this->moduleName = $this->choice('For Which Module ?', $modules);
			return true;
		}
		$this->error('no module is created yet !');
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function handle() 
	{	
		if( $this->_construct() ) {
			if (!$this->pathExist()) {
				if ($this->confirm($this->moduleName)) {
					$this->extractModule();
					$this->comment('<options=bold;fg=yellow>['.$this->moduleName.'] package deleted successfully');
					$this->deleteDir($this->path);
				}
			} else {
				$this->error("[".$this->moduleName."] module does't exist");
			}
		}
	}
	/**
	 * Check if current Module exists
	 * 
	 * @return Boolean
	 */
	protected function pathExist() 
	{
		$this->path = Laraset::base('modules/'.$this->moduleName);

		return !is_dir($this->path);
	}
	/**
	 * Remove the current Module from webpack config
	 * 
	 * @return void
	 */
	protected function extractModule() 
	{
		$this->extractModuleFromWebpack(Laraset::base('webpack.mix.js'));
		$this->extractModuleFromCore();
	}
	/**
	 * update webpack.mix.js after removing current Module
	 * 
	 * @param  String
	 * @return void
	 */
	protected function extractModuleFromWebpack($path) 
	{
		$js = file($path);
		foreach ($js as $key => $elem) {
			$el = $this->stripeElemet($elem);
			if ( Laraset::checkStrPos($el,'/'.$this->moduleName.'/') ) {
				unset($js[$key]);
			}
		}
		File::put($path, $js);
	}
	/**
	 * remove Spaces from a given string
	 * 
	 * @param  String
	 * @return String
	 */
	protected function stripeElemet($element) 
	{	
		return trim(str_replace(' ', '', $element));
	}
	/**
	 * remove Current Module from Core.json
	 * 
	 * @return void
	 */
	protected function extractModuleFromCore() 
	{	
		$path = Laraset::base('core.json');
		$modules = json_decode(File::get($path),true);

		if (isset($modules['modules'][$this->moduleName])) {
			unset($modules['modules'][$this->moduleName]);
		}

		File::put($path, json_encode((Object)$modules));
	}
	/**
	 * delete Current Module Directory
	 * 
	 * @param  String
	 * @return void
	 * @throws InvalidArgumentException if no directory exist
	 */
	protected function deleteDir($path) 
	{
		if (!is_dir($path)) {		
			throw new InvalidArgumentException("message");
		}
		
		$path = str_finish($path,'/');
		$files = File::glob($path.'*',GLOB_MARK);

		foreach ($files as $file) {
			if (is_dir($file)) {
				Self::deleteDir($file);
			} else {
				File::delete($file);
			}
		}
		File::deleteDirectory($path);
	}
}