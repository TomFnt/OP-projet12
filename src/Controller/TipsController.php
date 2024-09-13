<?php

namespace App\Controller;

use App\Entity\Tips;
use App\Repository\TipsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TipsController extends AbstractController
{
    /**
     * Permet d'obtenir l'ensemble des conseils.
     *
     * @param TipsRepository $tipsRepository
     * @return JsonResponse
     */
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour accèder à cette route')]
    #[Route('/api/conseils', name: 'app_tips', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Retourne l'ensemble des conseils.",
        content: new OA\JsonContent(ref: new Model(type: Tips::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Retourne ce message d\'erreur si le token JWT n\'est pas valide.',
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
                )
            ]
        )
    )]
    #[OA\Tag(name: 'Conseil(s)')]
    public function getTips(TipsRepository $tipsRepository): JsonResponse
    {
        return $this->json($tipsRepository->findAll(), Response::HTTP_OK, [], (array) 'serializer');
    }

    /**
     * Permet d'obtenir les conseils qui sont destinés à un mois spécifique
     * @param TipsRepository $tipsRepository
     * @param int $month
     * @return JsonResponse
     */
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour accèder à cette route')]
    #[Route('/api/conseils/{month}', name: 'app_tips_by_month', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Retourne l'ensemble des conseils qui ont dans leur liste de mois le mois recherché.",
        content: new OA\JsonContent(ref: new Model(type: Tips::class))
    )]
    #[OA\Parameter(
        name: 'month',
        in: 'path',
        description: "Définis le mois qu'on souhaites utiliser pour affiner la recherche des conseils, le mois doit être indiqué en chiffre et il doit compris entre 1 et 12.",
        schema: new OA\Schema(type: 'integer', example: 8)
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
                )
            ]
        )
    )]
    #[OA\Tag(name: 'Conseil(s)')]
    public function getTipsByMonth(
        TipsRepository $tipsRepository,
        int $month): JsonResponse
    {
        if ($month < 1 || $month > 12) {
            return $this->json(['error' => 'Le mois que vous avez saisi est invalide. Cela doit être un nombre entier compris entre 1 et 12.'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($tipsRepository->findByMonth($month), Response::HTTP_OK, [], (array) 'serializer');
    }

    /**
     * Permet de créer un nouveau conseil.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à créer un nouveau conseil')]
    #[Route('/api/conseil', name: 'app_tip_create', methods: ['POST'])]
    #[OA\RequestBody(
        description: "Les informations nécessaires pour créer un conseil :",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    description: 'Correspond à la description du conseil.',
                    example: 'Conseil n°41 : ...'
                ),
                new OA\Property(
                    property: 'month_list',
                    type: 'array',
                    description: "Correspond à l'ensemble des mois associés à ce conseil, sous la forme d'une liste de nombres entre 1 et 12.",
                    items: new OA\Items(type: 'integer'),
                    example: [12, 1, 2]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Confirme la création du nouveau conseil.",
        content: new OA\JsonContent(ref: new Model(type: Tips::class))
    )]
    #[OA\Response(
        response: 400,
        description: "Retourne ce message d'erreur si les informations saisies ne sont pas correctes.",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 400
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: "month_list: La valeur \"13\" dans la liste \"month_list\" doit-être un nombre entier entre 1 et 12.",
                )
            ]
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
                )
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Indique que l'utilisateur n'as pas les droits pour créer un nouveau conseil.",
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
                    example: "Vous n'êtes pas autorisé à créer un nouveau conseil"
                )
            ]
        )
    )]
    #[OA\Tag(name: 'Conseil(s)')]
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

        return $this->json($tip, Response::HTTP_CREATED, ['serializer']);
    }

    /**
     * Permet de modifier les informations d'un conseil.
     *
     * @param Request $request
     * @param Tips $currentTip
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à modifier un conseil')]
    #[Route('/api/conseil/{id}', name: 'app_tip_edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: "Correspond à l'id du conseil qu'on souhaites modifier.",
        schema: new OA\Schema(type: 'integer', example: 8)
    )]
    #[OA\RequestBody(
        description: "Les informations nécessaires pour modifier un conseil :",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    description: 'Correspond à la description du conseil.',
                    example: 'Conseil n°41 : ...'
                ),
                new OA\Property(
                    property: 'month_list',
                    type: 'array',
                    description: "Correspond à l'ensemble des mois associés à ce conseil, sous la forme d'une liste de nombres entre 1 et 12.",
                    items: new OA\Items(type: 'integer'),
                    example: [12, 1, 2]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Confirme la modification du conseil."
    )]
    #[OA\Response(
        response: 400,
        description: "Retourne ce message d'erreur si les informations saisies ne sont pas correctes.",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    example: 400
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: "month_list: La valeur \"13\" dans la liste \"month_list\" doit-être un nombre entier entre 1 et 12.",
                )
            ]
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
                )
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Indique que l'utilisateur n'as pas les droits pour créer un nouveau conseil.",
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
                    example: "Vous n'êtes pas autorisé à modifier un conseil"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Le conseil n'a pas pu être trouvé avec l'id saisie par l'administrateur.",
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
                    example: "\"App\\Entity\\Tips\" object not found by \"Symfony\\Bridge\\Doctrine\\ArgumentResolver\\EntityValueResolver\"."
                )
            ]
        )
    )]
    #[OA\Tag(name: 'Conseil(s)')]
    public function editTip(
        Request $request,
        Tips $currentTip,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $updatedTip = $serializer->deserialize(
            $request->getContent(),
            Tips::class,
            'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTip]);

        $errors = $validator->validate($updatedTip);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST, ['serializer']);
        }

        $em->persist($updatedTip);
        $em->flush();

        return $this->json(null, Response::HTTP_OK);
    }

    /**
     * Permet de supprimer un conseil
     *
     * @param Tips $tip
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à supprimer un conseil')]
    #[Route('/api/conseil/{id}', name: 'app_tip_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: "Correspond à l'id du conseil qu'on souhaites supprimer.",
        schema: new OA\Schema(type: 'integer', example: 8)
    )]
    #[OA\Response(
        response: 200,
        description: "Confirme la suppression du conseil.",
        content: new OA\JsonContent(ref: new Model(type: Tips::class))
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
                )
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Indique que l'utilisateur n'as pas les droits pour créer un nouveau conseil.",
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
                    example: "Vous n'êtes pas autorisé à supprimer un conseil"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Le conseil n'a pas pu être trouvé avec l'id saisie par l'administrateur.",
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
                    example: "\"App\\Entity\\Tips\" object not found by \"Symfony\\Bridge\\Doctrine\\ArgumentResolver\\EntityValueResolver\"."
                )
            ]
        )
    )]
    #[OA\Tag(name: 'Conseil(s)')]
    public function deleteTip(Tips $tip, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($tip);
        $em->flush();

        return $this->json(null, Response::HTTP_OK);
    }
}
