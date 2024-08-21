<?php

namespace App\Controller;

use App\Repository\TipsRepository;
use Masterminds\HTML5\Serializer\RulesInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TipsController extends AbstractController
{
    #[Route('/api/tips', name: 'app_tips')]
    public function getTips(TipsRepository $tipsRepository, SerializerInterface $serializer): JsonResponse
    {
        $tips_list = $tipsRepository->findAll();

        if($tips_list){
            $json_response = $serializer->serialize($tips_list, 'json');
            return new JsonResponse($json_response, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
