<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    private function createAuthenticatedClient(): array
    {
        $client = static::createClient();

        $email = 'user' . uniqid() . '@example.com';
        $password = 'TestPassword123!';

        // Inscription
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
                'nom' => 'Test',
                'prenom' => 'User',
                'adresse' => '123 rue de test',
                'telephone' => '0600000000',
                'role' => 'ROLE_USER'
            ])
        );

        // Connexion
        $client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password
            ])
        );

        $this->assertResponseIsSuccessful();
        $loginData = json_decode($client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        // Récupération du profil utilisateur (pour l'ID)
        $client->request(
            'GET',
            '/check',
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $userData = json_decode($client->getResponse()->getContent(), true);
        $userId = $userData['user']['id'];

        return [$client, $token, $userId];
    }

    public function testCreateUser(): void
    {
        [$client, $token] = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/user',
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'testuser' . uniqid() . '@example.com',
                'password' => 'TestPassword123!',
                'adresse' => '123 rue de test',
                'telephone' => '0600000000',
                'role' => 'ROLE_USER'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('Test', $data['nom']);
    }

    public function testUpdateUser(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            "/api/user/{$userId}",
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode([
                'nom' => 'NouveauNom',
                'prenom' => 'NouveauPrenom',
                'adresse' => 'Nouvelle adresse',
                'telephone' => '0700000000',
            ])
        );

        $this->assertResponseStatusCodeSame(200);
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('NouveauNom', $updated['nom']);
    }

    public function testIndexReturnsUserList(): void
    {
        [$client, $token] = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            '/api/user',
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testShowReturnsUserWhenExists(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            "/api/user/{$userId}",
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $user = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($userId, $user['id']);
    }

    public function testDeleteUser(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        $client->request(
            'DELETE',
            "/api/user/{$userId}",
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('User deleted successfully', $response['message']);
    }
}
