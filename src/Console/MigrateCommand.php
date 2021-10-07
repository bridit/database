<?php

namespace Brid\Database\Console;

use Brid\Console\Commands\Command;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\SqlServerConnection;

class MigrateCommand extends Command
{

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected string $name = 'migrate';

  /**
   * The console command description.
   *
   * @var string
   */
  protected string $description = 'Run the database migrations';

  /**
   * The migrator instance.
   *
   * @var Migrator
   */
  protected $migrator;

  /**
   * The migrator instance.
   *
   * @var DatabaseMigrationRepository
   */
  protected DatabaseMigrationRepository $repository;

  /**
   * Create a new migration command instance.
   *
   * @param string|null $name
   * @return void
   */
  public function __construct(string $name = null)
  {
    parent::__construct($name);

    $resolver = app('db');
    $this->repository = new DatabaseMigrationRepository($resolver, 'migrations');
    $this->migrator = new Migrator($this->repository, $resolver, new \Illuminate\Filesystem\Filesystem());
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {

    if (config('app.env') === 'production') {
      $this->alert('Application In Production!');

      $confirmed = $this->confirm('Do you really wish to run this command?');

      if (! $confirmed) {
        $this->comment('Command Canceled!');

        return 1;
      }

    }

    $this->migrator->usingConnection('default', function () {
      $this->prepareDatabase();

      // Next, we will check to see if a path option has been defined. If it has
      // we will use the path relative to the root of this installation folder
      // so that migrations may be run for any path within the applications.
      $this->migrator->setOutput($this->output)
        ->run($this->getMigrationPaths(), [
          'pretend' => false, // $this->option('pretend'),
          'step' => false, // $this->option('step'),
        ]);
    });

    return 0;
  }

  /**
   * Prepare the migration database for running.
   *
   * @return void
   */
  protected function prepareDatabase()
  {
    if (!$this->migrator->repositoryExists()) {
      $this->install();
    }

    if (!$this->migrator->hasRunAnyMigrations()) {
      $this->loadSchemaState();
    }
  }

  protected function install(): void
  {
    $this->repository->setSource('default');

    $this->repository->createRepository();

    $this->info('Migration table created successfully.');
  }

  /**
   * Load the schema state to seed the initial database schema structure.
   *
   * @return void
   */
  protected function loadSchemaState()
  {
    $connection = $this->migrator->resolveConnection('default');

    // First, we will make sure that the connection supports schema loading and that
    // the schema file exists before we proceed any further. If not, we will just
    // continue with the standard migration operation as normal without errors.
    if ($connection instanceof SqlServerConnection ||
      !is_file($path = $this->schemaPath($connection)))
    {
      return;
    }

    $this->line('<info>Loading stored database schema:</info> ' . $path);

    $startTime = microtime(true);

    // Since the schema file will create the "migrations" table and reload it to its
    // proper state, we need to delete it here so we don't get an error that this
    // table already exists when the stored database schema file gets executed.
    $this->migrator->deleteRepository();

    $connection->getSchemaState()->handleOutputUsing(function ($type, $buffer) {
      $this->output->write($buffer);
    })->load($path);

    $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

    $this->line('<info>Loaded stored database schema.</info> (' . $runTime . 'ms)');
  }

  /**
   * Get the path to the stored schema for the given connection.
   *
   * @param \Illuminate\Database\Connection $connection
   * @return string
   */
  protected function schemaPath($connection)
  {
    return '';

//    if (file_exists($path = database_path('schema/' . $connection->getName() . '-schema.dump')))
//    {
//      return $path;
//    }
//
//    return database_path('schema/' . $connection->getName() . '-schema.sql');
  }

  /**
   * Get all of the migration paths.
   *
   * @return array
   */
  protected function getMigrationPaths()
  {
    // Here, we will check to see if a path option has been defined. If it has we will
    // use the path relative to the root of the installation folder so our database
    // migrations may be run for any customized path from within the application.
    if ($this->input->hasOption('path') && $this->option('path'))
    {
      return collect($this->option('path'))->map(function ($path) {
        return !$this->usingRealPath()
          ? $this->laravel->basePath() . '/' . $path
          : $path;
      })->all();
    }

    return array_merge(
      $this->migrator->paths(), [$this->getMigrationPath()]
    );
  }

  /**
   * Determine if the given path(s) are pre-resolved "real" paths.
   *
   * @return bool
   */
  protected function usingRealPath()
  {
    return $this->input->hasOption('realpath') && $this->option('realpath');
  }

  /**
   * Get the path to the migration directory.
   *
   * @return string
   */
  protected function getMigrationPath()
  {
    return path('database/migrations');
  }

}
