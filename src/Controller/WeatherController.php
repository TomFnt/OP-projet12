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
    #[Route('api/weather', name: 'app_weather_nullable', methods: ['GET'])]
    public function getWeatherForecast(
        WeatherForecast $weatherForecast,
        string $town = ''
    ): JsonResponse {
        if ('' == $town) {
            $town = $this->getUser()->getCity();
        }

        return $this->json($weatherForecast->getWeatherForecast($town), Response::HTTP_OK, [], (array) 'serializer');
    }
}
