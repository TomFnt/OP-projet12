<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * Permet de créer un nouveau compte utilisateur.
     */
    #[Route('api/user', name: 'app_user_create', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Les informations nécessaires pour créer un conseil :',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'login',
                    type: 'string',
                    description: "correspond à l'email de l'utilisateur, sert aussi de login de connexion",
                    example: 'john.doe@gmail.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    description: "Correspond au mot de passe du compte de l'utilisateur",
                    example: '1Mot2passe'
                ),
                new OA\Property(
                    property: 'city',
                    type: 'string',
                    description: "Correspond à la ville de résidence de l'utilisateur",
                    example: 'Paris'
                ),
                new OA\Property(
                    property: 'country',
                    type: 'string',
                    description: "Correspond au pays de résidence de l'utilisateur",
                    example: 'France'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Confirme la création d'un nouvel utilisateur.",
        content: new OA\JsonContent(ref: new Model(type: User::class))
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
                    example: "login: Ce nom d'utilisateur est déjà utilisé",
                ),
            ]
        )
    )]
    #[OA\Tag(name: 'Utilisateur')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // check if user data are correct before hash password and create new User.
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST, ['serializer']);
        }

        // get user password & hash them before user creation.
        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $this->json($user, Response::HTTP_OK, ['serializer']);
    }

    /**
     * Permet de modifier les informations d'un compte utilisateur.
     */
    #[Route('api/user/{id}', name: 'app_user_edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à éditer un compte utilisateur')]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: "Correspond à l'id de l'utilisateur qu'on souhaites modifier.",
        schema: new OA\Schema(type: 'integer', example: 8)
    )]
    #[OA\RequestBody(
        description: 'Les informations nécessaires pour créer un conseil :',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'login',
                    type: 'string',
                    description: "correspond à l'email de l'utilisateur, sert aussi de login de connexion",
                    example: 'john.doe@gmail.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    description: "Correspond au mot de passe du compte de l'utilisateur",
                    example: '1Mot2passe'
                ),
                new OA\Property(
                    property: 'city',
                    type: 'string',
                    description: "Correspond à la ville de résidence de l'utilisateur",
                    example: 'Paris'
                ),
                new OA\Property(
                    property: 'country',
                    type: 'string',
                    description: "Correspond au pays de résidence de l'utilisateur",
                    example: 'France'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Confirme la création d'un nouvel utilisateur.",
        content: new OA\JsonContent(ref: new Model(type: User::class))
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
                    example: 'password: Le mot de passe doit comporter au moins 8 caractères.',
                ),
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
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Indique que l'utilisateur n'as pas les droits pour modifier les informations d'un compte utilisateur.",
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
                    example: "Vous n'êtes pas autorisé à éditer un compte utilisateur"
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "L'utilisateur n'a pas pu être trouvé avec l'id saisie par l'administrateur.",
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
                    example: '"App\\Entity\\User" object not found by "Symfony\\Bridge\\Doctrine\\ArgumentResolver\\EntityValueResolver".'
                ),
            ]
        )
    )]
    #[OA\Tag(name: 'Utilisateur')]
    public function editUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        User $currentUser,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $updatedUser = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);

        $errors = $validator->validate($updatedUser);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST, ['serializer']);
        }

        // check if password are send in request & hash them before user edition.
        $requestData = json_decode($request->getContent(), true);

        if (array_key_exists('password', $requestData)) {
            $hashedPassword = $passwordHasher->hashPassword($currentUser, $updatedUser->getPassword());
            $updatedUser->setPassword($hashedPassword);
        }

        $em->persist($updatedUser);
        $em->flush();

        return $this->json(null, Response::HTTP_OK);
    }

    /**
     * Permet de supprimer le compte d'un utilisateur.
     */
    #[Route('/api/user/{id}', name: 'app_user_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à supprimer un utilisateur')]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: "Correspond à l'id de l'utilisateur qu'on souhaite supprimer.",
        schema: new OA\Schema(type: 'integer', example: 8)
    )]
    #[OA\Response(
        response: 200,
        description: "Confirme la suppression de l'utilisateur.",
        content: null
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
        description: "Indique que l'utilisateur n'as pas les droits pour supprimer un autre utilisateur.",
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
                    example: "Vous n'êtes pas autorisé à supprimer un utilisateur"
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Le compte utilisateur n'a pas pu être trouvé avec l'id saisie par l'administrateur.",
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
                    example: '"App\\Entity\\User" object not found by "Symfony\\Bridge\\Doctrine\\ArgumentResolver\\EntityValueResolver".'
                ),
            ]
        )
    )]
    #[OA\Tag(name: 'Utilisateur')]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
