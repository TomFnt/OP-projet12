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
     * Permet de récupèrer une prévision météo sur 5 jours pour une ville spécifique.
     */
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour accèder à cette route')]
    #[Route('api/meteo/{town}', name: 'app_weather_by_town', methods: ['GET'])]
    #[OA\Parameter(
        name: 'town',
        in: 'path',
        description: "Correspond au nom de la ville qu'on souhaites obtenir la prévision météo.",
        schema: new OA\Schema(type: 'string', example: 'London')
    )]
    #[OA\Response(
        response: 200,
        description: "Retourne la dernière prévision météo concernant cette ville, soit trouvé dans un cache datant d'il y a moins de 3 jours, ou obtenu directement via l'appel API OpenWeather Map .",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: 'dt',
                        type: 'integer',
                        example: 1726768800
                    ),
                    new OA\Property(
                        property: 'main',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'temp', type: 'number', format: 'float', example: 23.16),
                            new OA\Property(property: 'feels_like', type: 'number', format: 'float', example: 22.97),
                            new OA\Property(property: 'temp_min', type: 'number', format: 'float', example: 20.91),
                            new OA\Property(property: 'temp_max', type: 'number', format: 'float', example: 23.16),
                            new OA\Property(property: 'pressure', type: 'integer', example: 1023),
                            new OA\Property(property: 'humidity', type: 'integer', example: 55),
                        ]
                    ),
                    new OA\Property(
                        property: 'weather',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 800),
                                new OA\Property(property: 'main', type: 'string', example: 'Clear'),
                                new OA\Property(property: 'description', type: 'string', example: 'clear sky'),
                                new OA\Property(property: 'icon', type: 'string', example: '01d'),
                            ]
                        )
                    ),
                    new OA\Property(
                        property: 'clouds',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'all', type: 'integer', example: 2),
                        ]
                    ),
                    new OA\Property(
                        property: 'wind',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'speed', type: 'number', format: 'float', example: 4.4),
                            new OA\Property(property: 'deg', type: 'integer', example: 65),
                            new OA\Property(property: 'gust', type: 'number', format: 'float', example: 9.33),
                        ]
                    ),
                    new OA\Property(
                        property: 'visibility',
                        type: 'integer',
                        example: 10000
                    ),
                    new OA\Property(
                        property: 'dt_txt',
                        type: 'string',
                        example: '2024-09-19 18:00:00'
                    ),
                ]
            )
        )
    )]
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
        response: 404,
        description: "La ville saisie par l'utilisateur n'est pas valide ou reconnu (exemple : Londre au lieu de London).",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 404
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: "La ville n'est pas valide ou reconnu (exemple : Londre au lieu de London)."
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 429,
        description: "Cette erreur indique qu'il y a eu trop de requêtes vers l'API OpenWeatherMap par rapport à l'abonnement que vous avez. Le message d'erreur est donc modifier pour l'utilisateur",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 429
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Une erreur est survenue lors de la récupèration des prévisions météo, veuillez ressayer ultérieurment.'
                ),
            ]
        )
    )]
    #[OA\Tag(name: 'Prévision météo')]
    public function getWeatherForecastBySpecificTown(
        WeatherForecast $weatherForecast,
        string $town
    ): JsonResponse {
        return $this->json($weatherForecast->getWeatherForecast($town), Response::HTTP_OK, [], (array) 'serializer');
    }

    /**
     * Permet de récupèrer une prévision météo sur 5 jours en se basant sur la ville renseignée sur le compte de l'utilisateur.
     */
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour accèder à cette route')]
    #[Route('api/meteo', name: 'app_weather_nullable', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Retourne la dernière prévision météo concernant pour la ville saisie par l'utilisateur lors de son inscription, soit trouvé dans un cache datant d'il y a moins de 3 jours, ou obtenu directement via l'appel API OpenWeather Map .",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: 'dt',
                        type: 'integer',
                        example: 1726768800
                    ),
                    new OA\Property(
                        property: 'main',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'temp', type: 'number', format: 'float', example: 23.16),
                            new OA\Property(property: 'feels_like', type: 'number', format: 'float', example: 22.97),
                            new OA\Property(property: 'temp_min', type: 'number', format: 'float', example: 20.91),
                            new OA\Property(property: 'temp_max', type: 'number', format: 'float', example: 23.16),
                            new OA\Property(property: 'pressure', type: 'integer', example: 1023),
                            new OA\Property(property: 'humidity', type: 'integer', example: 55),
                        ]
                    ),
                    new OA\Property(
                        property: 'weather',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 800),
                                new OA\Property(property: 'main', type: 'string', example: 'Clear'),
                                new OA\Property(property: 'description', type: 'string', example: 'clear sky'),
                                new OA\Property(property: 'icon', type: 'string', example: '01d'),
                            ]
                        )
                    ),
                    new OA\Property(
                        property: 'clouds',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'all', type: 'integer', example: 2),
                        ]
                    ),
                    new OA\Property(
                        property: 'wind',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'speed', type: 'number', format: 'float', example: 4.4),
                            new OA\Property(property: 'deg', type: 'integer', example: 65),
                            new OA\Property(property: 'gust', type: 'number', format: 'float', example: 9.33),
                        ]
                    ),
                    new OA\Property(
                        property: 'visibility',
                        type: 'integer',
                        example: 10000
                    ),
                    new OA\Property(
                        property: 'dt_txt',
                        type: 'string',
                        example: '2024-09-19 18:00:00'
                    ),
                ]
            )
        )
    )]
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
        response: 404,
        description: "La ville renseigné sur le compte de l'utilisateur n'est pas valide ou reconnu (exemple : Londre au lieu de London).",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 404
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: "La ville n'est pas valide ou reconnu (exemple : Londre au lieu de London)."
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 429,
        description: "Cette erreur indique qu'il y a eu trop de requêtes vers l'API OpenWeatherMap par rapport à l'abonnement que vous avez. Le message d'erreur est donc modifier pour l'utilisateur",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 429
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Une erreur est survenue lors de la récupèration des prévisions météo, veuillez ressayer ultérieurment.'
                ),
            ]
        )
    )]
    #[OA\Tag(name: 'Prévision météo')]
    public function getWeatherByUserTown(WeatherForecast $weatherForecast): JsonResponse
    {
        $town = $this->getUser()->getCity();

        return $this->json($weatherForecast->getWeatherForecast($town), Response::HTTP_OK);
    }
}
