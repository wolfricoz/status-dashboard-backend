<?php

namespace App\Service;


use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClient;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class BaseApi
{
  protected string $url;
  protected string $key;
  protected HttpClientInterface $client;


//    private string API_KEU = env()
  public function __construct($url_key, $api_key
  ) {
    $this->client = HttpClient::create();
    $this->url = $_ENV[$url_key];
    $this->key = $_ENV[$api_key];
  }

  public function urlBuilder($path)
  {
    return $this->url . $path;
  }

  public function send_request($path, $method = 'GET', $data = []): array
  {
    try {
      return $this->client->request(
        $method,
        $this->urlBuilder($path),
        [
          'headers' => [
            'token' => $this->key,
          ],
        ],
      )?->toArray();
    } catch (TransportException) {
      return [
        'status' => 'offline',

      ];

    } catch (\Exception $e) {
      return [
        'status' => 'error',
        'message' => $e->getMessage(),
      ];
    }
  }


}
