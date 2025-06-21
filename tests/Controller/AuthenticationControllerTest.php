<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationControllerTest extends WebTestCase
{
    public function testRegister(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'testuser100@example.com',
                'password' => 'TestPassword123!',
                'nom' => 'Test16',
                'prenom' => 'User',
                'adresse' => '123 rue de test',
                'telephone' => '0600000000'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('User registered successfully', $client->getResponse()->getContent());
    }

    public function testLogin(): void
    {
        $client = static::createClient();

        // Succès : utilisateur existant
        $client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'testuser100@example.com',
                'password' => 'TestPassword123!'
            ])
        );
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);

        $token = $data['token'];

        // 2. Use the token to access a protected route
        $client->request(
            'GET',
            '/check', // replace with your protected route
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        // Optionally check the user data returned
        $userData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $userData);
        $this->assertSame('testuser100@example.com', $userData['user']['email']);
    }
}
