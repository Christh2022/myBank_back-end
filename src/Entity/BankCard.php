<?php

namespace App\Entity;

use App\Repository\BankCardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankCardRepository::class)]
class BankCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $number = null;

    #[ORM\Column]
    private ?\DateTime $expirationDate = null;

    #[ORM\Column(length: 3)]
    private ?string $cvv = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'bankCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $user = null;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'bankCard')]
    private Collection $expenses;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTime $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    public function setCvv(string $cvv): static
    {
        $this->cvv = $cvv;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): static
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
            $expense->setBankCard($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): static
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getBankCard() === $this) {
                $expense->setBankCard(null);
            }
        }

        return $this;
    }
}
