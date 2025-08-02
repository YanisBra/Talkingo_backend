<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class GroupThemesProgressControllerTest extends ApiTestCase
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

        $this->client->request('GET', '/api/groups/' . $group->getId() . '/themes/progress');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetGroupProgress(): void
    {
        $this->authenticate('user2@user.com'); 

        $group = $this->em->getRepository(Group::class)->findOneBy(['name' => 'Group 2']);

        $this->client->request('GET', '/api/groups/' . $group->getId() . '/themes/progress');

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse()->toArray();

        $this->assertArrayHasKey('themes', $response);
        $this->assertArrayHasKey('totalAverageProgress', $response);

        $this->assertEquals(38, $response['totalAverageProgress']);

        $themes = $response['themes'];
        $this->assertCount(2, $themes); // 2 themes

        foreach ($themes as $themeData) {
            $this->assertArrayHasKey('theme', $themeData);
            $this->assertArrayHasKey('averageProgress', $themeData);

            $labelInterface = $themeData['theme']['label_interface'];
            $progress = $themeData['averageProgress'];

            if ($labelInterface === 'Airport') {
                $this->assertEquals(25, $progress);
            }

            if ($labelInterface === 'Restaurant') {
                $this->assertEquals(50, $progress);
            }
        }
    }

    public function testCantGetGroupProgressIfMember(): void
    {
        $this->authenticate('user@user.com'); 

        $group = $this->em->getRepository(Group::class)->findOneBy(['name' => 'Group 2']);

        $this->client->request('GET', '/api/groups/' . $group->getId() . '/themes/progress');

        $this->assertResponseStatusCodeSame(403);
    }

    
}