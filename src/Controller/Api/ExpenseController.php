<?php

namespace App\Controller\Api;

use App\Entity\Expense;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/expense')]
class ExpenseController extends AbstractController
{
    #[Route('', name: 'app_api_expense')]
    public function index(ExpenseRepository $expenseRepository): JsonResponse
    {
        $expenses = $expenseRepository->findAll();
        return $this->json($expenses, 200, [], ['groups' => 'expense:read']);
    }

    #[Route('/{id}', name: 'get_expense_by_id')]
    public function show(int $id, ExpenseRepository $expenseRepository): JsonResponse
    {
        $expense = $expenseRepository->find($id);
        if (!$expense) {
            return $this->json(['error' => 'Expense not found'], 404);
        }
        return $this->json($expense, 200, [], ['groups' => 'expense:read']);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete_expense_by_id')]
    public function delete(int $id, ExpenseRepository $expenseRepository, EntityManagerInterface $em): JsonResponse
    {
        $expense = $expenseRepository->find($id);
        if (!$expense) {
            return $this->json(['error' => 'Expense not found'], 404);
        }

        $em->remove($expense);
        $em->flush();

        return $this->json(['message' => 'Expense deleted successfully']);
    }

    #[Route('', methods: ['POST'], name: 'create_expense')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['amount']) || empty($data['status']) || empty($data['label'])) {
            return $this->json(['error' => 'Amount, status, and label are required'], 400);
        }


        // Validate category and user if they are provided
        if (isset($data['category']) && !is_numeric($data['category'])) {
            return $this->json(['error' => 'Invalid category ID'], 400);
        }

        $expense = new Expense();
        $expense->setDate(new \DateTime());
        $expense->setAmount($data['amount']);
        $expense->setStatus($data['status']);
        $expense->setLabel($data['label']);
        $expense->setCategory($data['category'] ?? null); // Assuming category is passed as an ID or object
        $expense->setUser($data['user'] ?? null); // Assuming the user is authenticated

        $em->persist($expense);
        $em->flush();

        return $this->json($expense, 201, [], ['groups' => 'expense:read']);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'update_expense_by_id')]
    public function update(int $id, Request $request, ExpenseRepository $expenseRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $expense = $expenseRepository->find($id);

        if (!$expense) {
            return $this->json(['error' => 'Expense not found'], 404);
        }

        $expense->setAmount($data['amount'] ?? $expense->getAmount());
        $expense->setStatus($data['status'] ?? $expense->getStatus());
        $expense->setLabel($data['label'] ?? $expense->getLabel());
        $expense->setCategory($data['category'] ?? $expense->getCategory());
        $expense->setUser($data['user'] ?? $expense->getUser());

        $em->flush();

        return $this->json($expense);
    }
}
