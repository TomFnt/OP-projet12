<?php

namespace App\Controller;

use App\Repository\TipsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TipsController extends AbstractController
{
    #[Route('/api/tips', name: 'app_tips', methods: ['GET'])]
    public function getTips(TipsRepository $tipsRepository, SerializerInterface $serializer): JsonResponse
    {
        return $this->json($tipsRepository->findAll(), Response::HTTP_OK, [], (array) 'serializer');
    }
}
