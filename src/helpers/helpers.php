<?php 

if (!function_exists('laraset_path')) {
    function laraset_path($path = '') {
        return base_path('vendor/khofaai/laraset/src/'.$path);
    }
}

if (!function_exists(('laraset_modules'))) {
    function laraset_modules() {
        return json_decode(file_get_contents(app_path('laraset').'/core.json'),true)['modules'];
    }
}

if (!function_exists(('laraset_base'))) {
    function laraset_base($path = '') {
        return app_path('laraset').'/'.$path;
    }
}

if (!function_exists(('laraset_asset'))) {
    function laraset_asset($path = '') {
        return url('app/laraset').'/'.$path;
    }
}

if (!function_exists('module_exists')) {
    function module_exists($module_name) {
        $path = laraset_base('modules/'.$module_name);
        return file_exists($path) ? $path : false;
    }
}

if(!function_exists('module_path')) {
    function module_path($module_name) {
        $path = module_exists($module_name);
        
        return $path ? $path : null;
    }
}

if (!function_exists('dir_structure')) {
    function dir_structure($path = null,$subdir = '',$subpath = '') {

        $bt = debug_backtrace();
        $dirname = dirname($bt[0]['file']);
        if (!is_null($path)) {
            $dirname = $path;
        }

        if (is_dir($dirname)) {
            
            $dirname   = str_finish($dirname,'/');
            $dir_path  = str_finish(str_replace($subpath, '', $dirname),'/');

            $structure = [
                'path' => $subpath != '' ? $dir_path : $dirname,
                'directories' => [],
                'files' => []
            ];
            
            $directory = scandir($dirname);
            $directory = array_diff($directory, array('.', '..'));

            // fetch all files inside Helper Directory
            foreach ($directory as $filename) {
                // not including this file
                if(basename($dirname) != $filename){
                    
                    $base_file_path = $filename;
                    if ($subdir != '') {
                        $base_file_path .= '/'.$subdir;
                    }
                    // get each file location
                    $file_path = $dirname.$base_file_path;
                    // check if this is a file or directory
                    if (is_file($file_path)) {

                        array_push($structure['files'],$base_file_path);
                    } elseif( is_dir($file_path)) {

                        $structure['directories'][$filename] = dir_structure($file_path);
                    }
                }
            }
            return $structure;
        }
        return null;
    }
}

if(!function_exists('file_get_php_classes')) {

    function file_get_php_classes($filepath) {
      $php_code = file_get_contents($filepath);
      $classes = get_php_classes($php_code);
      return $classes;
    }
}

if(!function_exists('get_php_classes')) {

    function get_php_classes($php_code) {
      $classes = array();
      $tokens = token_get_all($php_code);
      $count = count($tokens);
      for ($i = 2; $i < $count; $i++) {
        if (   $tokens[$i - 2][0] == T_CLASS
            && $tokens[$i - 1][0] == T_WHITESPACE
            && $tokens[$i][0] == T_STRING) {

            $class_name = $tokens[$i][1];
            $classes[] = $class_name;
        }
      }
      return $classes;
    }
}

if (!function_exists('laraset_get_stub')) {
    function laraset_get_stub($name) {
        $stub_path = laraset_path('core/Stubs/'.$name.'.stub');
        return file_exists($stub_path) ? $stub_path : null;
    }
}