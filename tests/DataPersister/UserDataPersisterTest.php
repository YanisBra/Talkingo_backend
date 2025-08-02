<?php

namespace App\Tests\DataPersister;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;

class UserDataPersisterTest extends ApiTestCase
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
            'json' => ['email' => $email, 'password' => 'pass123']
        ]);

        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];

        $this->client->setDefaultOptions(['headers' => [
            'Authorization' => 'Bearer ' . $token
        ]]);
    }

    public function testPasswordIsHashedAndRolesProtected(): void
    {
        // Authenticate as normal user
        $this->authenticate('user@user.com');

        // Get language entities
        $interfaceLang = $this->em->getRepository(Language::class)->findOneBy(['code' => 'fr']);
        $targetLang = $this->em->getRepository(Language::class)->findOneBy(['code' => 'en']);

        // Create user
        $response = $this->client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/ld+json'], 
            'json' => [
                'email' => 'persistertest@example.com',
                'name' => 'PersisterTest',
                'plainPassword' => 'testpersister123',
                'roles' => ['ROLE_ADMIN'],
                'interfaceLanguage' => '/api/languages/' . $interfaceLang->getId(),
                'targetLanguage' => '/api/languages/' . $targetLang->getId(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'persistertest@example.com']);
        $this->assertNotNull($user);

        // 🔐 Password should be hashed
        $this->assertNotSame('testpersister123', $user->getPassword());
        $this->assertGreaterThan(20, strlen($user->getPassword())); // hash length

        // 🛡️ Roles should not include ROLE_ADMIN
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }
}