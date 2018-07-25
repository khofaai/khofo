<?php

namespace Khofaai\Laraset\core\Commands;

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
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $moduleNameToUpper;

	public function __construct() 
	{	
		parent::__construct();
	}
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
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() 
	{	
		try {
			if (!$this->super_construct()) {
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

	protected function generateModule() 
	{
		$this->updateModules();
		$this->generateDirectories();
		$this->generateFiles();

		$this->updateWebpackMix();
	}

	protected function generateDirectories() 
	{
		$this->makeDirectory();
		$this->makeDirectory('build');
		$this->makeDirectory('Components');
		$this->makeDirectory('Components/app');
	
		if ($this->getOption('with-admin')) {
			$this->makeDirectory('Components/admin');
		}

		$this->makeDirectory('Controllers');
		$this->makeDirectory('Database');
		$this->makeDirectory('Database/Models');
		$this->makeDirectory('Database/Migrations');
	}

	protected function generateFiles() 
	{
		$this->makeJsFile('app');
		$this->makeJsFile('route');
		$this->makeVueFile();
		$this->makeControllerFile();
		$this->makeRouteFile();
		
		$option_tpl = $this->getOption('tpl');

		if ($option_tpl && !file_exists(resource_path('views/'.$option_tpl.'.blade.php'))) {
			$this->makeFile(resource_path('views/'.$option_tpl.'.blade.php'),$this->getStubFileContent('template.blade'));
		}
	}
   
	protected function checkStrPos($el,$str) 
	{
		return $el != '' && strpos($el,$str) !== false;
	}

	protected function updateWebpackMix() {
	   
		$path = $this->basePath.'webpack.mix.js';
		$js = file($path);

		foreach ($js as $key => $elem) {
			$el = trim(str_replace(' ', '', $elem));

			if ($this->checkStrPos($el,'module.exports=[')){
				$enable = $this->moduleEnabled();
				$modl_line = "\t{src:'modules/".$this->moduleName."/app.js',build:'modules/".$this->moduleName."/build',enable:".$enable."},\n";
				array_splice( $js, $key+1, 0, $modl_line );
			}
		}

		file_put_contents($path, $js);
	}

	protected function moduleEnabled() 
	{
		return is_null($this->getOption('no-sync')) ? 'true' : ( !$this->getOption('no-sync') ? 'false' : 'true' );
	}

	protected function updateModules() 
	{
		$path = laraset_base('core.json');

		$modules = json_decode(file_get_contents($path),true);

		$modules['modules'][$this->moduleName] = [
			'installed' => true,
			'enabled' => $this->moduleEnabled() == "true" ? true : false,
			'created_at' => date('Y-m-d H:i:s')
		];

		file_put_contents($path, json_encode((Object)$modules, JSON_PRETTY_PRINT));
	}

	protected function makeModuleFile($target,$fileName = '',$ext = 'js') 
	{	
		$filename = ($fileName == '' ? $target : $fileName);
		
		$path = $this->modulePath.'/'.$filename.'.'.$ext;
		$content = $this->{$target.'Content'}();
		
		$this->makeFile($path,$content);
	}

	protected function makeJsFile($target,$fileName = '') 
	{	
		$this->makeModuleFile($target,$fileName);
	}

	protected function makeVueFile() 
	{	
		$this->makeModuleFile('Components','Components/app/'.$this->moduleName,'vue');
		
		if ($this->getOption('with-admin')) {
			$this->makeModuleFile('Components','Components/admin/admin_'.$this->moduleName,'vue');
		}
	}

	protected function makeControllerFile() 
	{	
		$this->makeModuleFile('Controllers','Controllers/'.$this->moduleNameToUpper.'Controller','php');
	}

	protected function makeRouteFile() 
	{	
		$this->makeFile($this->modulePath.'/routes.php',$this->phpRouteContent());
	}

	protected function appContent() 
	{	
		return  "import router from '../../routes';\n"
				."\nrequire('../../core');\n"
				."/**\n"
				."* Next, we will create a fresh Vue application instance and attach it to\n"
				."* the page. Then, you may begin adding components to this application\n"
				."* or customize the JavaScript scaffolding to fit your unique needs.\n"
				."*/\n\n"
				."Vue.component('".$this->moduleName."', require('./Components/app/".$this->moduleName.".vue'));\n\n"
				."const app = new Vue({\n"
				."	el: '#".$this->moduleName."',\n"
				."	router\n"
				."});\n";
	}

	protected function routeContent() 
	{
		$content =  "import ".$this->moduleNameToUpper." from './Components/app/".$this->moduleName."';\n";

		if ($this->getOption('with-admin')) {
			$content .= "import Admin".$this->moduleNameToUpper." from './Components/app/".$this->moduleName."';\n";
		}

		$content .= "export default [\n"
					."	{\n"
					."		path:'/".strtolower($this->moduleName)."',\n"
					."		component:".$this->moduleNameToUpper."\n"
					."	},\n";

		if ($this->getOption('with-admin')) {
			$content .=		"	{\n"
							."		path:'/admin/".$this->moduleName."',\n"
							."		component: Admin".$this->moduleNameToUpper."\n"
							."	}\n";
		}
		$content .= "];\n";

		return $content;
	}

	protected function ComponentsContent() 
	{
		return  "<template>\n"
				."    <div class='container'>\n"
				."        <div class='row'>\n"
				."            <div class='col-md-8 col-md-offset-2'>\n"
				."                <div class='panel panel-default'>\n"
				."                    <div class='panel-heading'>".$this->moduleName." Component</div>\n\n"
				."                    <div class='panel-body'>\n"
				."                        I'm an ".$this->moduleName." component!\n"
				."                    </div>\n"
				."                </div>\n"
				."            </div>\n"
				."        </div>\n"
				."    </div>\n"
				."</template>\n"

				."<script>\n"
				."    export default {\n"
				."        mounted() {\n"
				."            console.log('Component mounted.')\n"
				."        }\n"
				."    }\n"
				."</script>";
	}

	protected function ControllersContent() 
	{	
		return  $this->generateController($this->moduleNameToUpper);
	}

	protected function phpRouteContent() {
		return  "<?php\n\n"
				."Route::get('".strtolower($this->moduleName)."',function() {\n"
				."\tdd('".$this->moduleName." component routes');\n"
				."});\n"
				."// set your routes here";
	}
}