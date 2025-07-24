<?php
namespace App\Controller;

use App\Entity\Phrase;
use App\Entity\PhraseTranslation;
use App\Entity\Theme;
use App\Entity\ThemeTranslation;
use App\Entity\UserPhraseProgress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ThemePhraseTranslationsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    #[Route('/api/themes/{id}/phrases/translated', name: 'theme_phrases_translated', methods: ['GET'])]
    public function __invoke(int $id): JsonResponse
    {
        $user = $this->security->getUser();
        $interfaceLangId = $user->getInterfaceLanguage()->getId();
        $targetLangId = $user->getTargetLanguage()->getId();

        $conn = $this->em->getConnection();

        $sql = "
            SELECT 
                ttt.label as theme_translations_target,
                tti.label as theme_translations_interface,
                p.id as phrase_id,
                pt_target.text as target_text,
                pt_target.id as phrase_translation_id,
                pt_interface.text as interface_text,
                upp.id as progress_id,
                CASE WHEN upp.id IS NOT NULL THEN true ELSE false END as is_known
            FROM phrase p
            LEFT JOIN theme_translation ttt ON ttt.theme_id = p.theme_id AND ttt.language_id = :targetLang
            LEFT JOIN theme_translation tti ON tti.theme_id = p.theme_id AND tti.language_id = :interfaceLang
            LEFT JOIN phrase_translation pt_target ON pt_target.phrase_id = p.id AND pt_target.language_id = :targetLang
            LEFT JOIN phrase_translation pt_interface ON pt_interface.phrase_id = p.id AND pt_interface.language_id = :interfaceLang
            LEFT JOIN user_phrase_progress upp ON upp.phrase_translation_id = pt_target.id AND upp.user_id = :userId
            WHERE p.theme_id = :themeId
        ";


        $result = $conn->executeQuery($sql, [
            'targetLang' => $targetLangId,
            'interfaceLang' => $interfaceLangId,
            'userId' => $user->getId(),
            'themeId' => $id,
        ])->fetchAllAssociative();

        return new JsonResponse($result);
    }
}