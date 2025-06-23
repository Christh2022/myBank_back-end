<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExpenseControllerTest extends WebTestCase
{
    private function createAuthenticatedClient(): array
    {
        $client = static::createClient();

        // Crée un utilisateur pour l'authentification
        $email = 'user' . uniqid() . '@example.com';
        $password = 'TestPassword123!';

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
        $token = $loginData['token'];

        // Récupère l'ID utilisateur connecté
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

    // Helper pour créer une dépense valide avant tests update/delete/show
    private function createExpense(string $token, int $userId): array
    {
        $client = static::createClient();

        // Note : Pour simplifier, on assume que category et bankCards existent avec ID 1
        // Si besoin, tu peux ajouter des fixtures ou les créer dans les tests

        $expenseData = [
            'amount' => 100.50,
            'status' => 'pending',
            'label' => 'Test Expense',
            'category' => 1,
            'user' => $userId,
            // 'bankCards' => 1, // facultatif si besoin
        ];

        $client->request(
            'POST',
            '/api/expense',
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($expenseData)
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);

        return $data;
    }

    public function testIndexReturnsExpensesList(): void
    {
        [$client, $token] = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            '/api/expense',
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

    public function testCreateExpense(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        // Pour ce test, on assume category ID 1 existe
        $expenseData = [
            'amount' => 200,
            'status' => 'paid',
            'label' => 'New Expense',
            'category' => 1,
            'user' => $userId,
        ];

        $client->request(
            'POST',
            '/api/expense',
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($expenseData)
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('New Expense', $data['label']);
    }

    public function testShowExpense(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        $expense = $this->createExpense($token, $userId);

        $client->request(
            'GET',
            '/api/expense/' . $expense['id'],
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($expense['id'], $data['id']);
    }

    public function testUpdateExpense(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        $expense = $this->createExpense($token, $userId);

        $updateData = [
            'amount' => 300,
            'status' => 'paid',
            'label' => 'Updated Expense',
        ];

        $client->request(
            'PUT',
            '/api/expense/' . $expense['id'],
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Expense', $data['label']);
        $this->assertEquals(300, $data['amount']);
    }

    public function testDeleteExpense(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        $expense = $this->createExpense($token, $userId);

        $client->request(
            'DELETE',
            '/api/expense/' . $expense['id'],
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Expense deleted successfully', $response['message']);
    }

    public function testGetExpensesByUserId(): void
    {
        [$client, $token, $userId] = $this->createAuthenticatedClient();

        // Crée une dépense pour l'utilisateur
        $this->createExpense($token, $userId);

        $client->request(
            'GET',
            '/api/expense/user/expense/' . $userId,
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());

        $expenses = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($expenses);
        $this->assertNotEmpty($expenses);
    }
}
