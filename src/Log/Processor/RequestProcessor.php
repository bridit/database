<?php

declare(strict_types=1);

namespace Brid\Database\Log\Processor;

class RequestProcessor
{

  public function __invoke(array $record): array
  {

    $request = request();

    $record['client_id'] = $request->getAttribute('oauth_client_id');
    $record['user_id'] = $request->getAttribute('oauth_user_id');

    $url = $request->getUri()->getScheme() . '://' .
      $request->getUri()->getHost() .
      ($request->getUri()->getPort() ?? '') .
      $request->getUri()->getPath() .
      $request->getUri()->getQuery();

    $record['extra']['server'] = $request->server('SERVER_ADDR');
    $record['extra']['host'] = $request->getUri()->getHost();
    $record['extra']['origin'] = $request->header('origin');
    $record['extra']['uri'] = $request->getUri()->getPath();
    $record['extra']['request'] = [
      'client_ip' => $request->getClientIp(),
      'http_method' => $request->getMethod(),
      'headers' => $request->getHeaders(),
      'params' => $request->all(),
      'url' => $url,
      'user_agent' => $request->getClientUserAgent(),
    ];

    return $record;
  }

}
