<?php

namespace BatinMustu\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function Laravel\Prompts\multiselect;

class StubPublishCommand extends Command
{
    public $signature = 'module-maker:publish';

    public $description = 'My command';

    protected Filesystem $fileSystem;

    public function __construct(FileSystem $fileSystem)
    {
        parent::__construct();

        $this->fileSystem = $fileSystem;
    }

    public function handle(): int
    {
        $templates = $this->askTemplates();

        $this->fileSystem->ensureDirectoryExists(config('laravel-module-maker.stub_template_folder'));

        foreach ($templates as $template) {
            $this->fileSystem->copyDirectory(
                $this->getCoreStubTemplateFolder().'/'.$template,
                config('laravel-module-maker.stub_template_folder').'/'.$template
            );
        }

        $this->info('Stub templates published to '.config('laravel-module-maker.stub_template_folder').' folder successfully');
        $this->info('You can now customize the stub templates as you wish.');

        return self::SUCCESS;
    }

    protected function getCoreStubTemplateFolder(): string
    {
        return __DIR__.'/../../resources/stubs';
    }

    protected function getCoreStubTemplates(): array
    {
        return Arr::mapWithKeys(
            $this->fileSystem->directories($this->getCoreStubTemplateFolder()),

            function ($stubTemplate) {
                $stubTemplateKey = basename($stubTemplate);

                return [
                    $stubTemplateKey => Str::headline($stubTemplateKey)." ($stubTemplateKey)",
                ];
            });
    }

    protected function askTemplates(): array
    {
        return multiselect(
            label: 'Select the stub templates you want to publish:',
            options: $this->getCoreStubTemplates(),
            default: array_keys($this->getCoreStubTemplates()),
        );
    }
}
