<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\GroupMembership;

class CurrentUserGroupMembershipExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}

    private function addWhere(QueryBuilder $qb, string $resourceClass): void
    {
        if ($resourceClass !== GroupMembership::class) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        $alias = $qb->getRootAliases()[0];

        $qb
            ->andWhere("$alias.user = :currentUser")
            ->setParameter('currentUser', $user);
    }

    public function applyToCollection(QueryBuilder $qb, QueryNameGeneratorInterface $qng, string $resourceClass, ?\ApiPlatform\Metadata\Operation $op = null, array $context = []): void
    {
        $this->addWhere($qb, $resourceClass);
    }

    public function applyToItem(QueryBuilder $qb, QueryNameGeneratorInterface $qng, string $resourceClass, array $uriVars = [], ?\ApiPlatform\Metadata\Operation $op = null, array $context = []): void
    {
        $this->addWhere($qb, $resourceClass);
    }
}