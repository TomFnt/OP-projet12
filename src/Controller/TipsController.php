<?php

namespace App\Controller;

use App\Entity\Tips;
use App\Repository\TipsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TipsController extends AbstractController
{
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour accèder à cette route')]
    #[Route('/api/tips', name: 'app_tips', methods: ['GET'])]
    public function getTips(TipsRepository $tipsRepository, SerializerInterface $serializer): JsonResponse
    {
        return $this->json($tipsRepository->findAll(), Response::HTTP_OK, [], (array) 'serializer');
    }

    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à créer un nouveau conseil')]
    #[Route('/api/tip', name: 'app_tip_create', methods: ['POST'])]
    public function createTip(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $tip = $serializer->deserialize($request->getContent(), Tips::class, 'json');

        $errors = $validator->validate($tip);

        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST, ['serializer']);
        }

        $em->persist($tip);
        $em->flush();

        return $this->json($tip, Response::HTTP_OK, ['serializer']);
    }
}
