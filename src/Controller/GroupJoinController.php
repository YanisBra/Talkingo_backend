<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class GroupJoinController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private RateLimiterFactory $joinGroupLimiter,

    ) {}

    #[Route('/api/groups/join', name: 'group_join', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Get the currently authenticated user
        $user = $this->security->getUser();

        // Decode the JSON content from the request body
        $data = json_decode($request->getContent(), true);

        // Extract the invitation code from the request data
        $code = $data['invitationCode'] ?? null;

        // Create a rate limiter instance for the client's IP address
        $limiter = $this->joinGroupLimiter->create($request->getClientIp());

        // Consume one token from the rate limiter and check if the request is allowed
        if (!$limiter->consume(1)->isAccepted()) {
            // Too many requests, throw a 429 Too Many Requests HTTP exception
            throw new TooManyRequestsHttpException(null, "Too many attempts to join a group. Please try again later.");
        }

        // Check if the invitation code was provided
        if (!$code) {
            // Return a 400 Bad Request response if no code is present
            return new JsonResponse(['error' => 'Invitation code is required'], 400);
        }

        // Look up the group entity based on the invitation code
        $group = $this->em->getRepository(Group::class)->findOneBy(['invitationCode' => $code]);

        // If no group is found, return a 404 Not Found response
        if (!$group) {
            return new JsonResponse(['error' => 'Group not found'], 404);
        }

        // Check if the user is already a member of the group
        $existingMembership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $group,
        ]);

        // If the user is already a member, return a 409 Conflict response
        if ($existingMembership) {
            return new JsonResponse(['error' => 'You already joined this group'], 409);
        }

        // Create a new GroupMembership entity to link the user with the group
        $membership = new GroupMembership();
        $membership->setUser($user);
        $membership->setTargetGroup($group);
        $membership->setJoinedAt(new \DateTimeImmutable());

        // Persist the new membership to the database
        $this->em->persist($membership);
        $this->em->flush();

        // Return a success response indicating the user joined the group
        return new JsonResponse(['success' => 'You joined the group']);
    }
}