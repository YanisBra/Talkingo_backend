<?php

/**
 * This file defines a Doctrine ORM extension that restricts access to Group entities
 * based on the current user's group memberships.
 * It ensures that non-admin users only see groups they belong to.
 */

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Group;
use App\Entity\GroupMembership;
use ApiPlatform\Metadata\Operation;


 // Filters visible groups based on the current user's memberships.
class CurrentUserGroupExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    //Injects the security service.
    public function __construct(private Security $security) {}


    // Restricts access to groups where the user is a member unless they are admin.
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // Only apply this extension to the Group entity
        if ($resourceClass !== Group::class) {
            return;
        }

        $user = $this->security->getUser();
        // Skip filtering for admins or if no user is logged in
        if (!$user || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        // Filter groups to those where the user is a member
        $queryBuilder
            ->join("$alias.groupMemberships", "gm")
            ->andWhere("gm.user = :currentUser")
            ->setParameter("currentUser", $user);
    }


    // Applies the filter to collection routes.
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = [] ): void 
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }


    //Applies the filter to item routes.
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = [] ): void 
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }
}