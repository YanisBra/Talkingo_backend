<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\UserPhraseProgress;

/**
 * This extension automatically filters UserPhraseProgress resources
 * to ensure that users only access their own progress records.
 */
class CurrentUserUserPhraseProgressExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    /**
     * Adds a condition to restrict access to only the authenticated user's records.
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // Only apply this filter for UserPhraseProgress entities
        if ($resourceClass !== UserPhraseProgress::class) {
            return;
        }

        // Get the currently authenticated user
        $user = $this->security->getUser();
        if (!$user || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        // Add a condition to only return records that belong to the current user
        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.user = :current_user', $alias))
            ->setParameter('current_user', $user);
    }

    /**
     * Applies the filter to collection endpoints ( GET /api/user_phrase_progresses)
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?\ApiPlatform\Metadata\Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Applies the filter to item endpoints ( GET /api/user_phrase_progresses/{id})
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $uriVariables = [], ?\ApiPlatform\Metadata\Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }
}