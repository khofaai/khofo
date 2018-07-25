<?php

namespace Khofaai\Laraset\core\Commands;

class LarasetMakeCommand extends LarasetCommands
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'laraset:make:command 
	{name : command name}
    {--alias=default : set command alias (by default is "default")}
    {--desc=default : set command description (by default is "Command Descroption")}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'make command system helpers';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $_name;
	protected $commandPath;

	public function __construct() {
		
		parent::__construct();
	}

	public function self_construct() {
		$this->moduleName = $this->argument('name');
		$this->basePath = dirname(__FILE__);
		$this->commandPath = $this->basePath.'/';
		$this->setCommandOption('alias');
		$this->setCommandOption('desc');

	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$this->self_construct();
		// check if module exist and installed
		// then set path to Migrations fir
		// then create Migration File
		$className = $this->commandClassName();

		if (!class_exists_in_directory($this->commandPath,$className)) {

			$this->makeFile($this->commandPath.$className.'.php',$this->CommandContent());
			$this->info('<options=bold;fg=green>['.$className.']<bg=black;fg=green> command created successfully !');
		} else {

			$this->warn('<options=bold;fg=yellow>['.$className.']<bg=black;fg=yellow> command already exist !');
		}		
	}

	protected function commandClassName() {

		$name = str_replace('-', ' ', $this->moduleName);
		$name = str_replace('_', ' ', $name);
		$name = str_replace(' ', '', ucwords($name));
		$className = str_replace('-', ' ', $name);
		
		return 'Laraset'.$className;
	}

	public function CommandContent() {
		$alias = 'command:name';
		$description = 'Command description';
		if (isset($this->_options['alias']) && $this->_options['alias'] != 'default') {
			$alias = $this->_options['alias'];
		}
		if (isset($this->_options['desc']) && $this->_options['desc'] != 'default') {
			$description = $this->_options['desc'];
		}
		return 	"<?php\n\n"
				."namespace Khofaai\Laraset\\vendor\\Commands;\n\n"
				."class ".$this->commandClassName()." extends LarasetCommands {\n"
				."    /**\n"
				."     * The name and signature of the console command.\n"
				."     *\n"
				."     * @var string\n"
				."     */\n"
				."    protected \$signature = '".$alias."';\n"
				."    /**\n"
				."     * The console command description.\n"
				."     *\n"
				."     * @var string\n"
				."     */\n"
				."    protected \$description = '".$description."';\n"
				."    /**\n"
				."     * Create a new command instance.\n"
				."     *\n"
				."     * @return void\n"
				."     */\n"
				."    public function __construct()\n"
				."    {\n"
				."        parent::__construct();\n"
				."    }\n"
				."    /**\n"
				."     * Execute the console command.\n"
				."     *\n"
				."     * @return mixed\n"
				."     */\n"
				."    public function handle()\n"
				."    {\n"
				."        \$this->info('[".$alias."] command exectuted // this command description > ".$description."');\n"
				."    }\n"
				."}\n";
	}
}