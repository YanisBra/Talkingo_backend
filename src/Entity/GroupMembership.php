<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GroupMembershipRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\User;
use App\Entity\Group;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;



#[ORM\Entity(repositoryClass: GroupMembershipRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['targetGroup' => 'exact'])]
#[ApiResource(
    normalizationContext: ['groups' => ['group_membership:read']],
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "object.getTargetGroup().getCreatedBy() == user or is_granted('ROLE_ADMIN')"),
        new Patch(security: "object.getTargetGroup().getCreatedBy() == user or is_granted('ROLE_ADMIN')"),
        new Delete(security: "(object.getTargetGroup().getCreatedBy() == user or is_granted('ROLE_ADMIN')) and object.getUser() != user"),
    ]
)]
class GroupMembership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['group_membership:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'groupMemberships')]
    #[Groups(['group_membership:read', 'group_membership:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'groupMemberships')]
    #[Groups(['group_membership:read', 'group_membership:write'])]
    private ?Group $targetGroup = null;

    #[ORM\Column]
    #[Groups(['group_membership:read'])]
    private ?\DateTimeImmutable $joinedAt = null;

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

    public function getTargetGroup(): ?Group
    {
        return $this->targetGroup;
    }

    public function setTargetGroup(?Group $targetGroup): static
    {
        $this->targetGroup = $targetGroup;

        return $this;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }
}
