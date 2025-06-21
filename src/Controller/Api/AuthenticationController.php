<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthenticationController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'invalid_credentials'], 401);
        }

        return $this->json(['token' => $jwtManager->create($user)], 200, [], ['groups' => ['user:read']]);

    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // The logout route is handled by the security system, so we can just return a success message.
        return $this->json(['message' => 'Logged out successfully'], 200);
    }

    #[Route('/api/refresh', name: 'api_refresh', methods: ['POST'])]
    public function refresh(#[CurrentUser] ?User $user, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'invalid_credentials'], 401);
        }

        return $this->json(['token' => $jwtManager->create($user)], 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/api/check', name: 'api_check', methods: ['GET'])]
    public function check(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'invalid_credentials'], 401);
        }

        return $this->json(['user' => $user], 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher, 
        UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Name, surname, email, and password are required'], 400);
        }

        // Check if the user already exists
        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json(['error' => 'User already exists'], 400);
        }

        // Create a new user
        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRole($data['role'] ?? 'ROLE_USER'); // Default role if not provided
        $user->setAdresse($data['adresse'] ?? null);
        $user->setTelephone($data['telephone'] ?? null);
        $user->setCreatedAt(new \DateTimeImmutable());

        // Persist the user entity
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User registered successfully'], 201);
    }

}
