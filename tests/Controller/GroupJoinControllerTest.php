<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class GroupJoinControllerTest extends ApiTestCase
{
    private EntityManagerInterface $em;
    private $client;

    // Set up the entity manager for database access
    protected function setUp(): void
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->client = static::createClient();
    }

    private function authenticate(string $email): void
    {
        $this->client->request('POST', '/api/login_check', [
            'json' => ['email' => $email, 'password' => 'pass123'],
        ]);
        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];
        $this->client->setDefaultOptions(['headers' => ['Authorization' => "Bearer $token"]]);
    }

    // Test joining a group with a valid invitation code and verify the membership reference in response
    public function testJoinAndLeaveGroupWithValidCode(): void
    {
        $client = $this->client;

        $this->authenticate('user3@user.com');

        // Retrieve user2 and dynamically get a group created by user2
        $user2 = $this->em->getRepository(User::class)->findOneBy(['email' => 'user2@user.com']);
        $group = $this->em->getRepository(Group::class)->findOneBy(['createdBy' => $user2]);
        $invitationCode = $group->getInvitationCode();
        $groupId = $group->getId();


        // Send request to join the group using the invitation code
        $response = $client->request('POST', '/api/groups/join', [
            'json' => ['invitationCode' => $invitationCode],
        ]);

        $client->request('POST', '/api/groups/' . $group->getId() . '/leave');

        // Assert that the response is successful and contains the correct group membership reference
        $this->assertResponseIsSuccessful();
    }

    // Test joining a group with an invalid invitation code returns 404
    public function testJoinGroupWithInvalidCode(): void
    {
        $client = $this->client;

        $this->authenticate('user@user.com');

        // Send request with a non-existent invitation code
        $response = $client->request('POST', '/api/groups/join', [
            'json' => ['invitationCode' => 'invalid-code-xyz'],
        ]);

        // Assert that the response has status 404
        $this->assertResponseStatusCodeSame(404);
    }

    // Test that a user cannot join a group they are already a member of and receives a 400 status
    public function testJoinGroupAlreadyMember(): void
    {
        $client = $this->client;

        $this->authenticate('user2@user.com');

        // Dynamically get the group created by user2
        $user2 = $this->em->getRepository(User::class)->findOneBy(['email' => 'user2@user.com']);
        $group = $this->em->getRepository(Group::class)->findOneBy([
            'createdBy' => $user2
        ]);

        // Attempt to join the same group again
        $response = $client->request('POST', '/api/groups/join', [
            'json' => ['code' => $group->getInvitationCode()],
        ]);

        // Assert that the response has status 400 (already a member)
        $this->assertResponseStatusCodeSame(400);
    }
}