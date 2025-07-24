<?php
// src/Controller/UserMeController.php
namespace App\Controller;

// use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Bundle\SecurityBundle\Security;
// use App\Entity\User;

// class UserMeController extends AbstractController
// {
//     #[Route('/api/users/me', name: 'api_user_me', methods: ['GET'])]
//     public function __invoke(Security $security): JsonResponse
//     {
//         /** @var User $user */
//         $user = $security->getUser();

//         return $this->json($user, context: ['groups' => 'user:read']);
//     }
// }

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/users/me', name: 'api_user_me', methods: ['GET'], priority: 1)]
class UserMeController extends AbstractController
{
    public function __invoke(Security $security, NormalizerInterface $normalizer): JsonResponse
    {
        $user = $security->getUser();

        // Normalise sans générer de @id = /api/users/me
        $data = $normalizer->normalize($user, null, ['groups' => ['user:read']]);

        return new JsonResponse($data);
    }
}