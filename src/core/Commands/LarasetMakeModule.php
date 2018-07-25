<?php

namespace Khofaai\Laraset\core\Commands;

use Laraset,File;

class LarasetMakeModule extends LarasetCommands
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'laraset:make:module
	{name : module name} 
	{--no-sync=default : to not synchronize global router with this components router}
	{--tpl=default : to create own blade template for this module}
	{--with-admin=default : generate elements for admin interface}
	{--model=default : if model is need with creation set name for it} 
	{--migrate=default : if migration is need with creation set name for it}';
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'creating module';
	/**
	 * module name first caractere Uppercase
	 * 
	 * @var String
	 */
	protected $moduleNameToUpper;
	/**
	 * Init command options
	 * 
	 * @return void
	 */
	protected function _construct() 
	{
		$this->setCommandOption(['no-sync','tpl','with-admin','model','migrate']);
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
			$this->_construct();
			
			if (file_exists($this->modulePath)) {
				
				$this->comment("[".$this->moduleName.'] directory already exist !');
				return false;
			}
			$this->moduleNameToUpper = ucfirst($this->moduleName);
			$this->generateModule();

			$this->info('<options=bold;fg=cyan>['.$this->moduleName.']<bg=black;fg=cyan> package created successfully');
		} catch (Exception $e) {

			$this->error('somthing went wrong !');
		}
	}
	/**
	 * generate Directories/Files & update data
	 * 
	 * @return void
	 */
	protected function generateModule() 
	{
		$this->updateModules();
		$this->generateDirectories();
		$this->generateFiles();
		$this->updateWebpackMix();
	}
	/**
	 * Generate all Directories for module
	 * 
	 * @return void
	 */
	protected function generateDirectories() 
	{
		$directories = [ 'build', 'Components', 'Components/app', 'Components/admin' ];
	
		if ($this->getOption('with-admin')) {
			$directories[] = 'Components/admin';
		}

		$directories[] = 'Controllers';
		$directories[] = 'Database';
		$directories[] = 'Database/Models';
		$directories[] = 'Database/Migrations';

		$this->makeDirectories($directories);

		$this->comment('[directories] : finished');
	}
	/**
	 * format data to array
	 * 
	 * @param  String $filename
	 * @param  String $stub
	 * @param  Array $toReplace
	 * @param  Array $replaceWith
	 * @return void
	 */
	protected function fileData($filename,$stub ,$toReplace,$replaceWith) {
		return [
			"filename" => $filename,
			"stub" => $stub,
			"to-replace" => $toReplace,
			"replace-with" => $replaceWith
		];
	}
	/**
	 * Generate Files needed for module
	 *
	 * @return void
	 */
	protected function generateFiles() 
	{
		$modName = $this->moduleName;
		$files = [
			$this->fileData("app.js", "js/app.js", [ 'DumpModuleName'] ,[ $modName ] ),
			$this->fileData("route.js", "js/route.js.module", [ 'DumpModuleNameUpper','DumpModuleName' ], [ ucfirst($modName), strtolower($modName) ]),
			$this->fileData('Components/app/'.$modName.".vue","js/vuejs/component.vue", ['DumpModuleName'],[ $modName ])
		];

		if ($this->getOption('with-admin')) {
			$files[] =  $this->fileData('Components/admin/admin_'.$modName.".vue","js/vuejs/component.vue", [ 'DumpModuleName' ],[ $modName ]);
		}

		$files[] =  $this->fileData('Controllers/'.$modNameToUpper."php","controller", [ 'DumpModuleName', 'DumpName' ], [ $modName, $modName ]);
		$files[] =  $this->fileData('route.php',"route.php.module", [ 'DumpModuleName' ], [ strtolower($modName) ]);

		$this->makeModuleFiles($files);
		
		$option_tpl = $this->getOption('tpl');
		if ($option_tpl && !file_exists(resource_path('views/'.$option_tpl.'.blade.php'))) {
			$this->makeFile(resource_path('views/'.$option_tpl.'.blade.php'),$this->getStubFileContent('template.blade'));
		}
	}
	/**
	 * update webpack mix file
	 * 
	 * @return void
	 */
	protected function updateWebpackMix() {
	   
		$path = $this->basePath.'webpack.mix.js';
		$js = file($path);

		foreach ($js as $key => $elem) {
			$el = trim(str_replace(' ', '', $elem));
			if (Laraset::checkStrPos($el,'module.exports=[')){
				$modl_line = "\t{src:'modules/".$this->moduleName."/app.js',build:'modules/".$this->moduleName."/build',enable:".$this->moduleEnabled()."},\n";
				array_splice( $js, $key+1, 0, $modl_line );
			}
		}

		$this->makeFile($path, $js);
	}
	/**
	 * check if module enabled
	 * 
	 * @return String
	 */
	protected function moduleEnabled() 
	{
		return is_null($this->getOption('no-sync')) ? 'true' : ( !$this->getOption('no-sync') ? 'false' : 'true' );
	}
	/**
	 * update core.json modules
	 * 
	 * @return void
	 */
	protected function updateModules() 
	{
		$path = Laraset::base('core.json');

		$modules = json_decode(File::get($path),true);

		$modules['modules'][$this->moduleName] = [
			'installed' => true,
			'enabled' => $this->moduleEnabled() == "true" ? true : false,
			'created_at' => date('Y-m-d H:i:s')
		];

		$this->makeFile($path, json_encode((Object)$modules, JSON_PRETTY_PRINT));
	}
	/**	
	 * generate files
	 * 
	 * @param  Array $targets
	 * @return void
	 */
	protected function makeModuleFiles($targets) 
	{
		foreach ($targets as $key => $target) {
			
			$filename = ($target['filename'] == '' ? $target['stub'] : $target['filename']);
			$content = $this->getStubFileContent($target['stub']);
	        $content = str_replace($target['to-replace'],$target['replace-with'], $content);
			$path = $this->modulePath.'/'.$filename;

			$this->makeFile($path,$content);
		}
	}
}