<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\WeatherForecast;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class WeatherController extends AbstractController
{
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour accèder à cette route')]
    #[Route('api/weather/{town}', name: 'app_weather_by_town', methods: ['GET'])]
    public function getWeatherByTown(
        WeatherForecast $weatherForecast,
        string $town
    ): JsonResponse
    {
        return $this->json($weatherForecast->getWeatherForecastByTown($town), Response::HTTP_OK, [], (array) 'serializer');
    }
}
