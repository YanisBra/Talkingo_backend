<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\GroupMembership;
use Symfony\Bundle\SecurityBundle\Security;


class GroupDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Group) {
            // Generate and assign a unique invitation code if none is set
            if (null === $data->getInvitationCode()) {
                $data->setInvitationCode($this->generateUniqueInvitationCode());
            }

            // Set the currently authenticated user as the group creator if not already set
            if (null === $data->getCreatedBy()) {
                $currentUser = $this->security->getUser();
                if ($currentUser) {
                    $data->setCreatedBy($currentUser);

                    // Automatically add the creator as a member of the group
                    $membership = new GroupMembership();
                    $membership->setUser($currentUser);
                    $membership->setTargetGroup($data);
                    $membership->setJoinedAt(new \DateTimeImmutable());
                    $membership->setIsAdmin(true);
                    $this->em->persist($membership);
                }
            }

            // Assign user's target language as group target language if not already set
            if (null === $data->getTargetLanguage()) {
                $currentUser = $this->security->getUser();
                if ($currentUser && $currentUser->getTargetLanguage()) {
                    $data->setTargetLanguage($currentUser->getTargetLanguage());
                }
            }

            // Persist and save the group
            $this->em->persist($data);
            $this->em->flush();
        }

        return $data;
    }

    public function remove(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // Remove the group from the database
        $this->em->remove($data);
        $this->em->flush();
    }

    // Generates a readable code 
    private function generateReadableCode(int $length): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // avoids I, O, 0, 1
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $code;
    }

    // Loops until a unique invitation code is found
    private function generateUniqueInvitationCode(): string
    {
        do {
            $code = $this->generateReadableCode(8);
            $existing = $this->em->getRepository(Group::class)->findOneBy(['invitationCode' => $code]);
        } while ($existing !== null);

        return $code;
    }
}