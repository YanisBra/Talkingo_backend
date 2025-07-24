<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserPhraseProgressRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use App\DataPersister\UserPhraseProgressDataPersister;
use Symfony\Component\Serializer\Annotation\Groups;


#[ApiResource(
    operations: [
        new GetCollection(), 
        new Get(),
        new Post(processor: UserPhraseProgressDataPersister::class, security: "is_granted('ROLE_USER')"),
        new Put(processor: UserPhraseProgressDataPersister::class, security: "is_granted('ROLE_USER')"),
        new Patch(processor: UserPhraseProgressDataPersister::class, security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')")
    ],
    normalizationContext: ['groups' => ['user_phrase_progress:read']],
    denormalizationContext: ['groups' => ['user_phrase_progress:write']]
)]
#[ORM\Entity(repositoryClass: UserPhraseProgressRepository::class)]
class UserPhraseProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_phrase_progress:read'])]
    private ?int $id = null;

    #[Assert\NotNull(message: "The phraseTranslation is required.")]
    #[ORM\ManyToOne(inversedBy: 'userPhraseProgress')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_phrase_progress:read', 'user_phrase_progress:write'])]
    private ?PhraseTranslation $phraseTranslation = null;

    #[Assert\NotNull(message: "The user is required.")]
    #[ORM\ManyToOne(inversedBy: 'userPhraseProgress')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_phrase_progress:read', 'user_phrase_progress:write'])]
    private ?User $user = null;

    #[Assert\NotNull(message: "The createdAt date is required.")]
    #[ORM\Column]
    #[Groups(['user_phrase_progress:read'])]
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
