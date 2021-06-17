<?php

declare(strict_types=1);

namespace Brid\Database\Log\Handler;

use Brid\Database\Log\Formatter\EloquentFormatter;
use Carbon\Carbon;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;

class EloquentHandler extends AbstractProcessingHandler
{

  public function __construct($level = Logger::DEBUG, bool $bubble = true)
  {
    parent::__construct($level, $bubble);
  }

  protected function write(array $record): void
  {

    $context = $record['context'];

    unset($context['exception']);

    app()
      ->get('db')
      ->table('logs')
      ->insert($this->getRecord($record, $context));

  }

  /**
   * {@inheritDoc}
   */
  protected function getDefaultFormatter(): FormatterInterface
  {
    return new EloquentFormatter();
  }

  /**
   * @param array $record
   * @param array $context
   * @return array
   */
  protected function getRecord(array $record, array $context): array
  {
    return [
      'id' => (string) Uuid::uuid4(),
      'created_at' => Carbon::now(),
      'client_id' => $record['client_id'] ?? null,
      'user_id' => $record['user_id'] ?? null,
      'level' => strtolower($record['level_name']),
      'type' => $record['type'],
      'message' => $record['message'],
      'context' => json_encode($context),
      'extra' => json_encode($record['extra'] ?? []),
    ];
  }

}
