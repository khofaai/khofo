<?php

namespace Khofo\vendor\Commands;

use Illuminate\Console\Command;

class KhofoCommands extends Command
{
	protected $core_namespace = "Khofo";
	protected $base_path;
	protected $module_path;
	protected $_options;
	protected $_name;
	protected $_src;

	public function __construct() {
		$this->base_path = app_path('Khofo').'/';
		parent::__construct();
	}

	protected function super_construct() {
		
        $this->initName();

        if ( in_array( strtolower($this->_name), $this->notAllowedNames() ) ) {
            
            $this->info('<options=bold;fg=yellow>['.strtolower($this->_name).']<bg=black;fg=yellow> name is reserved ! please choose another one');
            return false;
        }

        return true;
	}

	protected function makeFile($path,$content) {

		\File::put($path,$content);
		chmod($path, 0777);
	}

	protected function notAllowedNames() {
		return [
			'admin'
		];
	}

	protected function generateController($module_name) {

		return  "<?php\n\n"

				."namespace Khofo\modules\\".$module_name."\Controllers;\n\n"
				."use Khofo\\vendor\Controllers\Controller;\n"
				."use Illuminate\Http\Request;\n\n"

				."class ".ucfirst($this->_name)."Controller extends Controller\n"
				."{\n"
				."    //\n"
				."    public function __construct() {\n"
				."        // #code\n"
				."    }\n\n"

				."    public function index(Request \$request){\n"
				."        // #code\n"
				."    }\n"
				."}";
	}

	protected function generateModel($module_name) {

		return  "<?php\n\n"

				."namespace Khofo\modules\\".$module_name."\Database\Models;\n"
				."use Illuminate\Database\Eloquent\Model;\n\n"

				."class ".ucfirst($this->_name)." extends Model\n"
				."{\n"
				."	//\n"
				."}\n";
	}

	protected function initName() {

		$this->_name = $this->formatName($this->argument('name'));
		$this->_Uname = ucfirst(camel_case($this->_name));
		
		$this->_src =  $this->base_path.'modules/';
		$this->module_path = $this->_src.$this->_name;
	}

	protected function setOption($option) {

		$option_val = $this->option($option);
		$this->_options[$option] = $option_val == 'default' ? false : (is_null($option_val) ? true : $option_val);
	}

	protected function setCommandOption($option) {

		if (is_array($option)) {
				
			foreach ($option as $opt) {
				$this->setOption($opt);
			}
		} else {

			$this->setOption($opt);
		}
	}

	protected function formatName($name) {

		return str_replace('-', '_', $name); 
	}

	protected function makeDirectory($directory = '') {
		
		$src = $this->module_path;
		\File::makeDirectory($src.($directory == '' ? '' : '/'.$directory),0777,true,true);
	}

	protected function modulesName() {

		return array_keys(khofo_modules());
	}

	public function getSignature() {
		return $this->signature;
	}

	public function getDescription() {
		return $this->description;
	}

	protected function CoreTemplateContent() {

		return 	"<!DOCTYPE html>\n"
				."<html>\n"
				."<head>\n"
				."	<title>Khofo Platform</title>\n"
				."	<meta charset='utf-8' name='csrf-token' content='{{ csrf_token(); }}'>\n"
				."	<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>\n"
				."	<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>\n"
				."</head>\n"
				."<body>\n"
				."	<main id='app'>\n"
				."		<router-view></router-view>\n"
				."	</main>\n"
				."	<script type='text/javascript' src='{{ khofo_asset('dist/js/core.js'); }}'></script>\n"
				."</body>\n"
				."</html>";
	}
}