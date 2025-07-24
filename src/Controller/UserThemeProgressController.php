<?php

namespace App\Controller;

use App\Entity\PhraseTranslation;
use App\Entity\UserPhraseProgress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users/me/themes/progress', name: 'user_theme_progress', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class UserThemeProgressController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        $targetLanguageId = $user->getTargetLanguage()?->getId();
        $interfaceLanguageId = $user->getInterfaceLanguage()?->getId();

        // Total phrases per theme
        $total = $this->em->getConnection()->executeQuery(
            "SELECT p.theme_id, COUNT(p.id) as phrase_count FROM phrase p GROUP BY p.theme_id"
        )->fetchAllAssociative();

        $totalByTheme = [];
        foreach ($total as $t) {
            $totalByTheme[$t['theme_id']] = (int)$t['phrase_count'];
        }

        // Known phrases per theme for current user
        $known = $this->em->getConnection()->executeQuery(
            "SELECT p.theme_id, COUNT(DISTINCT upp.id) as known_count
             FROM user_phrase_progress upp
             JOIN phrase_translation pt ON upp.phrase_translation_id = pt.id
             JOIN phrase p ON pt.phrase_id = p.id
             WHERE upp.user_id = :userId AND pt.language_id = :languageId
             GROUP BY p.theme_id",
            ['userId' => $user->getId(), 'languageId' => $targetLanguageId]
        )->fetchAllAssociative();

        $knownByTheme = [];
        foreach ($known as $k) {
            $knownByTheme[$k['theme_id']] = (int)$k['known_count'];
        }

        // Labels for themes (interface language only)
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

        $themeLabelsByInterface = [];
        $themeLabelsByTarget = [];
        foreach ($labels as $l) {
            if ((int)$l['language_id'] === $interfaceLanguageId) {
                $themeLabelsByInterface[$l['theme_id']] = $l['label'];
            } elseif ((int)$l['language_id'] === $targetLanguageId) {
                $themeLabelsByTarget[$l['theme_id']] = $l['label'];
            }
        }

        // Build result
        $result = [];
        foreach ($themeLabelsByInterface as $themeId => $interfaceLabel) {
            $targetLabel = $themeLabelsByTarget[$themeId] ?? null;
            $knownCount = $knownByTheme[$themeId] ?? 0;
            $totalPhrases = $totalByTheme[$themeId] ?? 0;

            $progress = $totalPhrases > 0
                ? round(($knownCount / $totalPhrases) * 100)
                : 0;

            $result[] = [
                'theme' => [
                    'id' => (int)$themeId,
                    'label_interface' => $interfaceLabel,
                    'label_target' => $targetLabel,
                ],
                'progress' => $progress,
            ];
        }

        return new JsonResponse($result);
    }
}
