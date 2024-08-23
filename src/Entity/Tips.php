<?php

namespace App\Entity;

use App\Repository\TipsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TipsRepository::class)]
class Tips
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $month_list = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMonthList(): ?array
    {
        return $this->month_list;
    }

    public function setMonthList(?array $month_list): static
    {
        $this->month_list = $month_list;

        return $this;
    }
}
