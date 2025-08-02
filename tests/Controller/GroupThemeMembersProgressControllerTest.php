<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Group;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class GroupThemeMembersProgressControllerTest extends ApiTestCase
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

    public function testAccessDeniedIfNotMember(): void
    {
        $this->authenticate('user@user.com'); // not a member of Group 2

        $group = $this->em->getRepository(Group::class)->findOneBy(['name' => 'Group 2']);
        $theme = $this->em->getRepository(Theme::class)->findOneBy(['code' => 'AIRPORT']);

        $this->client->request('GET', '/api/groups/' . $group->getId() . '/themes/' . $theme->getId() . '/members/progress');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testReturnsZeroForMembersWithNoProgress(): void
    {
        $this->authenticate('user2@user.com'); // admin member of the group

        $group = $this->em->getRepository(Group::class)->findOneBy(['invitationCode' => 'GROUP1234']);
        $themeId = $this->em->getRepository(\App\Entity\Theme::class)->findOneBy(['code' => 'AIRPORT'])->getId();

        $this->client->request('GET', '/api/groups/' . $group->getId() . '/themes/' . $themeId . '/members/progress');

        $this->assertResponseIsSuccessful();

        $data = $this->client->getResponse()->toArray();

        $this->assertIsArray($data);
        $this->assertCount(2, $data); // user2 and user4

        foreach ($data as $member) {
            $this->assertArrayHasKey('user', $member);
            $this->assertArrayHasKey('progress', $member);
            $this->assertArrayHasKey('id', $member['user']);
            $this->assertArrayHasKey('name', $member['user']);

            // Check that user4 has 0%
            if ($member['user']['name'] === 'user4') {
                $this->assertSame(0, $member['progress']);
            }

            // Check that user2 has 50%
            if ($member['user']['name'] === 'user2') {
                $this->assertSame(50, $member['progress']);
            }
        }
    }

    public function testStandardProgressCalculation(): void
    {
        $this->authenticate('user2@user.com');

        $group = $this->em->getRepository(Group::class)->findOneBy(['name' => 'Group 2']);
        $theme = $this->em->getRepository(Theme::class)->findOneBy(['code' => 'AIRPORT']);

        $this->client->request('GET', '/api/groups/' . $group->getId() . '/themes/' . $theme->getId() . '/members/progress');

        $this->assertResponseIsSuccessful();

        $data = $this->client->getResponse()->toArray();

        $this->assertIsArray($data);

        foreach ($data as $memberProgress) {
            $this->assertArrayHasKey('user', $memberProgress);
            $this->assertArrayHasKey('progress', $memberProgress);
            $this->assertArrayHasKey('name', $memberProgress['user']);

            if ($memberProgress['user']['name'] === 'user2') {
                $this->assertSame(50, $memberProgress['progress']);
            }

            if ($memberProgress['user']['name'] === 'user4') {
                $this->assertSame(0, $memberProgress['progress']);
            }
        }
    }
}