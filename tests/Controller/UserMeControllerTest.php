<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserMeControllerTest extends ApiTestCase
{
    private EntityManagerInterface $em;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    private function authenticate(string $email): void
    {
        $this->client->request('POST', '/api/login_check', [
            'json' => ['email' => $email, 'password' => 'pass123'],
        ]);

        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];
        $this->client->setDefaultOptions(['headers' => ['Authorization' => "Bearer $token"]]);
    }

    public function testAuthenticatedUserCanGetTheirOwnData(): void
    {
        $this->authenticate('user2@user.com');

        $this->client->request('GET', '/api/users/me');

        $this->assertResponseIsSuccessful();

        $data = $this->client->getResponse()->toArray();

        $this->assertSame('user2@user.com', $data['email']);
        $this->assertSame('user2', $data['name']);
        $this->assertEquals(['ROLE_USER'], $data['roles']);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('createdAt', $data);

        $this->assertArrayHasKey('interfaceLanguage', $data);
        $this->assertArrayHasKey('targetLanguage', $data);

        $this->assertSame('en', $data['interfaceLanguage']['code']);
        $this->assertSame('English', $data['interfaceLanguage']['label']);

        $this->assertSame('es', $data['targetLanguage']['code']);
        $this->assertSame('Español', $data['targetLanguage']['label']);
    }
}