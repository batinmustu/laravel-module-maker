<?php

namespace BatinMustu\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BlueprintCommand
 *
 * This class is responsible for handling the 'module-maker:blueprint' command.
 * It reads a blueprint file and executes the 'module-maker' command for each module defined in the blueprint.
 */
class BlueprintCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    public $signature = 'module-maker:blueprint {--accept-risk}';

    /**
     * The description of the command.
     *
     * @var string
     */
    public $description = 'My command';

    /**
     * The Filesystem instance.
     */
    protected Filesystem $fileSystem;

    /**
     * BlueprintCommand constructor.
     *
     * @param  FileSystem  $fileSystem  The Filesystem instance.
     */
    public function __construct(FileSystem $fileSystem)
    {
        parent::__construct();

        $this->fileSystem = $fileSystem;
    }

    /**
     * Handle the command.
     *
     * This method reads the blueprint file and executes the 'module-maker' command for each module defined in the blueprint.
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

        $blueprint = Yaml::parse($this->fileSystem->get(base_path('module-blueprint.yml')));

        foreach ($blueprint as $moduleName => $options) {
            $status = $this->call('module-maker', [
                'moduleName' => $moduleName,
                '--template' => $options['template'],
                '--exclude-stub' => $options['excludeStubs'] ?? ['*'],
                '--accept-risk' => true,
            ]);
        }

        $this->newLine(1);

        $total = count($blueprint);

        $this->alert("Blueprint has been executed successfully for $total modules.");

        return self::SUCCESS;
    }

    /**
     * Check if the 'accept-risk' option is set.
     *
     * This method checks if the 'accept-risk' option is set. If not, it warns the user about the potential risks and asks for confirmation.
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
}
