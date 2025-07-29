<?php


namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PhraseTranslationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;


#[ApiFilter(SearchFilter::class, properties: ['phrase' => 'exact', 'phrase.theme' => 'exact'])]
#[ApiResource(
    normalizationContext: ['groups' => ['phrase_translation:read']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
#[ORM\Entity(repositoryClass: PhraseTranslationRepository::class)]
class PhraseTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['phrase_translation:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "The phrase translation cannot be empty.")]
    #[Assert\Length(
        min: 2,
        max: 500,
        minMessage: "The phrase translation must be at least {{ limit }} characters long.",
        maxMessage: "The phrase translation cannot be longer than {{ limit }} characters.",
    )]
    #[Groups(['phrase_translation:read'])]
    private ?string $text = null;

    #[ORM\ManyToOne(inversedBy: 'phraseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['phrase_translation:read'])]
    private ?Phrase $phrase = null;

    #[ORM\ManyToOne(inversedBy: 'phraseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['phrase_translation:read'])]
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
        $this->text = trim(strip_tags($text));;

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
