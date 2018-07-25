<?php

namespace Khofaai\Laraset;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

use Khofaai\Laraset\vendor\Commands\LarasetCommand;
use Khofaai\Laraset\vendor\Commands\LarasetInstall;
use Khofaai\Laraset\vendor\Commands\LarasetDelete;
use Khofaai\Laraset\vendor\Commands\LarasetCores;
use Khofaai\Laraset\vendor\Commands\LarasetMakeController;
use Khofaai\Laraset\vendor\Commands\LarasetMakeModel;
use Khofaai\Laraset\vendor\Commands\LarasetMakeModule;
use Khofaai\Laraset\vendor\Commands\LarasetMakeCommand;
use Khofaai\Laraset\vendor\Commands\LarasetMakeMigration;
use Khofaai\Laraset\vendor\Commands\LarasetMigrate;

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

    private function loadCommands() 
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LarasetCommand::class,
                LarasetCores::class,
                LarasetDelete::class,
                LarasetInstall::class,
                LarasetMakeCommand::class,
                LarasetMakeController::class,
                LarasetMakeMigration::class,
                LarasetMakeModel::class,
                LarasetMakeModule::class,
                LarasetMigrate::class
            ]);
        }
    }
}
