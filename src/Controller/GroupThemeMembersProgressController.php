<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Controller for returning individual progress of each group member for a given theme.
class GroupThemeMembersProgressController
{
    // Constructor injecting security and database access.
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function __invoke(int $groupId, int $themeId): JsonResponse
    {
        // Get the currently authenticated user.
        $user = $this->security->getUser();

        //  Check if the group exists by ID.
        $group = $this->em->getRepository(Group::class)->find($groupId);
        if (!$group) {
            throw new NotFoundHttpException("Group not found.");
        }

        // Ensure the user is a member of the group.
        $membership = $this->em->getRepository(GroupMembership::class)->findOneBy([
            'user' => $user,
            'targetGroup' => $group
        ]);
        if (!$membership) {
            throw new AccessDeniedHttpException("You are not a member of this group.");
        }

        // Get the ID of the target language used by the group.
        $targetLanguageId = $group->getTargetLanguage()?->getId();

        // Count the number of phrases linked to the given theme.
        $sqlTotal = "
            SELECT COUNT(p.id) as total_phrases
            FROM phrase p
            WHERE p.theme_id = :themeId
        ";
        $total = (int) $this->em->getConnection()->executeQuery(
            $sqlTotal,
            ['themeId' => $themeId]
        )->fetchOne();

        // If no phrases found, return empty result.
        if ($total === 0) {
            return new JsonResponse([], 200);
        }

        // Compute how many phrases each member has learned in the group for this theme.
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

        // Execute the progress query.
        $rows = $this->em->getConnection()->executeQuery(
            $sql,
            ['groupId' => $groupId, 'themeId' => $themeId, 'langId' => $targetLanguageId]
        )->fetchAllAssociative();

        // Format the data to include user info and percentage learned.
        $result = array_map(fn ($row) => [
            'user' => [
                'id' => (int) $row['user_id'],
                'name' => $row['name']
            ],
            'progress' => round(($row['learned'] / $total) * 100)
        ], $rows);

        // Return the formatted progress data.
        return new JsonResponse($result);
    }
}