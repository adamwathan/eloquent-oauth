<?php namespace AdamWathan\EloquentOAuth\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    protected $name = 'eloquent-oauth:install';

    protected $description = 'Install package config and migrations';

    protected $filesystem;

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
        $this->publishFile(__DIR__ . '/../../config/config.php', config_path() . '/eloquent-oauth.php');
    }

    public function publishMigrations()
    {
        $from = __DIR__ . '/../../migrations/create_oauth_identities_table.php';
        $to = base_path() . '/database/migrations/' . date('Y_m_d_His') . '_' . 'create_oauth_identities_table.php';
        $this->publishFile($from, $to);
    }

    public function publishFile($from, $to)
    {
        if ($this->filesystem->exists($to) && ! $this->option('force')) {
            return;
        }

        $this->filesystem->copy($from, $to);

        $this->info('Published: ' . $to);
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Overwrite any existing files.'],
        ];
    }

}
