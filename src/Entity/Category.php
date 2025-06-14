<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 100)]
    private ?string $icon_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $Expense;

    #[ORM\Column]
    private ?\DateTime $date = null;

    public function __construct()
    {
        $this->Expense = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIconName(): ?string
    {
        return $this->icon_name;
    }

    public function setIconName(string $icon_name): static
    {
        $this->icon_name = $icon_name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpense(): Collection
    {
        return $this->Expense;
    }

    public function addExpense(Expense $expense): static
    {
        if (!$this->Expense->contains($expense)) {
            $this->Expense->add($expense);
            $expense->setCategory($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): static
    {
        if ($this->Expense->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getCategory() === $this) {
                $expense->setCategory(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }
}
