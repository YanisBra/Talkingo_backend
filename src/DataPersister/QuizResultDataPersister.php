<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\QuizResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class QuizResultDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof QuizResult) {
            return $data;
        }

        $user = $this->security->getUser();

        // Set the authenticated user
        $data->setUser($user);

        // Set the language to the user's target language
        $data->setLanguage($user->getTargetLanguage());

        // Set the end date if not provided
        if (null === $data->getEndDate()) {
            $data->setEndDate(new \DateTimeImmutable());
        }

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }

    public function remove(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $this->em->remove($data);
        $this->em->flush();
    }
}