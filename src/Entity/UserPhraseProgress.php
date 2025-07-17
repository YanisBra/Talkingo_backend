<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserPhraseProgressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: UserPhraseProgressRepository::class)]
class UserPhraseProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userPhraseProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PhraseTranslation $phraseTranslation = null;

    #[ORM\ManyToOne(inversedBy: 'userPhraseProgress')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $isKnown = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhraseTranslation(): ?PhraseTranslation
    {
        return $this->phraseTranslation;
    }

    public function setPhraseTranslation(?PhraseTranslation $phraseTranslation): static
    {
        $this->phraseTranslation = $phraseTranslation;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isKnown(): ?bool
    {
        return $this->isKnown;
    }

    public function setIsKnown(bool $isKnown): static
    {
        $this->isKnown = $isKnown;

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
}

