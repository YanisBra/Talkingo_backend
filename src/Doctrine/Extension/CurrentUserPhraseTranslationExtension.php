<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\PhraseTranslation;


// Filter translations matching with user interface or target language.
class CurrentUserPhraseTranslationExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    // Injects the Symfony Security service to access the currently authenticated user.
    public function __construct(private Security $security) {}

    // Adds filtering conditions to the query for non-admin users.
    // Limits PhraseTranslation results to those in the user's interface or target language.
    private function addWhere(QueryBuilder $qb, string $resourceClass): void
    {
        if ($resourceClass !== PhraseTranslation::class) {
            return;
        }

        // Get the currently authenticated user and Admins can access everything
        $user = $this->security->getUser();
        if (!$user || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return; 
        }
        // Add a condition to only return translations for the user's target language
        $alias = $qb->getRootAliases()[0];
            $qb
                ->andWhere(sprintf('%s.language IN (:langs)', $alias))
                ->setParameter('langs', [
                    $user->getInterfaceLanguage(),
                    $user->getTargetLanguage(),
                ]); 
    }

    // Applies the filtering logic to collection endpoints (e.g. GET /api/phrase_translations)
    public function applyToCollection(QueryBuilder $qb, QueryNameGeneratorInterface $qng, string $resourceClass, ?\ApiPlatform\Metadata\Operation $op = null, array $context = []): void
    {
        $this->addWhere($qb, $resourceClass);
    }

    // Applies the filtering logic to item endpoints (e.g. GET /api/phrase_translations/{id})
    public function applyToItem(QueryBuilder $qb, QueryNameGeneratorInterface $qng, string $resourceClass, array $uriVars = [], ?\ApiPlatform\Metadata\Operation $op = null, array $context = []): void
    {
        $this->addWhere($qb, $resourceClass);
    }
}