<?php

namespace Khofo\vendor\Commands;

use Khofo\vendor\Commands\KhofoCommands;

class KhofoInstall extends KhofoCommands
{
    protected $signature = 'Khofo:install';
    protected $description = 'install cores';

    protected $messages = [
    	'success' => '<options=bold;fg=cyan>[Khofo]<bg=black;fg=cyan> installed successfully',
    	'warning' => '<options=bold,reverse>~Khofo~<fg=yellow> already installed.'
    ];

	public function __construct() {

		parent::__construct();
	}

	public function handle() {
		$this->updateInstallStatus();
	}

	public function updateInstallStatus() {

		$path = khofo_path('core.json');

		if (file_exists($path)) {
			
			$modules = json_decode(file_get_contents($path),true);

			if (!$modules['installed']["status"] || !isset($modules['installed']["status"])) {

				$this->generateArchitecture();
				$this->generateFiles();
				$this->updateWebpackMixJs();

				$modules['installed']["status"] = true;
				$modules['installed']["installed_at"] = date('Y-m-d H:i:s');

				file_put_contents($path, json_encode((Object)$modules, JSON_PRETTY_PRINT));
				$this->info($this->messages['success']);
			} else {

				$this->info($this->messages['warning']);
				$this->info('//installed at : '.$modules['installed']['installed_at'].'');
			}

		} else {
			
			$this->error('core.json not found');
		}
	}

	public function generateArchitecture() {
		$architecture = array(
			'assets' => '',
			'dist' => array('js' => ''),
			'helpers' => '' ,
			'modules' => ''
		);

		$path = app_path('Khofo');
		$this->createFolder($path);
		
		$this->createArchitectureFolders($architecture,$path);

		$this->info('* [Folders] Generated Successfully !');
	}

	public function createArchitectureFolders($architecture,$path) {

		foreach ($architecture as $key => $value) {
			$dir_path = $path.'/'.$key;
			$this->createFolder($dir_path);
			
			if (gettype($value) !== 'string') {

				$this->createArchitectureFolders($value,$dir_path);
			}
		}
	}

	public function createFolder($path) {
		
		if (!is_dir($path)) {
			
			mkdir($path,0777);
		}
	}

	public function getFileContentFunction($name) {
		
		$name = str_replace(' ', '', ucwords(str_replace('.', ' ', $name)));
		return $name.'Content';
	}

	public function generateFiles() {
		$files = array(
			'/' => 	array(
				'bootstrap.js',
				'core.js',
				'core.json',
				'routes.js',
				'routes.php',
				'webpack.mix.js'
			),
			'helpers' => array(
				'helpers.js',
				'helpers.php'
			),
			'modules' => array(
				'Core.vue',
				'menu.vue',
				'topbar.vue'
			)
		);

		$path = app_path('Khofo');

		foreach ($files as $key => $value) {

			foreach ($value as $filename) {
				
				$dir_path = $path;

				if ($key != '/') {
					$dir_path .= '/'.$key.'/';
				} else {
					$dir_path .= $key;
				}

				$functionName = $this->getFileContentFunction($filename);

				$this->makeFile( $dir_path.$filename, $this->{$functionName}() );
			}
		}
		if (!file_exists(resource_path('views/khofo.blade.php'))) {

			$this->makeFile(resource_path('views/khofo.blade.php'),$this->CoreTemplateContent());
		}

		$this->info('* [File] Generated Successfully !');
	}

	private function BootstrapJsContent() {

		return  "window.axios = require('axios');\n"
				."window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';\n\n"
				."let token = document.head.querySelector('meta[name=\"csrf-token\"]');\n\n"
				."if (token) {\n\n"
				."    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;\n"
				."} else {\n"
				."    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');\n"
				."}\n";
	}

	private function CoreJsContent() {

		return 	"require('./bootstrap');\n"
				."import router from './routes';\n\n"
				."/* Artivue MODULES */\n\n"
				."window.Vue = require('vue');\n\n"
				."import {Helpers} from './helpers/helpers';\n"
				."Vue.mixin(Helpers);\n\n"
				."import VueRouter from 'vue-router';\n"
				."import Vuetify from 'vuetify';\n\n"
				."Vue.use(VueRouter);\n"
				."Vue.use(Vuetify);\n\n"
				."require ('../../node_modules/vuetify/dist/vuetify.min.css');\n\n"
				."Vue.component('Core', require('./modules/Core.vue'));\n\n"
				."const eventBus = new Vue();\n\n"
				."Object.defineProperties(Vue.prototype, {\n"
				."  	\$event: {\n"
				."	    get: function () {\n"
				."	      	return eventBus\n"
				."	    }\n"
				."  	}\n"
				."});\n\n"
				."const app = new Vue({\n"
				."	el: '#app',\n"
				."	router\n"
				."});\n";
	}

	private function CoreJsonContent() {
		
		return 	"{\n"
				."    \"version\": \"0.1.0\",\n"
				."    \"name\": \"Artivue\",\n"
				."    \"modules\": {\n"
				."    }\n"
				."}\n";
	}

	private function RoutesJsContent() {
		
		return 	"import VueRouter from 'vue-router';\n\n"
				."import CoreLayout from './modules/Core.vue';\n\n"
				."const __ROUTES__ = [];\n\n"
				."let modules = require('./core.json').modules;\n\n"
				."Object.keys(modules).map((moduleKey) => {\n"
				."	let module = modules[moduleKey];\n\n"
				."	try	{\n\n"
				."		let DynamicRoute = require('./modules/'+moduleKey+'/route.js');\n"
				."		__ROUTES__.push( DynamicRoute.default );\n"
				."	} catch (e) {\n"
				."		\n"
				."		console.error(moduleKey+'/route.js not found !');\n"
				."	}\n"
				."});\n\n"
				."const Routes = [].concat.apply([],__ROUTES__);\n\n"
				."export default new VueRouter({\n"
				."	mode:'history',\n"
				."	routes:[\n"
				."		{\n"
				."			path:'/',\n"
				."			component:CoreLayout,\n"
				."			children:Routes\n"
				."		}\n"
				."	]\n"
				."});";
	}

	private function RoutesPhpContent() {

		return 	"<?php \n\n"
				."foreach (scandir(dirname(__FILE__).'/modules') as \$directory) {\n"
				."	// not including this file\n\n"
				."	if(basename(__FILE__) != \$directory && \$directory != '.' && \$directory != '..' && strpos(\$directory,'.') === false){\n\n"
				."		// get each file location\n"
				."		\$path = dirname(__FILE__).'/modules/'.\$directory.'/routes.php';\n"
				."		// check if this is a file\n"
				."		if (is_file(\$path)) {\n"
				."			// including file only once\n"
				."			require_once \$path;\n"
				."		}\n"
				."	}\n"
				."}\n";
	}

	private function WebpackMixJsContent() {
		return 	"module.exports = [\n"
				."	{src:'core.js',build:'dist/js/core.js',enable:true}\n"
				."];\n";
	}

	private function HelpersJsContent() {
		return 	"const __SESSION__ = {\n"
				."	store_session(obj) {\n"
				."		if (typeof obj === 'object') {\n"
				."			localStorage.setItem('session_expiration_date',obj.session_expiration)\n"
				."			delete obj.session_expiration;\n"
				."			localStorage.setItem('session',JSON.stringify(obj));\n"
				."		}\n"
				."	}\n"
				."};\n\n"
				."const __EVENT__ = {\n"
				."	catch_event(event,callback) {\n"
				."		if (typeof Object.keys(this.\$event._events)[event] === 'undefined') {\n"
				."			this.\$event.\$on(event,function(val){\n"
				."				callback(val);\n"
				."			});\n"
				."		}\n"
				."	},\n"
				."	emit_event(event,params){\n"
				."		this.\$event.\$emit(event,params);\n"
				."	}\n"
				."};\n\n"
				."const __REQUEST__ = {\n"
				."	__request(_obj) {\n"
				."		if (typeof _obj.data === 'undefined') {\n"
				."			_obj.data = {}\n"
				."		}\n"
				."		if (typeof _obj.headers === 'undefined') {\n"
				."			_obj.headers = {}\n"
				."		}\n"
				."		axios({\n"
				."			method 	: _obj.method,\n"
				."			url		: _obj.url,\n"
				."			data	: _obj.data,\n"
				."			headers	: _obj.headers\n"
				."		})\n"
				."		.then(_obj.then)\n"
				."		.catch( err => {\n"
				."			if (typeof _obj.catch !== 'undefined') { _obj.catch(err); } else { console.error(err); }\n"
				."		});\n"
				."	},\n"
				."	ajax_get(url,_obj) {\n"
				."		__REQUEST__.ajax_req(url,_obj,\"GET\");\n"
				."	},\n"
				."	ajax_post(url,_obj) {\n"
				."		__REQUEST__.ajax_req(url,_obj,\"POST\");\n"
				."	},\n"
				."	ajax_put(url,_obj) {\n"
				."		__REQUEST__.ajax_req(url,_obj,\"PUT\");\n"
				."	},\n"
				."	ajax_delete(url,_obj) {\n"
				."		__REQUEST__.ajax_req(url,_obj,\"DELETE\");\n"
				."	},\n"
				."	ajax_req(url,_obj,_method) {\n"
				."		_obj.url = url;\n"
				."		_obj.method = _method;\n"
				."		__REQUEST__.__request(_obj);\n"
				."	}\n"
				."}\n\n"
				."const __METHODS__ = {\n"
				."	methods:{\n"
				."		redirect(url) {\n"
				."			this.\$route.push(url);\n"
				."		}\n"
				."	}\n"
				."}\n\n"
				."const __HElPER_METHODS__ = Object.assign({},__EVENT__,__SESSION__,__REQUEST__);\n\n"
				."export const Helpers = {\n"
				."	methods: __HElPER_METHODS__\n"
				."};\n";
	}

	private function HelpersPhpContent() {
		//
	}

	private function TopbarVueContent() {

		return 	"<template>\n"
				."	<v-toolbar\n"
				."		color=\"blue darken-3\" \n"
				."		dark \n"
				."		app \n"
				."		fixed\n"
				."		:clipped-left=\"\$vuetify.breakpoint.mdAndUp\">\n\n"
				."		<v-toolbar-title style=\"width: 300px\" class=\"ml-0 pl-3\">\n\n"
				."			<v-toolbar-side-icon @click.stop=\"drawer = !drawer\"></v-toolbar-side-icon>\n"
				."			<span class=\"hidden-sm-and-down\"><b>|Artivue</b> Platform</span>\n\n"
				."		</v-toolbar-title>\n\n"
				."		<v-spacer></v-spacer>\n\n"
				."		<v-btn icon> <v-icon>notifications</v-icon> </v-btn>\n"
				."		<v-btn icon large> <v-icon>face</v-icon> </v-btn>\n\n"
				."	</v-toolbar>\n"
				."</template>\n"
				."<script>\n\n"
				."	export default {\n\n"
				."		data: () => ({\n"
				."			drawer: null\n"
				."		})\n"
				."	}\n"
				."</script>";
	}

	private function MenuVueContent() {
		
		return 	"<template>\n"
				."	<v-list dense>\n\n"
				."		<template v-for=\"item in menu\">\n\n"
				."			<v-list-group\n"
				."				v-if=\"item.children\"\n"
				."				v-model=\"item.model\"\n"
				."				:key=\"item.text\"\n"
				."				:prepend-icon=\"item.model ? item.icon : item['icon-alt']\"\n"
				."				append-icon=\"\">\n\n"
				."				<v-list-tile slot=\"activator\">\n\n"
				."					<v-list-tile-content>\n\n"
				."						<v-list-tile-title>\n\n"
				."							{{ item.text }}\n"
				."						</v-list-tile-title>\n"
				."					</v-list-tile-content>\n"
				."				</v-list-tile>\n\n"
				."				<v-list-tile\n"
				."					v-for=\"(child, i) in item.children\"\n"
				."					:key=\"i\"\n"
				."					@click=\"triggerRoute(child.route)\">\n\n"
				."					<v-list-tile-action v-if=\"child.icon\">\n\n"
				."						<v-icon>{{ child.icon }}</v-icon>\n"
				."					</v-list-tile-action>\n\n"
				."					<v-list-tile-content>\n\n"
				."						<v-list-tile-title>\n\n"
				."							{{ child.text }}\n"
				."						</v-list-tile-title>\n"
				."					</v-list-tile-content>\n"
				."				</v-list-tile>\n"
				."			</v-list-group>\n\n"
				."			<v-list-tile v-else @click=\"triggerRoute(item.route)\" :key=\"item.text\">\n\n"
				."				<v-list-tile-action>\n\n"
				."					<v-icon>{{ item.icon }}</v-icon>\n"
				."				</v-list-tile-action>\n\n"
				."				<v-list-tile-content>\n\n"
				."					<v-list-tile-title>\n\n"
				."						{{ item.text }}\n"
				."					</v-list-tile-title>\n"
				."				</v-list-tile-content>\n\n"
				."				<v-btn\n"
				."					v-if=\"item.action\"\n"
				."					right\n"
				."					color=\"blue\"\n"
				."					flat\n"
				."					fixed\n"
				."					small\n"
				."					@click.stop=\"\$emit('open',item)\">\n"
				."					<v-icon>add</v-icon>\n"
				."				</v-btn>\n"
				."			</v-list-tile>\n"
				."		</template>\n"
				."	</v-list>\n"
				."</template>\n\n"
				."<script>\n\n"
				."	export default {\n"
				."		data: () => ({\n"
				."			menu: []\n"
				."		}),\n"
				."		methods:{\n"
				."			triggerRoute(route) {\n"
				."				if (route.enable && route.path != '') {\n"
				."					this.\$emit('pageLoading',true);\n"
				."					setTimeout(() => {\n"
				."						this.\$emit('pageLoading',false)\n"
				."					}, 1000);\n"
				."					this.\$router.push(route.path)\n"
				."				}\n"
				."			}\n"
				."		}\n"
				."	}\n"
				."</script>\n";
	}

	private function CoreVueContent() {

		return 	"<template>\n"
				."	<v-app id=\"inspire\">\n\n"
				."		<v-navigation-drawer \n"
				."			app \n"
				."			fixed \n"
				."			:clipped=\"\$vuetify.breakpoint.mdAndUp\" \n"
				."			v-model=\"drawer\">\n"
				."			<Menu @open=\"open_dialog\" @pageLoading=\"page_loading\"></Menu>\n"
				."		</v-navigation-drawer>\n\n"
				."		<Topbar></Topbar>\n\n"
				."		<v-content>\n\n"
				."			<v-container style=\"margin-top:20%\" v-if=\"pageLoading\" fluid>\n\n"
				."                <v-layout justify-center align-center>\n\n"
				."					<v-progress-circular \n"
				."						:size=\"70\" \n"
				."						:rotate=\"0\" \n"
				."						indeterminate \n"
				."						color=\"primary\">\n"
				."					</v-progress-circular>\n"
				."                </v-layout>\n"
				."            </v-container>\n\n"
				."			<router-view v-else></router-view>\n\n"
				."		</v-content>\n\n"
				."	</v-app>\n"
				."</template>\n\n"
				."<script>\n"
				."	import Menu from './menu'; \n"
				."	import Topbar from './topbar'; \n\n"
				."	export default {\n"
				."		components:{\n"
				."			Menu,\n"
				."			Topbar\n"
				."		},\n"
				."		data: () => ({\n"
				."			pageLoading: false,\n"
				."			drawer: null,\n"
				."			open_dialog:false\n"
				."		}),\n"
				."		methods:{\n"
				."			page_loading(loading) {\n"
				."				this.pageLoading = loading;\n"
				."			}\n"
				."		}\n"
				."	}\n"
				."</script>\n"
				."<style type=\"text/css\">\n"
				."	.progress-linear{margin:0;}\n"
				."</style>\n";
	}

	private function BaseWebpackMixJsContent() {

		return 	"let mix = require('laravel-mix');\n"
				."let Core = require('./app/Khofo/webpack.mix.js');\n\n"
				."Core.map( file => {\n"
				."	if (file.enable) {\n"
				."		mix.js('app/Khofo/'+file.src,'app/Khofo/'+file.build);\n"
				."	}\n"
				."});";
	}

	public function updateWebpackMixJs() {
		
		$path = base_path('webpack.mix.js');

		if (file_exists($path)) {
			$content = file_get_contents($path);
			$this->makeFile(base_path('webpack-old.mix.js'),$content);
			unlink($path);
		}

		$this->makeFile($path,$this->BaseWebpackMixJsContent());
	}
}