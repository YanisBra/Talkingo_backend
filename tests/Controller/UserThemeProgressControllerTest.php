<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserThemeProgressControllerTest extends ApiTestCase
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

    public function testUserWithNoProgressGetsZeroPercent(): void
    {
        $this->authenticate('user4@user.com'); // Only knows a RESTAURANT phrase

        $this->client->request('GET', '/api/users/me/themes/progress');
        $this->assertResponseIsSuccessful();

        $data = $this->client->getResponse()->toArray();

        foreach ($data as $themeProgress) {
            $this->assertArrayHasKey('theme', $themeProgress);
            $this->assertArrayHasKey('progress', $themeProgress);

            $this->assertArrayHasKey('id', $themeProgress['theme']);
            $this->assertArrayHasKey('label_interface', $themeProgress['theme']);
            $this->assertArrayHasKey('label_target', $themeProgress['theme']);

            $this->assertIsNumeric($themeProgress['progress']);
            $this->assertGreaterThanOrEqual(0, $themeProgress['progress']);
            $this->assertLessThanOrEqual(100, $themeProgress['progress']);
        }
    }

    public function testUserWithKnownPhrasesGetsCorrectPercent(): void
    {
        $this->authenticate('user2@user.com'); // Has learned one phrase in each theme in Spanish

        $this->client->request('GET', '/api/users/me/themes/progress');
        $this->assertResponseIsSuccessful();

        $data = $this->client->getResponse()->toArray();

        $airport = array_filter($data, fn($t) => $t['theme']['label_target'] === 'Aeropuerto');
        $restaurant = array_filter($data, fn($t) => $t['theme']['label_target'] === 'Restaurante');

        $this->assertNotEmpty($airport);
        $this->assertNotEmpty($restaurant);

        $airportProgress = array_values($airport)[0]['progress'];
        $restaurantProgress = array_values($restaurant)[0]['progress'];

        $this->assertEquals(50.0, $airportProgress);
        $this->assertEquals(50.0, $restaurantProgress);
    }
}
