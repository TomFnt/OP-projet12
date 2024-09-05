<?php

namespace App\Service;

use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherForecast
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey, TagAwareCacheInterface $cachePool)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->cachePool = $cachePool;
    }

    public function getWeatherForecast(string $town): array
    {
        return $this->callOpenWeatherMap($town);


    }

    public function callOpenWeatherMap(string $town)
    {
        $url = sprintf(
            'http://api.openweathermap.org/data/2.5/forecast?q=%s&appid=%s&units=metric',
            urlencode($town),
            $this->apiKey
        );

        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $response = $response->toArray();

        return $response["list"];
    }
}
