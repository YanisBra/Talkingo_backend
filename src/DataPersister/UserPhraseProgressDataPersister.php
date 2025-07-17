<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\UserPhraseProgress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\UserPhraseProgressRepository; 


class UserPhraseProgressDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private UserPhraseProgressRepository $userPhraseProgressRepository

    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof UserPhraseProgress) {
            $currentUser = $this->security->getUser();

            // Prevent duplicate learning entries for the same phrase by the same user
            $existing = $this->userPhraseProgressRepository->findOneBy([
                'user' => $currentUser,
                'phraseTranslation' => $data->getPhraseTranslation(),
            ]);

            if ($existing !== null) {
                throw new \RuntimeException('You already marked this phrase as learned.');
            }

            // If no user is defined, assign the currently authenticated user
            if (null === $data->getUser()) {
                $data->setUser($currentUser);
            }

            // Prevent a user from modifying someone else's progress
            if ($data->getUser() !== $currentUser) {
                throw new \RuntimeException('You can only update your own progress.');
            }

            $this->em->persist($data);
            $this->em->flush();
        }

        return $data;
    }

    public function remove(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $this->em->remove($data);
        $this->em->flush();
    }
}