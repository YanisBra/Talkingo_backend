<?php

namespace App\DataPersister;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class UserDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {

        if ($data instanceof User && $data->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());
            $data->setPassword($hashedPassword);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }

    public function remove(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}