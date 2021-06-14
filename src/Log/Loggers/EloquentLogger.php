<?php

declare(strict_types=1);

namespace Brid\Database\Log\Loggers;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Brid\Database\Log\Handler\EloquentHandler;
use Brid\Database\Log\Processor\ContextProcessor;
use Brid\Database\Log\Processor\RequestProcessor;

class EloquentLogger
{

  public function __invoke(array $config): LoggerInterface
  {

    $logger = new Logger('eloquent');
    $logger->pushHandler(new EloquentHandler());
    $logger->pushProcessor(new ContextProcessor());

    if (APP_HANDLER_TYPE === 'http') {
      $logger->pushProcessor(new RequestProcessor());
    }

    return $logger;

  }

}
