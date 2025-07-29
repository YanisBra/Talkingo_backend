<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// Controller responsible for handling group leave requests.
class GroupLeaveController
{
    // Injects the EntityManager and Security service to access user and database operations.
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    // Handles the logic when a user requests to leave a group.
    // - If the user is the group creator and the only member, deletes the group.
    // - If the user is the group creator but there are other members, access is denied.
    // - Otherwise, removes the user's membership from the group.
    public function __invoke(Group $data): JsonResponse
    {
        $user = $this->security->getUser();

        $membership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $data,
        ]);

        // Check if the user is actually a member of the group
        if (!$membership) {
            throw new NotFoundHttpException("You are not a member of this group.");
        }

        // If the user is the creator of the group
        if ($data->getCreatedBy() === $user) {
            // Check if the user is the only member
            $memberships = $this->em->getRepository(GroupMembership::class)->findBy([
                'targetGroup' => $data,
            ]);

            // If the user is the only member, delete the group
            if (count($memberships) === 1) {
                // Remove both the membership and the group
                $this->em->remove($membership);
                $this->em->remove($data);
                $this->em->flush();

                return new JsonResponse(['message' => 'You left and deleted the group (only member).']);
            }

            // Deny access if the creator tries to leave while other members are still present
            throw new AccessDeniedHttpException("The group creator cannot leave the group while others are still members.");
        }

        // Remove the membership and confirm success
        $this->em->remove($membership);
        $this->em->flush();

        return new JsonResponse(['message' => 'Successfully left the group.']);
    }
}