<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class GroupLeaveControllerTest extends ApiTestCase
{
    private EntityManagerInterface $em;
    private $client;

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

    public function testGroupExistsForUser(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'user@user.com']);
        $group = $this->em->getRepository(Group::class)->findOneBy(['createdBy' => $user]);

        $this->assertNotNull($user, 'User not found');
        $this->assertNotNull($group, 'The group must exist before leaving');
    }

    public function testUserCanLeaveGroupWhenOnlyMember(): void
    {
        $this->authenticate('user@user.com');

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'user@user.com']);
        $group = $this->em->getRepository(Group::class)->findOneBy(['createdBy' => $user]);

        $this->client->request('POST', '/api/groups/' . $group->getId() . '/leave');

        $this->assertResponseIsSuccessful();
    }

        public function testGroupCreatorCantLeaveGroupWhenNotOnlyMember(): void
    {
        $this->authenticate('user2@user.com');

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'user2@user.com']);
        $group = $this->em->getRepository(Group::class)->findOneBy(['createdBy' => $user]);

        $this->client->request('POST', '/api/groups/' . $group->getId() . '/leave');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGroupIsDeletedAfterUserLeaves(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'user@user.com']);
        $group = $this->em->getRepository(Group::class)->findOneBy(['createdBy' => $user]);

        $this->assertNull($group, 'The group should have been deleted after leaving');
    }
}