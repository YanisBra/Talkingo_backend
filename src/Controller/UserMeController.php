<?php
namespace App\Controller;


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

        $data = $normalizer->normalize($user, null, ['groups' => ['user:read']]);

        return new JsonResponse($data);
    }
}