<?php namespace AdamWathan\EloquentOAuth\Installation;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    protected $filesystem;
    protected $migrationPublisher;
    protected $name = 'eloquent-oauth:install';
    protected $description = 'Install package config and migrations';

    public function __construct(Filesystem $filesystem, MigrationPublisher $migrationPublisher)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->migrationPublisher = $migrationPublisher;
    }

    public function handle()
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->comment('Package configuration and migrations installed!');
    }

    public function publishConfig()
    {
        try {
            $this->publishFile(__DIR__ . '/../../config/config.php', config_path() . '/eloquent-oauth.php');
            $this->info('Configuration published.');
        } catch (FileExistsException $e) {
            $this->error('Package configuration already exists. Use --force to override.');
        }
    }

    public function publishMigrations()
    {
        $from = __DIR__ . '/../../migrations';
        $to = base_path() . '/database/migrations';
        $this->migrationPublisher->publish($from, $to);
        $this->info('Migrations published.');
    }

    public function publishFile($from, $to)
    {
        if ($this->filesystem->exists($to) && ! $this->option('force')) {
            throw new FileExistsException;
        }

        $this->filesystem->copy($from, $to);
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Overwrite any existing files.'],
        ];
    }

}
