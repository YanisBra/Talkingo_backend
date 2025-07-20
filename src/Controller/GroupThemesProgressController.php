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

class GroupThemesProgressController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function __invoke(Group $group): JsonResponse
    {
        $user = $this->security->getUser();

        // Security: only group members can access
        $membership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $group
        ]);

        if (!$membership) {
            throw new AccessDeniedHttpException("You are not a member of this group.");
        }

        $targetLanguageId = $group->getTargetLanguage()?->getId();
        $interfaceLanguageId = $user->getInterfaceLanguage()?->getId();

        // Get all members
        $members = $this->em->getRepository(GroupMembership::class)
            ->findBy(['targetGroup' => $group]);

        if (count($members) === 0) {
            return new JsonResponse([]);
        }

        $memberIds = array_map(fn ($m) => $m->getUser()->getId(), $members);

        // Get total learned phrases per theme per user
        $conn = $this->em->getConnection();
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

        // Get total phrases per theme
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

        $themeLabels = $conn->executeQuery(
            "
            SELECT tt.theme_id, tt.label, tt.language_id
            FROM theme_translation tt
            WHERE tt.language_id IN (:interfaceLang, :targetLang)
            ",
            ['interfaceLang' => $interfaceLanguageId, 'targetLang' => $targetLanguageId],
            ['interfaceLang' => \PDO::PARAM_INT, 'targetLang' => \PDO::PARAM_INT]
        )->fetchAllAssociative();

        $themeLabelsByInterface = [];
        $themeLabelsByTarget = [];
        foreach ($themeLabels as $labelRow) {
            if ((int)$labelRow['language_id'] === $interfaceLanguageId) {
                $themeLabelsByInterface[$labelRow['theme_id']] = $labelRow['label'];
            } elseif ((int)$labelRow['language_id'] === $targetLanguageId) {
                $themeLabelsByTarget[$labelRow['theme_id']] = $labelRow['label'];
            }
        }

        // Calculate average per theme
        $result = [];
        foreach ($known as $row) {
            $themeId = $row['theme_id'];
            $knownCount = (int)$row['known_count'];
            $totalPhrases = $totalByTheme[$themeId] ?? 0;

            if ($totalPhrases > 0) {
                $average = round(($knownCount / ($totalPhrases * count($members))) * 100);
            } else {
                $average = 0;
            }

            $result[] = [
                'theme' => [
                    'id' => (int)$themeId,
                    'label_interface' => $themeLabelsByInterface[$themeId] ?? null,
                    'label_target' => $themeLabelsByTarget[$themeId] ?? null,
                ],
                'averageProgress' => $average
            ];
        }

        return new JsonResponse($result);
    }
}