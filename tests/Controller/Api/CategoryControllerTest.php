<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CategoryControllerTest extends WebTestCase
{
    private function authenticateClient($client): string
    {
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
                'role' => 'ROLE_USER',
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
                'password' => $password,
            ])
        );

        $this->assertResponseIsSuccessful();
        $loginData = json_decode($client->getResponse()->getContent(), true);
        return $loginData['token'];
    }

    private function createCategory($client, string $token): array
    {
        $categoryData = [
            'title' => 'Test Category',
            'icon_name' => 'test-icon',
            'description' => 'Description test',
        ];

        $client->request(
            'POST',
            '/api/category',
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($categoryData)
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);

        return $data;
    }

    public function testShowCategory(): void
    {
        $client = static::createClient();
        $token = $this->authenticateClient($client);

        $category = $this->createCategory($client, $token);

        $client->request(
            'GET',
            '/api/category/' . $category['id'],
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($category['id'], $data['id']);
    }

    public function testUpdateCategory(): void
    {
        $client = static::createClient();
        $token = $this->authenticateClient($client);

        $category = $this->createCategory($client, $token);

        $updateData = [
            'title' => 'Updated Category',
            'icon_name' => 'icon-updated',
            'description' => 'Updated description',
        ];

        $client->request(
            'PUT',
            '/api/category/' . $category['id'],
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Category', $data['title']);
        $this->assertEquals('icon-updated', $data['icon_name']);
    }

    // Autres méthodes de test dans le même style : 
    // créer $client avec static::createClient() une fois, puis passer en paramètre
}
