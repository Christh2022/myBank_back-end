<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        return $this->json($categories, 200, [], ['groups' => ['category:read', 'expense:read', 'user:read']]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'get_category_by_id')]
    public function show(int $id, CategoryRepository $categoryRepository): JsonResponse
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }
        return $this->json($category, 200, [], ['groups' => ['category:read', 'expense:read', 'user:read']]);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete_category_by_id')]
    public function delete(int $id, CategoryRepository $categoryRepository): JsonResponse
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $categoryRepository->delete($category);
        return $this->json(['message' => 'Category deleted successfully']);
    }

    #[Route('', methods: ['POST'], name: 'create_category')]
    public function create(CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['title']) && empty($data['icon_name'])) {
            return $this->json(['error' => 'Category title and icon name are required'], 400);
        }

        $category = new Category();
        $category->setTitle($data['title']);
        $category->setIconName($data['icon_name']);
        if (isset($data['description'])) {
            $category->setDescription($data['description']);
        }
        $category->setDate(new \DateTime());
        $category->setUser($this->getUser()); // Assuming the user is set from the authenticated session
        
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json($category, 201, [], ['groups' => ['category:read', 'user:read', 'expense:read']]);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'update_category_by_id')]
    public function update(int $id, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Assuming the request body contains the updated category data
        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return $this->json(['error' => 'Category title is required'], 400);
        }

        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        // Update the category properties
        $category->setTitle($data['title']);
        if (isset($data['icon_name'])) {
            $category->setIconName($data['icon_name']);
        }
        if (isset($data['description'])) {
            $category->setDescription($data['description']);
        }
        $entityManager->flush();
        return $this->json($category, 200, [], ['groups' => ['category:read', 'expense:read', 'user:read']]);
    }

    #[Route('/category/expense/{id}', methods: ['GET'], name: 'get_expenses_by_category_id')]
    public function getExpensesByCategoryId(int $id, ExpenseRepository $expenseRepository): JsonResponse
    {
        $expenses = $expenseRepository->findBy(['category' => $id]);
        if (!$expenses) {
            return $this->json(['error' => 'No expenses found for this category'], 404);
        }
        return $this->json($expenses, 200, [], ['groups' => ['expense:read', 'category:read', 'user:read']]);
    }

    #[Route('/category/user/{id}', methods: ['GET'], name: 'get_categories_by_user_id')]
    public function getCategoriesByUserId(int $id, CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findBy(['user' => $id]);
        if (!$categories) {
            return $this->json(['error' => 'No categories found for this user'], 404);
        }
        return $this->json($categories, 200, [], ['groups' => ['category:read', 'expense:read', 'user:read']]);
    }
}
