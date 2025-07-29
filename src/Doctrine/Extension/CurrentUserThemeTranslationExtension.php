<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\ThemeTranslation;

// Filter translations matching with user interface or target language.
class CurrentUserThemeTranslationExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}


     // Adds a condition to restrict access to only relevant ThemeTranslations.
     // Only applies for ThemeTranslation entities and non-admin users.
    private function addWhere(QueryBuilder $qb, string $resourceClass): void
    {
        // Only apply this filter for ThemeTranslation entities
        if ($resourceClass !== ThemeTranslation::class) {
            return;
        }

        // Get the currently authenticated user and allow admins full access
        $user = $this->security->getUser();
        if (!$user || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return; 
        }

        // Add a condition to only return translations for the user's interface and target languages
        $alias = $qb->getRootAliases()[0];
        $qb
            ->andWhere(sprintf('%s.language IN (:langs)', $alias))
            ->setParameter('langs', [
                $user->getInterfaceLanguage(),
                $user->getTargetLanguage(),
            ]); 
    }

    // Applies the filter to collection endpoints (GET /api/theme_translations)
    public function applyToCollection(QueryBuilder $qb, QueryNameGeneratorInterface $qng, string $resourceClass, ?\ApiPlatform\Metadata\Operation $op = null, array $context = []): void
    {
        $this->addWhere($qb, $resourceClass);
    }

    // Applies the filter to item endpoints (GET /api/theme_translations/{id})
    public function applyToItem(QueryBuilder $qb, QueryNameGeneratorInterface $qng, string $resourceClass, array $uriVars = [], ?\ApiPlatform\Metadata\Operation $op = null, array $context = []): void
    {
        $this->addWhere($qb, $resourceClass);
    }
}