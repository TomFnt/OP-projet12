<?php

namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherForecast
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->cache = $cache;
    }

    public function getWeatherForecast(string $town): array
    {
        return $this->callOpenWeatherMap($town);
    }

    /* Call API OpenWeatherMap in order to collect 5 days of weather forecast from specific town, and stock response in cache */
    public function callOpenWeatherMap(string $town)
    {
        return $this->cache->get($town, function (ItemInterface $item) use ($town) {
            $item->expiresAfter(259200); // expire after 3 days

            $url = sprintf(
                'http://api.openweathermap.org/data/2.5/forecast?q=%s&appid=%s&units=metric',
                urlencode($town),
                $this->apiKey
            );

            $response = $this->httpClient->request('GET', $url);

            match ($response->getStatusCode() ){
                404=> throw new HttpException($response->getStatusCode(), "La ville n'est pas valide ou reconnu (exemple : Londre au lieu de London) "),
                401=> throw new HttpException()
            };

            $responseData = $response->toArray();

            return $responseData['list'];
        });
    }
}
