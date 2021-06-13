<?php

namespace Brid\Database;

use Brid\Core\Foundation\Providers\ServiceProvider;

class EloquentServiceProvider extends ServiceProvider
{

  public function boot()
  {

    $databaseConfig = config('database');
    
    $default = $databaseConfig['default'];
    $connections = $databaseConfig['connections'] ?? [];
    $managerClassName = '\Illuminate\Database\Capsule\Manager';

    $capsule = new $managerClassName;

    foreach ($connections as $name => $config)
    {
      $capsule->addConnection($config, ($name === $default ? 'default' : $name));
    }

//    $capsule->setEventDispatcher(new Dispatcher(new Container));

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $this->container->set('db', $capsule);

  }

}