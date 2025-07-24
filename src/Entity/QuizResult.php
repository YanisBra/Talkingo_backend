<?php

namespace App\Entity;

use App\Repository\QuizResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use App\DataPersister\QuizResultDataPersister;
use Symfony\Component\Serializer\Annotation\Groups;


#[ApiResource(
    normalizationContext: ['groups' => ['quiz_result:read']],
    denormalizationContext: ['groups' => ['quiz_result:write']],
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')",  processor: QuizResultDataPersister::class),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
#[ORM\Entity(repositoryClass: QuizResultRepository::class)]
class QuizResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quiz_result:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quiz_result:read', 'quiz_result:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'quizResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quiz_result:read', 'quiz_result:write'])]
    private ?Theme $theme = null;

    #[ORM\Column]
    #[Groups(['quiz_result:read', 'quiz_result:write'])]
    private ?int $score = null;

    #[ORM\Column]
    #[Groups(['quiz_result:read', 'quiz_result:write'])]
    private ?int $questionCount = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['quiz_result:read', 'quiz_result:write'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\ManyToOne(inversedBy: 'quizResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quiz_result:read', 'quiz_result:write'])]
    private ?Language $language = null;

    public function __construct()
    {
        $this->endDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getQuestionCount(): ?int
    {
        return $this->questionCount;
    }

    public function setQuestionCount(int $questionCount): static
    {
        $this->questionCount = $questionCount;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }
}
