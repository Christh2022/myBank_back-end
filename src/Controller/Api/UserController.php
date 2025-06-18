<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('', methods: ['GET'], name: 'app_api_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/UserController.php',
        ]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'app_api_user_show')]
    public function show(int $id): JsonResponse
    {
        return $this->json([
            'message' => 'User ID',
            'id' => $id,
        ]);
    }
}
