<?php

namespace App\DataPersister;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;


class UserDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security
    ) {}

   public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
{
    if (!$data instanceof User) {
        return $data;
    }

    $currentUser = $this->security->getUser();

        // Prevents non-admins from changing roles
        if (!$this->security->isGranted('ROLE_ADMIN')) {
        // Always enforce default roles if not admin
        $data->setRoles(['ROLE_USER']);
}

    if ($data->getPlainPassword()) {
        $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPlainPassword());
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
