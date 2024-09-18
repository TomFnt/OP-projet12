<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\WeatherForecast;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class WeatherController extends AbstractController
{
    /**
     * Permet de récupèrer les prévisions météo sur 5 jours dans une ville spécifique.
     */
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour accèder à cette route')]
    #[Route('api/meteo/{town}', name: 'app_weather_by_town', methods: ['GET'])]
    #[Route('api/meteo', name: 'app_weather_nullable', methods: ['GET'])]
    #[OA\Parameter(
        name: 'town',
        in: 'path',
        description: "Correspond au nom de la ville qu'on souhaites obtenir la prévision météo.",
        schema: new OA\Schema(type: 'string', example: 'London')
    )]
    #[OA\Response(
        response: 200,
        description: "Retourne la dernière prévision météo concernant cette ville, soit trouvé dans un cache datant d'il y a moins de 3 jours, ou obtenu directement via l'appel API OpenWeather Map .",
        content: null
    )] /* To do : return example of forecast, custom example or OA\Schema ? */
    #[OA\Response(
        response: 401,
        description: "Retourne ce message d'erreur si le token JWT n'est pas valide.",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 401
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Invalid JWT Token'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Indique que l'utilisateur doit se connecter pour accèder à cette route.",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 403
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Vous devez vous connecter pour accèder à cette route'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Cela est dû a une erreur lors de l'appel de l'API OpenWeather Map. Cela peut être dû par exemple au nom d'une ville qui n'est pas détecter par OpenWeatherMap. On retourne alors directement le message d'erreur qu'on obtient en réponse de la part de OpenWeatherMap.",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 500
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'HTTP/1.1 404 Not Found returned for "http://api.openweathermap.org/data/2.5/forecast?q=Londre&appid=a04eaccdd0b38a79409e98dc7bfa34c5&units=metric".'
                ),
            ]
        )
    )]
    #[OA\Tag(name: 'Prévision météo')]
    public function getWeatherForecast(
        WeatherForecast $weatherForecast,
        string $town = ''
    ): JsonResponse {
        /* get town specify in User account if user does'nt set a town in their api call */
        if ('' == $town) {
            $town = $this->getUser()->getCity();
        }

        return $this->json($weatherForecast->getWeatherForecast($town), Response::HTTP_OK, [], (array) 'serializer');
    }
}
