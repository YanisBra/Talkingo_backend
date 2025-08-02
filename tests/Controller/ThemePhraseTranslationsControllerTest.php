<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ThemePhraseTranslationsControllerTest extends ApiTestCase
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

    public function testReturnsCorrectStructure(): void
    {
        // Authenticate user with interface language = fr and target = en
        $this->authenticate('user@user.com');

        // Fetch theme "AIRPORT"
        $theme = $this->em->getRepository(Theme::class)->findOneBy(['code' => 'AIRPORT']);

        // Call the translated phrases endpoint
        $this->client->request('GET', '/api/themes/' . $theme->getId() . '/phrases/translated');

        $this->assertResponseIsSuccessful();

        $data = $this->client->getResponse()->toArray();

        $this->assertIsArray($data);

        foreach ($data as $item) {
            $this->assertArrayHasKey('theme_translations_target', $item);
            $this->assertArrayHasKey('theme_translations_interface', $item);
            $this->assertArrayHasKey('phrase_id', $item);
            $this->assertArrayHasKey('phrase_translation_id', $item);
            $this->assertArrayHasKey('interface_text', $item);
            $this->assertArrayHasKey('target_text', $item);
            $this->assertArrayHasKey('progress_id', $item);
            $this->assertArrayHasKey('is_known', $item);
        }
    }

    public function testHandlesMissingTranslations(): void
    {
        $this->authenticate('user@user.com');

        $theme = $this->em->getRepository(Theme::class)->findOneBy(['code' => 'AIRPORT']);

        $this->client->request('GET', '/api/themes/' . $theme->getId() . '/phrases/translated');

        $data = $this->client->getResponse()->toArray();

        // Ensure no translation text is null or empty
        foreach ($data as $item) {
            $this->assertNotEmpty($item['interface_text']);
            $this->assertNotEmpty($item['target_text']);
        }
    }
}