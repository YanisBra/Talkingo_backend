<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\DataPersister\GroupDataPersister;
use App\Controller\GroupLeaveController;
use App\Controller\GroupThemesProgressController;
use App\Controller\GroupThemeMembersProgressController;


#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
#[ApiResource(
    normalizationContext: ['groups' => ['group:read']],
    denormalizationContext: ['groups' => ['group:write']],
    operations: [
        new GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
        new Get(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
        new Post(security: "is_granted('ROLE_USER')", processor: GroupDataPersister::class), 
        new Put(security: "object.getCreatedBy() === user or is_granted('ROLE_ADMIN')", processor: GroupDataPersister::class),
        new Patch(security: "object.getCreatedBy() === user or is_granted('ROLE_ADMIN')", processor: GroupDataPersister::class),
        new Delete(security: "object.getCreatedBy() === user or is_granted('ROLE_ADMIN')"),
        new Post(
            uriTemplate: '/groups/{id}/leave',
            controller: GroupLeaveController::class,
            name: 'group_leave',
            security: "is_granted('ROLE_USER')"
        ),
        new Get(
            uriTemplate: '/groups/{id}/themes/progress',
            controller: GroupThemesProgressController::class,
            name: 'group_theme_progress',
            read: false,
            deserialize: false,
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['group_theme_progress:read']]
        ),
        new Get(
            uriTemplate: '/groups/{groupId}/themes/{themeId}/members/progress',
            controller: GroupThemeMembersProgressController::class,
            name: 'group_theme_members_progress',
            read: false,
            deserialize: false,
            security: "is_granted('ROLE_USER')"
        )
    ]
)]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['group:read', 'group:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['group:read', 'group:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['group:read', 'group:write'])]
    private ?string $invitationCode = null;

    #[ORM\Column]
    #[Groups(['group:read', 'group:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'groups')]
    #[Groups(['group:read', 'group:write'])]
    private ?Language $targetLanguage = null;

    #[ORM\ManyToOne(inversedBy: 'groups')]
    #[Groups(['group:read'])]
    private ?User $createdBy = null;

    /**
     * @var Collection<int, GroupMembership>
     */
    #[ORM\OneToMany(
        targetEntity: GroupMembership::class,
        mappedBy: 'targetGroup',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $groupMemberships;

    public function __construct()
    {
        $this->groupMemberships = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInvitationCode(): ?string
    {
        return $this->invitationCode;
    }

    public function setInvitationCode(string $invitationCode): static
    {
        $this->invitationCode = $invitationCode;

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

    public function getTargetLanguage(): ?Language
    {
        return $this->targetLanguage;
    }

    public function setTargetLanguage(?Language $targetLanguage): static
    {
        $this->targetLanguage = $targetLanguage;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection<int, GroupMembership>
     */
    public function getGroupMemberships(): Collection
    {
        return $this->groupMemberships;
    }

    public function addGroupMembership(GroupMembership $groupMembership): static
    {
        if (!$this->groupMemberships->contains($groupMembership)) {
            $this->groupMemberships->add($groupMembership);
            $groupMembership->setTargetGroup($this);
        }

        return $this;
    }

    public function removeGroupMembership(GroupMembership $groupMembership): static
    {
        if ($this->groupMemberships->removeElement($groupMembership)) {
            if ($groupMembership->getTargetGroup() === $this) {
                $groupMembership->setTargetGroup(null);
            }
        }

        return $this;
    }
}
