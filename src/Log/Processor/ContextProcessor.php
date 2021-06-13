<?php

declare(strict_types=1);

namespace Brid\Database\Log\Processor;

use Throwable;

class ContextProcessor
{

  public function __invoke(array $record): array
  {

    $record['type'] = null;
    $context = $record['context'] ?? [];

    if (isset($context['type']) && is_string($context['type']) && strlen($context['type']) <= 255) {
      $record['type'] = $context['type'];
      unset($context['type']);
    }

    if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
      $context['code'] = $context['exception']->getCode();
      $context['file'] = $context['exception']->getFile();
      $context['line'] = $context['exception']->getLine();
      $context['trace'] = $context['exception']->getTraceAsString();
    }

    $record['context'] = $context;

    return $record;
  }

}
