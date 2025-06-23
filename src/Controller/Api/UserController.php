<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('', methods: ['GET'], name: 'app_api_user')]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => ['user:read', 'expense:read', 'category:read']]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'app_api_user_show')]
    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        return $this->json($user, 200, [], ['groups' => ['user:read', 'expense:read', 'category:read']]);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'app_api_user_delete')]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }

    #[Route('', methods: ['POST'], name: 'app_api_user_create')]
    public function create(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Name, surname, email, and password are required'], 400);
        }
        
        // Check if the user already exists
        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json(['error' => 'User already exists'], 400);
        }

        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setAdresse($data['adresse'] ?? null);
        $user->setTelephone($data['telephone'] ?? null);
        $user->setRole($data['role'] ?? 'ROLE_USER'); // Default role if not provided
        $user->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, 201, [], ['groups' => ['user:read', 'expense:read', 'category:read']]);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'app_api_user_update')]
    public function update(int $id, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        if(isset($data["nom"])) {
            $user->setNom($data['nom']);
        }

        if (isset($data['prenom']) ) {
            $user->setPrenom($data['prenom']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
            if ($userRepository->findOneBy(['email' => $data['email']]) && $user->getEmail() !== $data['email']) {
                return $this->json(['error' => 'Email already in use'], 400);
            }
        }
        
        if (isset($data['adresse'])) {
            $user->setAdresse($data['adresse']);
        }

        if (isset($data['telephone'])) {
            $user->setTelephone($data['telephone']);
        }

        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }

        if (isset($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, 200, [], ['groups' => ['user:read', 'expense:read', 'category:read']]);
    }

}
