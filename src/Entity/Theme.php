<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use Symfony\Component\Serializer\Annotation\Groups;


#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN')"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['theme_translation:read'])] 
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "The code cannot be blank.")]
    #[Assert\Length(
        max: 50,
        maxMessage: "The code must not exceed {{ limit }} characters."
    )]
    #[Groups(['theme_translation:read', 'phrase:read'])] 
    private ?string $code = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, ThemeTranslation>
     */
    #[ORM\OneToMany(targetEntity: ThemeTranslation::class, mappedBy: 'theme')]
    private Collection $themeTranslations;

    /**
     * @var Collection<int, Phrase>
     */
    #[ORM\OneToMany(targetEntity: Phrase::class, mappedBy: 'theme')]
    private Collection $phrases;

    /**
     * @var Collection<int, QuizResult>
     */
    #[ORM\OneToMany(targetEntity: QuizResult::class, mappedBy: 'theme')]
    private Collection $quizResults;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->themeTranslations = new ArrayCollection();
        $this->phrases = new ArrayCollection();
        $this->quizResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    /**
     * @return Collection<int, ThemeTranslation>
     */
    public function getThemeTranslations(): Collection
    {
        return $this->themeTranslations;
    }

    public function addThemeTranslation(ThemeTranslation $themeTranslation): static
    {
        if (!$this->themeTranslations->contains($themeTranslation)) {
            $this->themeTranslations->add($themeTranslation);
            $themeTranslation->setTheme($this);
        }

        return $this;
    }

    public function removeThemeTranslation(ThemeTranslation $themeTranslation): static
    {
        if ($this->themeTranslations->removeElement($themeTranslation)) {
            // set the owning side to null (unless already changed)
            if ($themeTranslation->getTheme() === $this) {
                $themeTranslation->setTheme(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Phrase>
     */
    public function getPhrases(): Collection
    {
        return $this->phrases;
    }

    public function addPhrase(Phrase $phrase): static
    {
        if (!$this->phrases->contains($phrase)) {
            $this->phrases->add($phrase);
            $phrase->setTheme($this);
        }

        return $this;
    }

    public function removePhrase(Phrase $phrase): static
    {
        if ($this->phrases->removeElement($phrase)) {
            // set the owning side to null (unless already changed)
            if ($phrase->getTheme() === $this) {
                $phrase->setTheme(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QuizResult>
     */
    public function getQuizResults(): Collection
    {
        return $this->quizResults;
    }

    public function addQuizResult(QuizResult $quizResult): static
    {
        if (!$this->quizResults->contains($quizResult)) {
            $this->quizResults->add($quizResult);
            $quizResult->setTheme($this);
        }

        return $this;
    }

    public function removeQuizResult(QuizResult $quizResult): static
    {
        if ($this->quizResults->removeElement($quizResult)) {
            // set the owning side to null (unless already changed)
            if ($quizResult->getTheme() === $this) {
                $quizResult->setTheme(null);
            }
        }

        return $this;
    }
}