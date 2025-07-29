<?php

namespace App\Controller;

use App\Entity\PhraseTranslation;
use App\Entity\UserPhraseProgress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Controller to fetch user's learning progress per theme
#[Route('/api/users/me/themes/progress', name: 'user_theme_progress', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class UserThemeProgressController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    // Main controller logic invoked when endpoint is accessed
    public function __invoke(): JsonResponse
    {
        // Get the current authenticated user
        $user = $this->security->getUser();

        // Get user's interface and target language IDs
        $targetLanguageId = $user->getTargetLanguage()?->getId();
        $interfaceLanguageId = $user->getInterfaceLanguage()?->getId();

        // Query to count total phrases per theme
        $total = $this->em->getConnection()->executeQuery(
            "SELECT p.theme_id, COUNT(p.id) as phrase_count FROM phrase p GROUP BY p.theme_id"
        )->fetchAllAssociative();

        // Reformat result into associative array for easier access
        $totalByTheme = [];
        foreach ($total as $t) {
            $totalByTheme[$t['theme_id']] = (int)$t['phrase_count'];
        }

        // Query to count how many phrases are known per theme by current user
        $known = $this->em->getConnection()->executeQuery(
            "SELECT p.theme_id, COUNT(DISTINCT upp.id) as known_count
             FROM user_phrase_progress upp
             JOIN phrase_translation pt ON upp.phrase_translation_id = pt.id
             JOIN phrase p ON pt.phrase_id = p.id
             WHERE upp.user_id = :userId AND pt.language_id = :languageId
             GROUP BY p.theme_id",
            ['userId' => $user->getId(), 'languageId' => $targetLanguageId]
        )->fetchAllAssociative();

        // Reformat known phrases result
        $knownByTheme = [];
        foreach ($known as $k) {
            $knownByTheme[$k['theme_id']] = (int)$k['known_count'];
        }

        // Fetch theme translations for both interface and target language
        $labels = $this->em->getConnection()->executeQuery(
            "SELECT theme_id, label, language_id FROM theme_translation
            WHERE language_id IN (:interfaceLang, :targetLang)",
                [
                    'interfaceLang' => $interfaceLanguageId,
                    'targetLang' => $targetLanguageId
                ],
                [
                    'interfaceLang' => \PDO::PARAM_INT,
                    'targetLang' => \PDO::PARAM_INT
                ]
        )->fetchAllAssociative();

        // Separate labels into two maps: one for interface language, one for target
        $themeLabelsByInterface = [];
        $themeLabelsByTarget = [];
        foreach ($labels as $l) {
            if ((int)$l['language_id'] === $interfaceLanguageId) {
                $themeLabelsByInterface[$l['theme_id']] = $l['label'];
            } elseif ((int)$l['language_id'] === $targetLanguageId) {
                $themeLabelsByTarget[$l['theme_id']] = $l['label'];
            }
        }

        // Build final result array with progress percentage per theme
        $result = [];
        foreach ($themeLabelsByInterface as $themeId => $interfaceLabel) {
            $targetLabel = $themeLabelsByTarget[$themeId] ?? null;
            $knownCount = $knownByTheme[$themeId] ?? 0;
            $totalPhrases = $totalByTheme[$themeId] ?? 0;

            // Calculate progress percentage
            $progress = $totalPhrases > 0
                ? round(($knownCount / $totalPhrases) * 100)
                : 0;

            // Append theme data and progress to result
            $result[] = [
                'theme' => [
                    'id' => (int)$themeId,
                    'label_interface' => $interfaceLabel,
                    'label_target' => $targetLabel,
                ],
                'progress' => $progress,
            ];
        }

        // Return JSON response
        return new JsonResponse($result);
    }
}
