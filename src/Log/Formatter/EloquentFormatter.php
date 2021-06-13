<?php

declare(strict_types=1);

namespace Brid\Database\Log\Formatter;

use Illuminate\Support\Str;
use Monolog\Formatter\NormalizerFormatter;

class EloquentFormatter extends NormalizerFormatter
{

  /**
   * type
   */
  const LOG = 'log';
  const STORE = 'store';
  const CHANGE = 'change';
  const DELETE = 'delete';

  /**
   * result
   */
  const SUCCESS = 'success';
  const NEUTRAL = 'neutral';
  const FAILURE = 'failure';

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $record)
  {
    $record = parent::format($record);

    return $this->getDocument($record);
  }

  /**
   * Convert a log message into an DB Log entity
   *
   * @param array $record
   * @return array
   */
  protected function getDocument(array $record): array
  {
    $document = $record['extra'];
    $document['level'] = Str::lower($record['level_name']);
    $document['message'] = $record['message'];
    $context = $record['context'];

    if (!empty($context))
    {

      if (isset($context['exception'])) {
        $document['type'] = self::LOG;
        $document['result'] = self::FAILURE;
        $document['code'] ??= $context['exception']['code'] ?? null;
        $document['file'] ??= $context['exception']['file'] ?? null;
        $document['trace'] ??= $context['exception']['trace'] ?? null;
      } else {
        $document['type'] = $context['type'] ?? self::LOG;
        $document['result'] = $context['result'] ?? self::NEUTRAL;
      }

      $document = array_merge($record['context'], $document);
    }

    return $document;
  }
}
