<?php namespace AdamWathan\EloquentOAuth\Installation;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    protected $filesystem;
    protected $name = 'eloquent-oauth:install';
    protected $description = 'Install package config and migrations';

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
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
            $this->publishFile(__DIR__ . '/../../config/eloquent-oauth.php', config_path() . '/eloquent-oauth.php');
            $this->info('Configuration published.');
        } catch (FileExistsException $e) {
            $this->error('Package configuration already exists. Use --force to override.');
        }
    }

    public function publishMigrations()
    {

        $name = 'create_oauth_identities_table';

        $path = $this->laravel['path'] . '/database/migrations';

        $fullPath = $this->laravel['migration.creator']->create($name, $path);

        $this->filesystem->put($fullPath, $this->files->get(__DIR__ . '/stubs/create_oauth_identities_table.stub'));

    }

    public function publishFile($from, $to)
    {
        if ($this->filesystem->exists($to) && !$this->option('force')) {
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
