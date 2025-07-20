<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupThemeMembersProgressController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function __invoke(int $groupId, int $themeId): JsonResponse
    {
        $user = $this->security->getUser();

        // 1. Check if the group exists
        $group = $this->em->getRepository(Group::class)->find($groupId);
        if (!$group) {
            throw new NotFoundHttpException("Group not found.");
        }

        // 2. Check if the current user is a member of the group
        $membership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $group
        ]);
        if (!$membership) {
            throw new AccessDeniedHttpException("You are not a member of this group.");
        }

        // 3. Get the group's target language
        $targetLanguageId = $group->getTargetLanguage()?->getId();

        // 4. Retrieve total number of phrases in the theme
        $sqlTotal = "
            SELECT COUNT(p.id) as total_phrases
            FROM phrase p
            WHERE p.theme_id = :themeId
        ";
        $total = (int) $this->em->getConnection()->executeQuery(
            $sqlTotal,
            ['themeId' => $themeId]
        )->fetchOne();

        if ($total === 0) {
            return new JsonResponse([], 200);
        }

        // 5. Compute individual progress for each group member
        $sql = "
            SELECT u.id as user_id, u.name, 
                   COUNT(DISTINCT CASE WHEN pt.language_id = :langId AND p.theme_id = :themeId THEN upp.id END) as learned
            FROM user u
            JOIN group_membership gm ON gm.user_id = u.id
            LEFT JOIN user_phrase_progress upp ON upp.user_id = u.id
            LEFT JOIN phrase_translation pt ON pt.id = upp.phrase_translation_id
            LEFT JOIN phrase p ON pt.phrase_id = p.id
            WHERE gm.target_group_id = :groupId
            GROUP BY u.id, u.name
        ";

        $rows = $this->em->getConnection()->executeQuery(
            $sql,
            ['groupId' => $groupId, 'themeId' => $themeId, 'langId' => $targetLanguageId]
        )->fetchAllAssociative();

        // 6. Format the results
        $result = array_map(fn ($row) => [
            'user' => [
                'id' => (int) $row['user_id'],
                'name' => $row['name']
            ],
            'progress' => round(($row['learned'] / $total) * 100)
        ], $rows);

        return new JsonResponse($result);
    }
}