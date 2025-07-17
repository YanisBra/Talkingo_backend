<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'usersUsingAsInterface')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $interfaceLanguage = null;

    #[ORM\ManyToOne(inversedBy: 'usersUsingAsTarget')]
    #[ORM\JoinColumn(nullable: false)]
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
