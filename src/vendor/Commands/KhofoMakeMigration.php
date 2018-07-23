<?php

namespace Khofo\vendor\Commands;

class KhofoMakeMigration extends KhofoCommands
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Khofo:make:migration 
	{name : migration name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make migration for specified module';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $module_name;
    protected $className;

    public function self_construct() {

        $this->module_name = $this->choice('For Which Module ?', $this->modulesName());
        $this->className = 'Create' . ucfirst($this->_name) . 'Table';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        if (!$this->super_construct()) {
            return false;
        }
        $this->self_construct();
        $module_path = module_path($this->module_name);
        if (!class_exists_in_directory($module_path . '/Database/Migrations/', $this->className)) {

            $migration_class = date('Y_m_d_his') . '_create_' . $this->_name . '_table.php';
            $this->makeFile($module_path . '/Database/Migrations/' . $migration_class, $this->MigrationContent());
            $this->info('<options=bold;fg=green>[' . $this->className . ']<bg=black;fg=green>  migration created successfully !');
        } else {

            $this->warn($this->className . ' class already Exist');
        }
    }

    public function MigrationContent() {

        return "<?php\n\n"
                . "use Illuminate\Support\Facades\Schema;\n"
                . "use Illuminate\Database\Schema\Blueprint;\n"
                . "use Illuminate\Database\Migrations\Migration;\n\n"
                . "class " . $this->className . " extends Migration\n"
                . "{\n"
                . "    /**\n"
                . "     * Run the migrations.\n"
                . "     *\n"
                . "     * @return void\n"
                . "     */\n"
                . "    public function up()\n"
                . "    {\n"
                . "        Schema::create('" . $this->_name . "', function (Blueprint \$table) {\n"
                . "            \$table->increments('id');\n"
                . "            \$table->string('name');\n"
                . "            \$table->rememberToken();\n"
                . "            \$table->timestamps();\n"
                . "        });\n"
                . "    }\n"
                . "    /**\n"
                . "     * Reverse the migrations.\n"
                . "     *\n"
                . "     * @return void\n"
                . "     */\n"
                . "    public function down()\n"
                . "    {\n"
                . "        Schema::dropIfExists('" . $this->_name . "');\n"
                . "    }\n"
                . "}";
    }

    protected function setNamespace() {
        return "App\\Core\\modules\\" . $this->module_name . "\\Database\\Migrations";
    }

}
