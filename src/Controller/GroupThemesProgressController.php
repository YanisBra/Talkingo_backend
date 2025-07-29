<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\PhraseTranslation;
use App\Entity\UserPhraseProgress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// Controller that returns the average learning progress per theme for a given group
class GroupThemesProgressController
{
    // Injects the EntityManager and Security service
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    // Handles the request to compute average theme progress for group members
    public function __invoke(Group $group): JsonResponse
    {
        $user = $this->security->getUser();

        // Verify that the user is a member of the group
        $membership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $group
        ]);

        if (!$membership) {
            throw new AccessDeniedHttpException("You are not a member of this group.");
        }

        // Get the IDs for the group's target language and the user's interface language
        $targetLanguageId = $group->getTargetLanguage()?->getId();
        $interfaceLanguageId = $user->getInterfaceLanguage()?->getId();

        // Get all members of the group
        $members = $this->em->getRepository(GroupMembership::class)
            ->findBy(['targetGroup' => $group]);

        // If no members, return empty result
        if (count($members) === 0) {
            return new JsonResponse([]);
        }

        // Extract the user IDs of group members
        $memberIds = array_map(fn ($m) => $m->getUser()->getId(), $members);

        // Get database connection to execute raw SQL queries
        $conn = $this->em->getConnection();

        // Query to count learned phrases per theme across group members in the target language
        $sql = "
            SELECT p.theme_id, COUNT(DISTINCT upp.id) as known_count
            FROM user_phrase_progress upp
            JOIN phrase_translation pt ON upp.phrase_translation_id = pt.id
            JOIN phrase p ON pt.phrase_id = p.id
            WHERE upp.user_id IN (:memberIds) AND pt.language_id = :languageId
            GROUP BY p.theme_id
        ";
        $known = $conn->executeQuery(
            $sql,
            ['memberIds' => $memberIds, 'languageId' => $targetLanguageId],
            ['memberIds' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY, 'languageId' => \PDO::PARAM_INT]
        )->fetchAllAssociative();

        // Query to count total phrases per theme
        $sqlTotal = "
            SELECT p.theme_id, COUNT(p.id) as phrase_count
            FROM phrase p
            GROUP BY p.theme_id
        ";
        $total = $conn->executeQuery($sqlTotal)->fetchAllAssociative();

        // Map for easy access
        $totalByTheme = [];
        foreach ($total as $t) {
            $totalByTheme[$t['theme_id']] = (int)$t['phrase_count'];
        }

        // Fetch labels for themes in both interface and target languages
        $themeLabels = $conn->executeQuery(
            "
            SELECT tt.theme_id, tt.label, tt.language_id
            FROM theme_translation tt
            WHERE tt.language_id IN (:interfaceLang, :targetLang)
            ",
            ['interfaceLang' => $interfaceLanguageId, 'targetLang' => $targetLanguageId],
            ['interfaceLang' => \PDO::PARAM_INT, 'targetLang' => \PDO::PARAM_INT]
        )->fetchAllAssociative();

        // Organize labels by language for easy lookup
        $themeLabelsByInterface = [];
        $themeLabelsByTarget = [];
        foreach ($themeLabels as $labelRow) {
            if ((int)$labelRow['language_id'] === $interfaceLanguageId) {
                $themeLabelsByInterface[$labelRow['theme_id']] = $labelRow['label'];
            } elseif ((int)$labelRow['language_id'] === $targetLanguageId) {
                $themeLabelsByTarget[$labelRow['theme_id']] = $labelRow['label'];
            }
        }

        // Prepare result array with average progress per theme
        $result = [];
        $allThemeIds = array_unique(array_merge(array_keys($totalByTheme), array_keys($themeLabelsByInterface), array_keys($themeLabelsByTarget)));

        foreach ($allThemeIds as $themeId) {
            // Calculate known and total phrases per theme
            $knownRow = array_filter($known, fn($row) => $row['theme_id'] == $themeId);
            $knownCount = count($knownRow) > 0 ? (int)array_values($knownRow)[0]['known_count'] : 0;
            $totalPhrases = $totalByTheme[$themeId] ?? 0;

            if ($totalPhrases > 0 && count($members) > 0) {
                $average = round(($knownCount / ($totalPhrases * count($members))) * 100);
            } else {
                $average = 0;
            }

            // Append progress data for each theme
            $result[] = [
                'theme' => [
                    'id' => (int)$themeId,
                    'label_interface' => $themeLabelsByInterface[$themeId] ?? null,
                    'label_target' => $themeLabelsByTarget[$themeId] ?? null,
                ],
                'averageProgress' => $average
            ];
        }

        // Calculate global average progress across all themes
        $totalAverageProgress = count($result) > 0
            ? round(array_sum(array_column($result, 'averageProgress')) / count($result))
            : 0;

        // Return the list of themes with their average progress and global progress
        return new JsonResponse([
            'themes' => $result,
            'totalAverageProgress' => $totalAverageProgress
        ]);
    }
}