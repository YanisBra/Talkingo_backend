<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};

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
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "The code cannot be blank.")]
    #[Assert\Length(
        max: 50,
        maxMessage: "The code must not exceed {{ limit }} characters."
    )]
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

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->themeTranslations = new ArrayCollection();
        $this->phrases = new ArrayCollection();
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
}