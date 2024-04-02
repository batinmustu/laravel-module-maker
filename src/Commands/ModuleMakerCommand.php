<?php

namespace BatinMustu\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Class ModuleMakerCommand
 *
 * This class is responsible for handling the 'module-maker' command.
 * It creates a new module with the given name and overwrites existing files in the module directory.
 */
class ModuleMakerCommand extends Command implements PromptsForMissingInput
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    public $signature = 'module-maker {moduleName} {--template=} {--exclude-stub=*} {--accept-risk}';

    /**
     * The description of the command.
     *
     * @var string
     */
    public $description = 'This command will create a new module with the given name. It will overwrite existing files in the module directory.';

    /**
     * The Filesystem instance.
     */
    protected Filesystem $fileSystem;

    /**
     * The core stub template folder.
     */
    protected string $coreStubTemplateFolder;

    /**
     * The stub template key.
     */
    protected string $stubTemplateKey;

    /**
     * The template stubs.
     */
    protected array $templateStubs;

    /**
     * The stubs.
     */
    protected array $stubs;

    /**
     * The template core.
     *
     * @var int
     */
    public const TEMPLATE_CORE = 0;

    /**
     * The template user.
     *
     * @var int
     */
    public const TEMPLATE_USER = 1;

    /**
     * The selected template type.
     */
    protected int $selectedTemplateType;

    /**
     * ModuleMakerCommand constructor.
     *
     * @param  FileSystem  $fileSystem  The Filesystem instance.
     */
    public function __construct(FileSystem $fileSystem)
    {
        parent::__construct();

        $this->fileSystem = $fileSystem;

        $this->coreStubTemplateFolder = $this->getCoreStubTemplateFolder();
    }

    /**
     * Prompt for missing arguments using.
     *
     * @return array The array of missing arguments.
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'moduleName' => fn () => $this->askModuleName(),
        ];
    }

    /**
     * Handle the command.
     *
     * This method prepares the template key, gets the stub names, prepares the stubs, puts the stubs, and outputs a success message.
     *
     * @return int The status of the command execution.
     */
    public function handle(): int
    {
        $confirm = $this->checkAcceptRiskOption();

        if (! $confirm) {
            $this->info('The command has been cancelled.');

            return self::FAILURE;
        }

        $this->prepareTemplateKey();

        $this->templateStubs = $this->getStubNames();

        $this->prepareStubs();

        $this->putStubs();

        $this->info("Module '{$this->argument('moduleName')}' created successfully!");

        return self::SUCCESS;
    }

    /**
     * Get the core stub template folder.
     *
     * @return string The core stub template folder.
     */
    protected function getCoreStubTemplateFolder(): string
    {
        return __DIR__.'/../../resources/stubs';
    }

    /**
     * Get the user stub template folder.
     *
     * @return string The user stub template folder.
     */
    protected function getUserStubTemplateFolder(): string
    {
        return config('laravel-module-maker.stub_template_folder');
    }

    /**
     * Get the stub template folder.
     *
     * @return string The stub template folder.
     */
    protected function getStubTemplateFolder(): string
    {
        return $this->selectedTemplateType === self::TEMPLATE_CORE
            ? $this->coreStubTemplateFolder
            : $this->getUserStubTemplateFolder();
    }

    /**
     * Get the core stub templates.
     *
     * @return array The core stub templates.
     */
    protected function getCoreStubTemplates(): array
    {
        return Arr::mapWithKeys(
            $this->fileSystem->directories($this->coreStubTemplateFolder),
            function ($stubTemplate) {
                $stubTemplateKey = basename($stubTemplate);

                return [
                    'core_'.$stubTemplateKey => Str::headline($stubTemplateKey).' (Core)',
                ];
            });
    }

    /**
     * Get the user stub templates.
     *
     * @return array The user stub templates.
     */
    protected function getUserStubTemplates(): array
    {
        $directory = $this->getUserStubTemplateFolder();

        if (! $this->fileSystem->exists($directory)) {
            return [];
        }

        return Arr::mapWithKeys(
            $this->fileSystem->directories($this->getUserStubTemplateFolder()),
            function ($stubTemplate) {
                $stubTemplateKey = basename($stubTemplate);

                return [
                    'user_'.$stubTemplateKey => Str::headline($stubTemplateKey).' (User)',
                ];
            });
    }

    /**
     * Get the stub names.
     *
     * @return array The stub names.
     */
    protected function getStubNames(): array
    {
        $stubTemplateFolder = $this->getStubTemplateFolder().'/'.$this->stubTemplateKey;

        $stubs = $this->fileSystem->allFiles($stubTemplateFolder);

        return collect($stubs)->mapWithKeys(function (SplFileInfo $stub) {
            return [
                $stub->getRelativePathname() => $this->fillStubPathParameters($stub->getRelativePathname()),
            ];
        })->toArray();
    }

    /**
     * Put the stubs.
     *
     * This method ensures the directory exists and puts the stubs.
     */
    protected function putStubs(): void
    {
        collect($this->stubs)->each(function ($stub) {
            $raw = $this->getStubRawContent($stub);

            $realPath = $this->fillStubPathParameters($stub);

            $this->fileSystem->ensureDirectoryExists(dirname($realPath));

            $this->fileSystem->put(
                $realPath,
                $this->fillStubContentParameters($raw, $realPath)
            );
        });
    }

    /**
     * Get the stub raw content.
     *
     * @param  string  $stub  The stub.
     * @return string The stub raw content.
     */
    protected function getStubRawContent($stub): string
    {
        return file_get_contents($this->getStubTemplateFolder().'/'.$this->stubTemplateKey.'/'.$stub);
    }

    /**
     * Fill the stub content parameters.
     *
     * @param  string  $raw  The raw content.
     * @param  string  $path  The path.
     * @return string The filled stub content parameters.
     */
    protected function fillStubContentParameters(string $raw, string $path): string
    {
        return str_replace(
            array_map(fn ($key) => $this->getParameter($key), array_keys($this->getStubParameters($path))),
            array_values($this->getStubParameters($path)),
            $raw
        );
    }

    /**
     * Fill the stub path parameters.
     *
     * @param  string  $path  The path.
     * @return string The filled stub path parameters.
     */
    protected function fillStubPathParameters(string $path): string
    {
        return str_replace(
            [
                ...array_map(fn ($key) => $this->getParameter($key), array_keys($this->getStubParameters())),
                '.stub',
            ],
            [
                ...array_values($this->getStubParameters()),
                '',
            ],
            $path
        );
    }

    /**
     * Get the parameter.
     *
     * @param  string  $key  The key.
     * @return string The parameter.
     */
    protected function getParameter($key): string
    {
        return '__'.$key.'__';
    }

    /**
     * Get the stub parameters.
     *
     * @param  string  $path  The path.
     * @return array The stub parameters.
     */
    protected function getStubParameters(string $path = ''): array
    {
        return [
            'Module_' => Arr::join(Str::ucsplit($this->argument('moduleName')), '_'),
            'Modules_' => Arr::join(Str::of($this->argument('moduleName'))->plural()->ucsplit()->toArray(), '_'),
            'module_' => Str::snake($this->argument('moduleName')),
            'modules_' => Str::plural(Str::snake($this->argument('moduleName'))),
            'Module' => $this->argument('moduleName'),
            'Module-' => Arr::join(Str::ucsplit($this->argument('moduleName')), '-'),
            'Module ' => Str::headline($this->argument('moduleName')),
            'Modules' => Str::pluralStudly($this->argument('moduleName')),
            'Modules-' => Arr::join(Str::of($this->argument('moduleName'))->plural()->ucsplit()->toArray(), '-'),
            'Modules ' => Str::plural(Str::headline($this->argument('moduleName'))),
            'module' => Str::camel($this->argument('moduleName')),
            'module-' => Str::kebab($this->argument('moduleName')),
            'module ' => Str::snake($this->argument('moduleName'), ' '),
            'modules' => Str::plural(Str::camel($this->argument('moduleName'))),
            'modules-' => Str::plural(Str::kebab($this->argument('moduleName'))),
            'modules ' => Str::plural(Str::snake($this->argument('moduleName'), ' ')),
            'Namespace' => $this->pathToNamespace($path),
            'migration' => now()->format('Y_m_d_His'),
        ];
    }

    /**
     * Convert path to namespace.
     *
     * @param  string  $path  The path.
     * @return string The namespace.
     */
    protected function pathToNamespace($path): string
    {
        if (! $path || ! Str::contains($path, '/')) {
            return '';
        }

        $directories = explode('/', $path);

        $namespace = '';

        foreach ($directories as $key => $directory) {
            if ($key === count($directories) - 1) {
                continue;
            }

            $namespace .= Str::ucfirst($directory).'\\';
        }

        return rtrim($namespace, '\\');
    }

    /**
     * Ask for the module name.
     *
     * @return string The module name.
     */
    protected function askModuleName(): string
    {
        return text(
            label: 'What is the module name?',
            placeholder: 'Eg: BlogCategory',
            required: true,
            hint: 'The module name should be in StudlyCase (eg: BlogCategory)'
        );
    }

    /**
     * Ask for the template.
     *
     * @return string The template.
     */
    protected function askTemplate(): string
    {
        $options = array_merge($this->getCoreStubTemplates(), $this->getUserStubTemplates());
        $template = select(
            label: 'Which template do you want to use?',
            options: $options,
            hint: 'Select the template you want to use for the module stubs.',
            required: true
        );

        $this->selectedTemplateType = Str::startsWith($template, 'core_') ? self::TEMPLATE_CORE : self::TEMPLATE_USER;

        return Str::replaceMatches('/^(core_|user_)/', '', $template);
    }

    /**
     * Ask the user to select the stubs they want to generate.
     *
     * @return array The selected stubs.
     */
    protected function askStubs(): array
    {
        return multiselect(
            label: 'Select the stubs you want to generate:',
            options: $this->templateStubs,
            default: array_keys($this->templateStubs),
            required: true,
            hint: 'Select the stubs you want to generate for the module.'
        );
    }

    /**
     * Check if the 'accept-risk' option is set.
     *
     * If not, warn the user about the potential risks and ask for confirmation.
     *
     * @return bool True if the 'accept-risk' option is set or the user confirmed to proceed, false otherwise.
     */
    protected function checkAcceptRiskOption(): bool
    {
        if ($this->option('accept-risk')) {
            return true;
        }

        $this->warn('This command will overwrite if there are files in the project that use the same path as the template stubs.');
        $this->warn('Please make sure to backup your files before proceeding.');
        $this->warn("If you don't want to see this message again, please run the command with the --accept-risk option.");

        return $this->confirm('Do you want to proceed?');
    }

    /**
     * Check if the given template exists.
     *
     * @param  string  $template  The template to check.
     * @return bool True if the template exists, false otherwise.
     */
    protected function hasTemplate($template)
    {
        $templates = array_keys(array_merge($this->getCoreStubTemplates(), $this->getUserStubTemplates()));

        return in_array("core_$template", $templates) || in_array("user_$template", $templates);
    }

    /**
     * Prepare the template key.
     *
     * If the 'template' option is not set, ask the user to select a template.
     * If the 'template' option is set, check if the template exists and set the selected template type.
     */
    protected function prepareTemplateKey(): void
    {
        if (! $this->hasOption('template') || ! $this->option('template')) {
            $this->stubTemplateKey = $this->askTemplate();

            return;
        }

        if (! $this->hasTemplate($this->option('template'))) {
            throw new \Exception("The template '{$this->option('template')}' does not exist.");
        }

        if (in_array('user_'.$this->option('template'), array_keys($this->getUserStubTemplates()))) {
            $this->selectedTemplateType = self::TEMPLATE_USER;
        } else {
            $this->selectedTemplateType = self::TEMPLATE_CORE;
        }

        $this->stubTemplateKey = Str::replaceMatches('/^(core_|user_)/', '', $this->option('template'));
    }

    /**
     * Prepare the stubs.
     *
     * If the 'exclude-stub' option is not set, ask the user to select the stubs.
     * If the 'exclude-stub' option is set, exclude the specified stubs.
     */
    protected function prepareStubs(): void
    {
        if (count($this->option('exclude-stub')) === 0) {
            $this->stubs = $this->askStubs();

            return;
        }

        $this->stubs = array_keys(Arr::except($this->templateStubs, $this->option('exclude-stub')));
    }
}
