<?php

namespace Khofo\vendor\Commands;

class KhofoDelete extends KhofoCommands
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'Khofo:delete';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'delete module';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	
	protected $path;
    protected $module_name;

	public function __construct() {
		
		parent::__construct();
	}

	public function self_construct() {
		$modules = $this->modulesName();
		if ($modules) {

			$this->module_name = $this->choice('For Which Module ?',$this->modulesName());
			return true;
		} else {
			$this->error('no module is created yet !');
			return false;
		}
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		
		if( $this->self_construct() ) {

			if (!$this->pathExist()) {

				if ($this->confirm($this->module_name)) {
					$this->extractModule();
					$this->comment('<options=bold;fg=yellow>['.$this->module_name.'] package deleted successfully');

					$this->deleteDir($this->path);
				}
			} else {
				
				$this->error("[".$this->module_name."] module does't exist");
			}
		}


	}

	private function pathExist() {

		$this->path = khofo_base('modules/'.$this->module_name);

		return !is_dir($this->path);
	}

	private function extractModule() {

		$this->extractModuleFromWebpack(khofo_base('webpack.mix.js'));
		$this->extractModuleFromCore();
	}
	
	public function checkStrPos($el,$str) {
		
		return $el != '' && strpos($el,$str) !== false;
	}

	public function extractModuleFromWebpack($path) {

		$js = file($path);
		foreach ($js as $key => $elem) {
			
			$el = $this->stripeElemet($elem);

			if ($this->checkStrPos($el,'/'.$this->module_name.'/')){
				
				unset($js[$key]);
			}
		}

		file_put_contents($path, $js);
	}

	private function stripeElemet($element) {
		
		return trim(str_replace(' ', '', $element));
	}

	public function extractModuleFromCore() {
		
		$path = khofo_base('core.json');

		$modules = json_decode(file_get_contents($path),true);

		if (isset($modules['modules'][$this->module_name])) {
			
			unset($modules['modules'][$this->module_name]);
		}

		file_put_contents($path, json_encode((Object)$modules, JSON_PRETTY_PRINT));
	}

	public function deleteDir($path) {
	
		if (!is_dir($path)) {
			
			throw new InvalidArgumentException("message");
		}
		
		$path = str_finish($path,'/');

		$files = glob($path.'*',GLOB_MARK);

		foreach ($files as $file) {
			
			if (is_dir($file)) {
			
				Self::deleteDir($file);
			} else {
			
				unlink($file);
			}
		}

		rmdir($path);
	}
}