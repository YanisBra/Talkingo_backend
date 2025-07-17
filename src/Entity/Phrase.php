<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PhraseRepository;
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
#[ORM\Entity(repositoryClass: PhraseRepository::class)]
class Phrase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'phrases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theme $theme = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, PhraseTranslation>
     */
    #[ORM\OneToMany(targetEntity: PhraseTranslation::class, mappedBy: 'phrase')]
    private Collection $phraseTranslations;

    public function __construct()
    {
        $this->phraseTranslations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

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
     * @return Collection<int, PhraseTranslation>
     */
    public function getPhraseTranslations(): Collection
    {
        return $this->phraseTranslations;
    }

    public function addPhraseTranslation(PhraseTranslation $phraseTranslation): static
    {
        if (!$this->phraseTranslations->contains($phraseTranslation)) {
            $this->phraseTranslations->add($phraseTranslation);
            $phraseTranslation->setPhrase($this);
        }

        return $this;
    }

    public function removePhraseTranslation(PhraseTranslation $phraseTranslation): static
    {
        if ($this->phraseTranslations->removeElement($phraseTranslation)) {
            // set the owning side to null (unless already changed)
            if ($phraseTranslation->getPhrase() === $this) {
                $phraseTranslation->setPhrase(null);
            }
        }

        return $this;
    }
}
