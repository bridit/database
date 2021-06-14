<?php

declare(strict_types=1);

namespace Brid\Database\Log\Loggers;

use Brid\Database\Log\Handler\EloquentHandler;
use Brid\Database\Log\Processor\ContextProcessor;
use Brid\Database\Log\Processor\RequestProcessor;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class EloquentLogger
{

  public function __invoke(array $config): LoggerInterface
  {

    $logger = new Logger('eloquent');
    $logger->pushHandler($this->getHandler());
    $logger->pushProcessor($this->getContextProcessor());

    if (APP_HANDLER_TYPE === 'http') {
      $logger->pushProcessor($this->getRequestProcessor());
    }

    return $logger;

  }

  /**
   * @return AbstractProcessingHandler
   */
  protected function getHandler(): AbstractProcessingHandler
  {
    return new EloquentHandler();
  }

  protected function getContextProcessor()
  {
    return new ContextProcessor();
  }

  protected function getRequestProcessor()
  {
    return new RequestProcessor();
  }

}
