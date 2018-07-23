<?php

namespace Khofo\vendor\Commands;

class KhofoMigrate extends KhofoCommands
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Khofo:migrate
	{--sync=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'migrate from Core modules';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setCommandOption('sync');
        $this->loadMigrationPaths();
        if ($this->_options['sync']) {

            $this->mappingMigrationDirectories();
        }

        $this->call('migrate');
    }

    private function loadMigrationPaths()
    {
        $core_path = khofo_base('modules');
        $migration_structure = dir_structure($core_path, 'Database/Migrations');

        foreach ($migration_structure['directories'] as $migration) {

            $this->fetchMigrationFiles($migration);
        }
    }

    private function fetchMigrationFiles($files)
    {
        foreach ($files['files'] as $file) {

            $this->copyFileToMigrationDir($files['path'], $file);
        }
    }

    private function copyFileToMigrationDir($path, $file)
    {
        \File::copy($path . $file, $this->migrationDirectory() . '/' . $file);
    }

    private function migrationDirectory()
    {
        $migrationDir = database_path('migrations');

        if (!is_dir($migrationDir)) {
            mkdir($migrationDir);
        }
        return $migrationDir;
    }

    // For synchronizing
    public function mappingMigrationDirectories()
    {
        $tree = dir_structure(core_path('modules'), 'Database');

        foreach ($tree['directories'] as $element) {

            $path = $element['directories']['Migrations']['path'];

            unset($element['directories']['Migrations']['path'], $element['directories']['Migrations']['directories']);
            if (!empty($element['directories']['Migrations']['files'])) {

                $files[$path] = array_values($element['directories']['Migrations']['files']);
            }
        }

        $migration_tree = dir_structure(database_path('migrations'));

        foreach ($migration_tree['files'] as $file) {

            foreach ($files as $path => $local_files) {

                foreach ($local_files as $key => $old_file) {

                    if (strpos($file, $old_file) !== false) {

                        $this->synchFile($path, $old_file);
                    }
                }
            }
        }
    }

    public function synchFile($path, $filename)
    {
        $migration_path = database_path('migrations') . '/' . $filename;
        $module_migration_path = $path . $filename;

        if (md5(file_get_contents($module_migration_path)) != md5(file_get_contents($migration_path))) {

            if (file_exists($migration_path)) {

                unlink($migration_path);
            }
            \File::copy($module_migration_path, $migration_path);
            $this->info('   Migration file [ ' . $filename . ' ] synchronized successfully !');
        }
    }
}
