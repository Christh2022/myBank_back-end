<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/category')]
class CategoryController extends AbstractController
{
    #[Route('', methods: ['GET'], name: 'get_all_category')]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        return $this->json($categories);
    }

    #[Route('/{id}', methods: ['GET'], name: 'get_category_by_id')]
    public function show(int $id, CategoryRepository $categoryRepository): JsonResponse
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }
        return $this->json($category);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete_category_by_id')]
    public function delete(int $id, CategoryRepository $categoryRepository): JsonResponse
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $categoryRepository->remove($category);
        return $this->json(['message' => 'Category deleted successfully']);
    }

    #[Route('', methods: ['POST'], name: 'create_category')]
    public function create(CategoryRepository $categoryRepository, Request $request, EntityManager $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['title']) && empty($data['icon_name'])) {
            return $this->json(['error' => 'Category title and icon name are required'], 400);
        }

        $category = new Category();
        $category->setTitle($data['title']);
        $category->setIconName($data['icon_name']);
        $category->setDescription($data['description'] ?? null);
        $category->setDate(new \DateTime());

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json($category, 201, [], ['groups' => ['category:read', 'user:read']]);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'update_category_by_id')]
    public function update(int $id, CategoryRepository $categoryRepository, Request $request, EntityManager $entityManager): JsonResponse
    {
        // Assuming the request body contains the updated category data
        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return $this->json(['error' => 'Category name is required'], 400);
        }

        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $category->setTitle($data['title'] ?? $category->getTitle());
        $category->setIconName($data['icon_name'] ?? $category->getIconName());
        $category->setDescription($data['description'] ?? $category->getDescription());
        $category->setDate(new \DateTime());

        $entityManager->flush();

        return $this->json($category, 200, [], ['groups' => ['category:read', 'user:read']]);
    }
}
