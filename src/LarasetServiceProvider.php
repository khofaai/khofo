<?php

namespace Khofaai\Laraset;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

use Khofaai\Laraset\core\Commands\LarasetInstall;
use Khofaai\Laraset\core\Commands\LarasetDelete;
use Khofaai\Laraset\core\Commands\LarasetModules;
use Khofaai\Laraset\core\Commands\LarasetMakeController;
use Khofaai\Laraset\core\Commands\LarasetMakeModel;
use Khofaai\Laraset\core\Commands\LarasetMakeModule;
use Khofaai\Laraset\core\Commands\LarasetMakeMigration;
use Khofaai\Laraset\core\Commands\LarasetMigrate;

use Khofaai\Laraset\core\Facades\Laraset;

class LarasetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadCommands();
        $this->app->bind('laraset',function() {
            return new Laraset;
        });
    }
    /**
     * bind all commands to Laravel Console
     * 
     * @return void
     */
    private function loadCommands() 
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LarasetDelete::class,
                LarasetInstall::class,
                LarasetMakeController::class,
                LarasetMakeMigration::class,
                LarasetMakeModel::class,
                LarasetMakeModule::class,
                LarasetModules::class,
                LarasetMigrate::class
            ]);
        }
    }
}
