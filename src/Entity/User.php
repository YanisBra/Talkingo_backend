<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use App\DataPersister\UserDataPersister;

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    operations: [
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(processor: UserDataPersister::class),
        new Get(security: "object == user or is_granted('ROLE_ADMIN')"),
        new Put(processor: UserDataPersister::class, security: "object == user or is_granted('ROLE_ADMIN')"),
        new Patch(processor: UserDataPersister::class, security: "object == user or is_granted('ROLE_ADMIN')"),
        new Delete(security: "object == user or is_granted('ROLE_ADMIN')")
    ]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Email is required.")]
    #[Assert\Email(message: "The email '{{ value }}' is not valid.")]
    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[Assert\NotBlank(message: "Password is required.")]
    #[Assert\Length(
        min: 12,
        max: 255,
        minMessage: "Password must be at least {{ limit }} characters long.",
        maxMessage: "Password cannot be longer than {{ limit }} characters."
    )]
    #[ORM\Column(length: 255)]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[Assert\NotBlank(message: "Name is required.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Name must be at least {{ limit }} characters long.",
        maxMessage: "Name cannot be longer than {{ limit }} characters."
    )]
    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[Assert\NotNull(message: "Interface language is required.")]
    #[ORM\ManyToOne(inversedBy: 'usersUsingAsInterface')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read', 'user:write'])]
    private ?Language $interfaceLanguage = null;

    #[Assert\NotNull(message: "Target language is required.")]
    #[ORM\ManyToOne(inversedBy: 'usersUsingAsTarget')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read', 'user:write'])]
    private ?Language $targetLanguage = null;

    /**
     * @var Collection<int, UserPhraseProgress>
     */
    #[ORM\OneToMany(targetEntity: UserPhraseProgress::class, mappedBy: 'user')]
    private Collection $userPhraseProgress;

    public function __construct()
    {
        $this->roles = ['ROLE_USER']; 
        $this->createdAt = new \DateTimeImmutable();
        $this->userPhraseProgress = new ArrayCollection();
    }

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

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

     public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $userPhraseProgress->setUser($this);
        }

        return $this;
    }

    public function removeUserPhraseProgress(UserPhraseProgress $userPhraseProgress): static
    {
        if ($this->userPhraseProgress->removeElement($userPhraseProgress)) {
            // set the owning side to null (unless already changed)
            if ($userPhraseProgress->getUser() === $this) {
                $userPhraseProgress->setUser(null);
            }
        }

        return $this;
    }
}
