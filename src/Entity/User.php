<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'usersUsingAsInterface')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $interfaceLanguage = null;

    #[ORM\ManyToOne(inversedBy: 'usersUsingAsTarget')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $targetLanguage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getInterfaceLanguage(): ?Language
    {
        return $this->interfaceLanguage;
    }

    public function setInterfaceLanguage(?Language $interfaceLanguage): static
    {
        $this->interfaceLanguage = $interfaceLanguage;

        return $this;
    }

    public function getTargetLanguage(): ?Language
    {
        return $this->targetLanguage;
    }

    public function setTargetLanguage(?Language $targetLanguage): static
    {
        $this->targetLanguage = $targetLanguage;

        return $this;
    }
}
