<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GroupLeaveController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function __invoke(Group $data): JsonResponse
    {
        $user = $this->security->getUser();

        $membership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $data,
        ]);

        if (!$membership) {
            throw new NotFoundHttpException("You are not a member of this group.");
        }

        if ($data->getCreatedBy() === $user) {
            throw new AccessDeniedHttpException("The group creator cannot leave the group.");
        }

        $this->em->remove($membership);
        $this->em->flush();

        return new JsonResponse(['message' => 'Successfully left the group.']);
    }
}