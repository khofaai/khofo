<?php

namespace Khofaai\Laraset\core\Commands;

use Khofaai\Laraset\core\Commands\LarasetCommands;
use Illuminate\Filesystem\Filesystem;
use File,Laraset;

class LarasetInstall extends LarasetCommands
{
    protected $signature = 'laraset:install';
    protected $description = 'install laraset logic';

    protected $messages = [
    	'success' => '<options=bold;fg=cyan>[Laraset]<bg=black;fg=cyan> installed successfully',
    	'warning' => '<options=bold,reverse>~Laraset~<fg=yellow> already installed.'
    ];
    /**
	 * @inheritdoc
	 */
	public function handle() 
	{
		$this->updateInstallStatus();
	}
	/**
	 * @return [type]
	 */
	protected function updateInstallStatus() 
	{
		$path = Laraset::path('core.json');

		if (File::exists($path)) {

			$modules = json_decode(File::get($path),true);

			if (!$modules['installed']["status"] || !isset($modules['installed']["status"])) {

				$this->generateArchitecture();
				$this->generateFiles();
				$this->updateWebpackMixJs();

				$modules['installed']["status"] = true;
				$modules['installed']["installed_at"] = date('Y-m-d H:i:s');
				File::put($path, json_encode((Object)$modules));

				$this->info($this->messages['success']);
			} else {

				$this->info($this->messages['warning']);
				$this->info('//installed at : '.$modules['installed']['installed_at'].'');
			}

		} else {
			
			$this->error('core.json not found');
		}
	}

	protected function generateArchitecture() 
	{
		$architecture = array(
			'assets' => '',
			'dist' => ['js' => ''],
			'helpers' => '' ,
			'modules' => ''
		);

		$path = app_path('laraset');
		$this->createFolder($path);
		
		$this->createArchitectureFolders($architecture,$path);

		$this->info('* [Folders] Generated Successfully !');
	}

	protected function createArchitectureFolders($architecture,$path) 
	{
		foreach ($architecture as $key => $value) {
			$dir_path = $path.'/'.$key;
			$this->createFolder($dir_path);
			
			if (gettype($value) !== 'string') {

				$this->createArchitectureFolders($value,$dir_path);
			}
		}
	}

	protected function createFolder($path) 
	{	
		if (!is_dir($path)) {
			File::makeDirectory($path);
		}
	}

	protected function generateFiles() 
	{
		$files = [
			'/' => 	[
				'bootstrap.js' => 'js/bootstrap.js',
				'core.js' => 'js/core.js',
				'core.json' => 'js/core.json',
				'routes.js' => 'js/route.js',
				'routes.php' => 'route.php',
				'webpack.mix.js' => 'js/webpack.mix.js'
			],
			'helpers' => [
				'helpers.js' => 'js/helpers.js',
				'helpers.php' => 'helpers.php'
			],
			'modules' => [
				'Core.vue' => 'js/vuejs/menu.vue',
				'menu.vue' => 'js/vuejs/core.vue',
				'topbar.vue' => 'js/vuejs/topbar.vue'
			]
		];
		$path = app_path('laraset');
		foreach ($files as $key => $value) {
			foreach ($value as $filename => $stub) {
				$dir_path = $key != '/' ? $path.'/'.$key.'/' : $path.$key;
				$this->makeFile( $dir_path.$filename, $this->getStubFileContent($stub) );
			}
		}
		
		$laraset_path = resource_path('views/laraset.blade.php');
		if (!File::exists($laraset_path)) {
			$this->makeFile($laraset_path,$this->getStubFileContent('template.blade'));
		}

		$this->info('* [File] Generated Successfully !');
	}

	protected function updateWebpackMixJs() {
		
		$path = base_path('webpack.mix.js');

		if (File::exists($path)) {
			$content = File::get($path);
			$this->makeFile(base_path('webpack-old.mix.js'),$content);
			File::delete($path);
		}

		$this->makeFile($path,$this->getStubFileContent('js/webpack.base.mix.js'));
	}
}