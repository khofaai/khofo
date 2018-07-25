<?php
namespace Khofaai\Laraset\core\Facades;

use Illuminate\Support\Facades\Facade;

class Laraset extends Facade
{
	public static function getFacadeAccessor()
    {
        return 'laraset';
    }
	
	public static function checkStrPos($el,$str) 
	{	
		return $el != '' && strpos($el,$str) !== false;
	}

	public static function path($path = '')
	{
		return laraset_path($path);
	}

	public static function modules()
	{
		return laraset_modules();
	}

	public static function base($path = '')
	{
		return laraset_base($path);
	}

	public static function asset($path = '')
	{
		return laraset_asset($path);
	}

	public static function getStub($name)
	{
		return laraset_get_stub($name);
	}

	public function moduleExists($module_name)
	{
		return module_exists($module_name);
	}

	public function modulePath($module_name)
	{
		return module_path($module_name);
	}

	public function dirStructure($path = null,$subdir = '',$subpath = '')
	{
		return dir_structure($path = null,$subdir,$subpath);
	}

	public function getFilePhpClasses($filepath)
	{
		return file_get_php_classes($filepath);
	}
}
