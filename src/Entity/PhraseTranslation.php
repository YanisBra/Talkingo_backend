<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PhraseTranslationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: PhraseTranslationRepository::class)]
class PhraseTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\ManyToOne(inversedBy: 'phraseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Phrase $phrase = null;

    #[ORM\ManyToOne(inversedBy: 'phraseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    /**
     * @var Collection<int, UserPhraseProgress>
     */
    #[ORM\OneToMany(targetEntity: UserPhraseProgress::class, mappedBy: 'phraseTranslation')]
    private Collection $userPhraseProgress;

    public function __construct()
    {
        $this->userPhraseProgress = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getPhrase(): ?Phrase
    {
        return $this->phrase;
    }

    public function setPhrase(?Phrase $phrase): static
    {
        $this->phrase = $phrase;

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

    /**
     * @return Collection<int, UserPhraseProgress>
     */
    public function getUserPhraseProgress(): Collection
    {
        return $this->userPhraseProgress;
    }

    public function addUserPhraseProgress(UserPhraseProgress $userPhraseProgress): static
    {
        if (!$this->userPhraseProgress->contains($userPhraseProgress)) {
            $this->userPhraseProgress->add($userPhraseProgress);
            $userPhraseProgress->setPhraseTranslation($this);
        }

        return $this;
    }

    public function removeUserPhraseProgress(UserPhraseProgress $userPhraseProgress): static
    {
        if ($this->userPhraseProgress->removeElement($userPhraseProgress)) {
            // set the owning side to null (unless already changed)
            if ($userPhraseProgress->getPhraseTranslation() === $this) {
                $userPhraseProgress->setPhraseTranslation(null);
            }
        }

        return $this;
    }
}
