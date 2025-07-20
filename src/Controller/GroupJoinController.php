<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupJoinController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    #[Route('/api/groups/join', name: 'group_join', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        $data = json_decode($request->getContent(), true);
        $code = $data['invitationCode'] ?? null;

        if (!$code) {
            return new JsonResponse(['error' => 'Invitation code is required'], 400);
        }

        $group = $this->em->getRepository(Group::class)->findOneBy(['invitationCode' => $code]);

        if (!$group) {
            return new JsonResponse(['error' => 'Group not found'], 404);
        }

        // Check if already member
        $existingMembership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $group,
        ]);

        if ($existingMembership) {
            return new JsonResponse(['error' => 'You already joined this group'], 409);
        }

        $membership = new GroupMembership();
        $membership->setUser($user);
        $membership->setTargetGroup($group);
        $membership->setJoinedAt(new \DateTimeImmutable());

        $this->em->persist($membership);
        $this->em->flush();

        return new JsonResponse(['success' => 'You joined the group']);
    }
}