<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/category')]
class CategoryController extends AbstractController
{
    #[Route('', methods: ['GET'], name: 'get_all_category')]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        return $this->json($categories, 200, [], ['groups' => 'category:read']);
    }

    #[Route('/{id}', methods: ['GET'], name: 'get_category_by_id')]
    public function show(int $id, CategoryRepository $categoryRepository): JsonResponse
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }
        return $this->json($category, 200, [], ['groups' => 'category:read']);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete_category_by_id')]
    public function delete(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $em): JsonResponse
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $em->remove($category);
        $em->flush();

        return $this->json(['message' => 'Category deleted successfully']);
    }

    #[Route('', methods: ['POST'], name: 'create_category')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['title']) || empty($data['icon_name'])) {
            return $this->json(['error' => 'Title and icon_name are required'], 400);
        }

        $category = new Category();
        $category->setTitle($data['title']);
        $category->setIconName($data['icon_name']);
        $category->setDescription($data['description'] ?? null);
        $category->setDate(new \DateTime($data['date'] ?? 'now'));

        $em->persist($category);
        $em->flush();

        return $this->json($category, 201);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'update_category_by_id')]
    public function update(int $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $category = $categoryRepository->find($id);

        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $category->setTitle($data['title'] ?? $category->getTitle());
        $category->setIconName($data['icon_name'] ?? $category->getIconName());
        $category->setDescription($data['description'] ?? $category->getDescription());
        $category->setDate(new \DateTime($data['date'] ?? 'now'));

        $em->flush();

        return $this->json($category, 200, [], ['groups' => 'category:read']);
    }
}
