<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


#[ApiResource(
    normalizationContext: ['groups' => ['language:read']],
    denormalizationContext: ['groups' => ['language:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['language:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 10, unique: true)]
    #[Assert\NotBlank(message: "The language code is required.")]
    #[Assert\Length(
        max: 10,
        maxMessage: "The code must not exceed {{ limit }} characters."
    )]
    #[Groups(['language:read', 'language:write', 'user:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 100, unique: true,)]
    #[Assert\NotBlank(message: "The language label is required.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "The label must not exceed {{ limit }} characters.",
    )]
    #[Groups(['language:read', 'language:write', 'theme_translation:read', 'phrase_translation:read', 'group:read', 'user:read'])]
    private ?string $label = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "The isActive field is required.")]
    #[Groups(['language:read', 'language:write'])]
    private ?bool $isActive = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: "The icon URL must not exceed {{ limit }} characters."
    )]
    #[Groups(['language:read', 'language:write'])]
    private ?string $iconUrl = null;

    #[ORM\Column]
    #[Groups(['language:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, ThemeTranslation>
     */
    #[ORM\OneToMany(targetEntity: ThemeTranslation::class, mappedBy: 'language')]
    private Collection $themeTranslations;

    /**
     * @var Collection<int, PhraseTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'language', targetEntity: PhraseTranslation::class)]
    private Collection $phraseTranslations;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'interfaceLanguage')]
    private Collection $usersUsingAsInterface;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'targetLanguage')]
    private Collection $usersUsingAsTarget;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\OneToMany(targetEntity: Group::class, mappedBy: 'targetLanguage')]
    private Collection $groups;

    /**
     * @var Collection<int, QuizResult>
     */
    #[ORM\OneToMany(targetEntity: QuizResult::class, mappedBy: 'language')]
    private Collection $quizResults;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive = true;
        $this->themeTranslations = new ArrayCollection();
        $this->usersUsingAsInterface = new ArrayCollection();
        $this->usersUsingAsTarget = new ArrayCollection();
        $this->groups = new ArrayCollection();
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
        $this->code = trim(strip_tags($code));;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = trim(strip_tags($label));;

        return $this;
    }

    public function getisActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    public function setIconUrl(?string $iconUrl): static
    {
        $this->iconUrl = trim(strip_tags($iconUrl));;

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
            $themeTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeThemeTranslation(ThemeTranslation $themeTranslation): static
    {
        if ($this->themeTranslations->removeElement($themeTranslation)) {
            if ($themeTranslation->getLanguage() === $this) {
                $themeTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @var Collection<int, PhraseTranslation>
     */
    public function getPhraseTranslations(): Collection
    {
        return $this->phraseTranslations;
    }

    public function addPhraseTranslation(PhraseTranslation $phraseTranslation): static
    {
        if (!$this->phraseTranslations->contains($phraseTranslation)) {
            $this->phraseTranslations->add($phraseTranslation);
            $phraseTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removePhraseTranslation(PhraseTranslation $phraseTranslation): static
    {
        if ($this->phraseTranslations->removeElement($phraseTranslation)) {
            if ($phraseTranslation->getLanguage() === $this) {
                $phraseTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersUsingAsInterface(): Collection
    {
        return $this->usersUsingAsInterface;
    }

    public function addUserUsingAsInterface(User $user): static
    {
        if (!$this->usersUsingAsInterface->contains($user)) {
            $this->usersUsingAsInterface->add($user);
            $user->setInterfaceLanguage($this);
        }

        return $this;
    }

    public function removeUserUsingAsInterface(User $user): static
    {
        if ($this->usersUsingAsInterface->removeElement($user)) {
            if ($user->getInterfaceLanguage() === $this) {
                $user->setInterfaceLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersUsingAsTarget(): Collection
    {
        return $this->usersUsingAsTarget;
    }

    public function addUserUsingAsTarget(User $user): static
    {
        if (!$this->usersUsingAsTarget->contains($user)) {
            $this->usersUsingAsTarget->add($user);
            $user->setTargetLanguage($this);
        }

        return $this;
    }

    public function removeUserUsingAsTarget(User $user): static
    {
        if ($this->usersUsingAsTarget->removeElement($user)) {
            if ($user->getTargetLanguage() === $this) {
                $user->setTargetLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->setTargetLanguage($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->removeElement($group)) {
            if ($group->getTargetLanguage() === $this) {
                $group->setTargetLanguage(null);
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
            $quizResult->setLanguage($this);
        }

        return $this;
    }

    public function removeQuizResult(QuizResult $quizResult): static
    {
        if ($this->quizResults->removeElement($quizResult)) {
            // set the owning side to null (unless already changed)
            if ($quizResult->getLanguage() === $this) {
                $quizResult->setLanguage(null);
            }
        }

        return $this;
    }
}