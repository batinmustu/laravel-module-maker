<?php

namespace BatinMustu\LaravelModuleMaker;

use BatinMustu\LaravelModuleMaker\Commands\BlueprintCommand;
use BatinMustu\LaravelModuleMaker\Commands\ModuleMakerCommand;
use BatinMustu\LaravelModuleMaker\Commands\StubPublishCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelModuleMakerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-module-maker')
            ->hasConfigFile('laravel-module-maker')
            ->hasCommands([
                ModuleMakerCommand::class,
                StubPublishCommand::class,
                BlueprintCommand::class,
            ]);
    }
}
