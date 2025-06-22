<?php

// src/Controller/Api/BankCardsController.php

namespace App\Controller\Api;

use App\Entity\BankCards;
use App\Repository\BankCardsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/bankcards')]
class BankCardsController extends AbstractController
{
    #[Route('/', name: 'api_bankcards_index', methods: ['GET'])]
    public function index(BankCardsRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $cards = $repo->findBy(['user' => $user]);

        return $this->json($cards, Response::HTTP_OK, [], ['groups' => ['bankcards']]);
    }

    #[Route('/', name: 'api_bankcards_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->getUser();
        $data = $request->getContent();

        try {
            /** @var BankCards $bankCard */
            $bankCard = $serializer->deserialize($data, BankCards::class, 'json');
            $bankCard->setUser($user);

            $errors = $validator->validate($bankCard);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $em->persist($bankCard);
            $em->flush();

            return $this->json($bankCard, Response::HTTP_CREATED, [], ['groups' => ['bankcards']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_bankcards_show', methods: ['GET'])]
    public function show(int $id, BankCards $bankCard): JsonResponse
    {
        $user = $this->getUser();
        if ($bankCard->getUser()->getId() !== $id) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($bankCard, Response::HTTP_OK, [], ['groups' => ['bankcards']]);
    }

    #[Route('/{id}', name: 'api_bankcards_edit', methods: ['PUT', 'PATCH'])]
    public function edit(int $id, Request $request, BankCards $bankCard, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->getUser();
        if ($bankCard->getUser()->getId() !== $id) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->getContent();

        try {
            $serializer->deserialize($data, BankCards::class, 'json', ['object_to_populate' => $bankCard]);

            $errors = $validator->validate($bankCard);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $em->flush();

            return $this->json($bankCard, Response::HTTP_OK, [], ['groups' => ['bankcards']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_bankcards_delete', methods: ['DELETE'])]
    public function delete(int $id, BankCards $bankCard, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if ($bankCard->getUser()->getId() !== $id) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($bankCard);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
