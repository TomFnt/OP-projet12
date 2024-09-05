<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;


class WeatherForecast
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }


    public function getWeatherForecastByTown(string $town): array
    {
        $url = sprintf(
            'http://api.openweathermap.org/data/2.5/weather?q=%s&appid=%s&units=metric',
            urlencode($town),
            $this->apiKey
        );

        $response = $this->httpClient->request('GET', $url);
        return $response->toArray();
    }
}