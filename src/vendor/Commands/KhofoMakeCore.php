<?php

namespace Khofo\vendor\Commands;

class KhofoMakeCore extends KhofoCommands
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Khofo:make:core
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
    protected $description = 'creating core';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $_Uname;
    protected $_model;
    protected $_migrate;

    private function self_construct() {

        $this->setCommandOption(['no-sync', 'tpl', 'with-admin', 'model', 'migrate']);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        try {

            if (!$this->super_construct()) {
                return false;
            }
            $this->self_construct();

            if (file_exists($this->module_path)) {

                $this->comment("[" . $this->_name . '] directory already exist !');
                return false;
            }

            $this->generateModule();

            $this->info('<options=bold;fg=cyan>[' . $this->_name . ']<bg=black;fg=cyan> package created successfully');
        } catch (Exception $e) {

            $this->error('somthing went wrong !');
        }
    }

    private function generateModule() {

        $this->updateModules();
        $this->generateDirectories();
        $this->generateFiles();

        $this->updateWebpackMix();
    }

    private function generateDirectories() {

        $this->makeDirectory();
        $this->makeDirectory('build');
        $this->makeDirectory('Components');
        $this->makeDirectory('Components/app');

        if ($this->_options['with-admin']) {
            $this->makeDirectory('Components/admin');
        }

        $this->makeDirectory('Controllers');
        $this->makeDirectory('Database');
        $this->makeDirectory('Database/Models');
        $this->makeDirectory('Database/Migrations');
    }

    private function generateFiles() {

        $this->makeJsFile('app');
        $this->makeJsFile('route');
        $this->makeVueFile();
        $this->makeControllerFile();
        $this->makeRouteFile();

        if ($this->_options['tpl'] && !file_exists(resource_path('views/' . $this->_options['tpl'] . '.blade.php'))) {
            $this->makeFile(resource_path('views/' . $this->_options['tpl'] . '.blade.php'), $this->coreTemplateContent());
        }
    }

    public function checkStrPos($el, $str) {

        return $el != '' && strpos($el, $str) !== false;
    }

    public function updateWebpackMix() {

        $path = $this->base_path . 'webpack.mix.js';
        $js = file($path);

        foreach ($js as $key => $elem) {
            $el = trim(str_replace(' ', '', $elem));

            if ($this->checkStrPos($el, 'module.exports=[')) {
                $enable = $this->moduleEnabled();
                $modl_line = "\t{src:'modules/" . $this->_name . "/app.js',build:'modules/" . $this->_name . "/build',enable:" . $enable . "},\n";
                array_splice($js, $key + 1, 0, $modl_line);
            }
        }

        file_put_contents($path, $js);
    }

    public function moduleEnabled() {

        return is_null($this->_options['no-sync']) ? 'true' : (!$this->_options['no-sync'] ? 'false' : 'true' );
    }

    public function updateModules() {

        $path = khofo_base('core.json');

        $modules = json_decode(file_get_contents($path), true);

        $modules['modules'][$this->_name] = [
            'installed' => true,
            'enabled' => $this->moduleEnabled() == "true" ? true : false,
            'created_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents($path, json_encode((Object) $modules, JSON_PRETTY_PRINT));
    }

    public function makeModuleFile($target, $fileName = '', $ext = 'js') {

        $filename = ($fileName == '' ? $target : $fileName);

        $path = $this->module_path . '/' . $filename . '.' . $ext;
        $content = $this->{$target . 'Content'}();

        $this->makeFile($path, $content);
    }

    public function makeJsFile($target, $fileName = '') {

        $this->makeModuleFile($target, $fileName);
    }

    public function makeVueFile() {

        $this->makeModuleFile('Components', 'Components/app/' . $this->_name, 'vue');

        if ($this->_options['with-admin']) {
            $this->makeModuleFile('Components', 'Components/admin/admin_' . $this->_name, 'vue');
        }
    }

    public function makeControllerFile() {

        $this->makeModuleFile('Controllers', 'Controllers/' . $this->_Uname . 'Controller', 'php');
    }

    public function makeRouteFile() {

        $this->makeFile($this->module_path . '/routes.php', $this->phpRouteContent());
    }

    public function appContent() {

        return "import router from '../../routes';\n"
                . "\nrequire('../../core');\n"
                . "/**\n"
                . "* Next, we will create a fresh Vue application instance and attach it to\n"
                . "* the page. Then, you may begin adding components to this application\n"
                . "* or customize the JavaScript scaffolding to fit your unique needs.\n"
                . "*/\n\n"
                . "Vue.component('" . $this->_name . "', require('./Components/app/" . $this->_name . ".vue'));\n\n"
                . "const app = new Vue({\n"
                . "	el: '#" . $this->_name . "',\n"
                . "	router\n"
                . "});\n";
    }

    public function routeContent() {
        $content = "import " . $this->_Uname . " from './Components/app/" . $this->_name . "';\n";

        if ($this->_options['with-admin']) {
            $content .= "import Admin" . $this->_Uname . " from './Components/app/" . $this->_name . "';\n";
        }

        $content .= "export default [\n"
                . "	{\n"
                . "		path:'/" . strtolower($this->_name) . "',\n"
                . "		component:" . $this->_Uname . "\n"
                . "	},\n";

        if ($this->_options['with-admin']) {
            $content .= "	{\n"
                    . "		path:'/admin/" . $this->_name . "',\n"
                    . "		component: Admin" . $this->_Uname . "\n"
                    . "	}\n";
        }
        $content .= "];\n";

        return $content;
    }

    public function ComponentsContent() {

        return "<template>\n"
                . "    <div class='container'>\n"
                . "        <div class='row'>\n"
                . "            <div class='col-md-8 col-md-offset-2'>\n"
                . "                <div class='panel panel-default'>\n"
                . "                    <div class='panel-heading'>" . $this->_name . " Component</div>\n\n"
                . "                    <div class='panel-body'>\n"
                . "                        I'm an " . $this->_name . " component!\n"
                . "                    </div>\n"
                . "                </div>\n"
                . "            </div>\n"
                . "        </div>\n"
                . "    </div>\n"
                . "</template>\n"
                . "<script>\n"
                . "    export default {\n"
                . "        mounted() {\n"
                . "            console.log('Component mounted.')\n"
                . "        }\n"
                . "    }\n"
                . "</script>";
    }

    public function ControllersContent() {

        return $this->generateController($this->_Uname);
    }

    public function phpRouteContent() {
        return "<?php\n\n"
                . "Route::get('" . strtolower($this->_name) . "',function() {\n"
                . "\tdd('" . $this->_name . " component routes');\n"
                . "});\n"
                . "// set your routes here";
    }

}
