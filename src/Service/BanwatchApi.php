<?php

namespace App\Service;


use Symfony\Component\HttpClient\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class BanwatchApi extends BaseApi
{



//    private string API_KEU = env()
  public function __construct(
  ) {
    parent::__construct('BANWATCH_URL', 'BANWATCH_KEY');
  }


}
