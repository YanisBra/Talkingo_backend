<?php

namespace App\Tests\DataPersister;

use App\DataPersister\UserPhraseProgressDataPersister;
use App\Entity\User;
use App\Entity\PhraseTranslation;
use App\Entity\UserPhraseProgress;
use App\Repository\UserPhraseProgressRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class UserPhraseProgressDataPersisterTest extends TestCase
{
    private $em;
    private $security;
    private $repository;
    private $dataPersister;
    private $user;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->repository = $this->createMock(UserPhraseProgressRepository::class);

        $this->dataPersister = new UserPhraseProgressDataPersister(
            $this->em,
            $this->security,
            $this->repository
        );

        $this->user = (new User())->setEmail('test@example.com');
    }

    // Ensure duplicate progress is not allowed
    public function testDuplicateProgressThrowsException(): void
    {
        $progress = new UserPhraseProgress();
        $phraseTranslation = $this->createMock(PhraseTranslation::class);
        $progress->setPhraseTranslation($phraseTranslation);

        $this->security->method('getUser')->willReturn($this->user);
        $this->repository->method('findOneBy')->willReturn(new UserPhraseProgress());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You already marked this phrase as learned.');

        $this->dataPersister->process($progress, new \ApiPlatform\Metadata\Post(), [], []);
    }

    // Ensure the current user is assigned if missing in progress
    public function testAssignCurrentUserIfMissing(): void
    {
        $progress = new UserPhraseProgress();
        $phraseTranslation = $this->createMock(PhraseTranslation::class);
        $progress->setPhraseTranslation($phraseTranslation);
        $progress->setUser(null);

        $this->repository->method('findOneBy')->willReturn(null);
        $this->security->method('getUser')->willReturn($this->user);

        $this->em->expects($this->once())->method('persist')->with($this->callback(
            fn(UserPhraseProgress $p) => $p->getUser() === $this->user
        ));
        $this->em->expects($this->once())->method('flush');

        $this->dataPersister->process($progress, new \ApiPlatform\Metadata\Post(), [], []);
    }

    // Ensure a user cannot update another user's progress
    public function testCannotUpdateOthersProgress(): void
    {
        $progress = new UserPhraseProgress();
        $otherUser = (new User())->setEmail('other@example.com');
        $progress->setUser($otherUser);
        $progress->setPhraseTranslation($this->createMock(PhraseTranslation::class));

        $this->security->method('getUser')->willReturn($this->user);
        $this->repository->method('findOneBy')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can only update your own progress.');

        $this->dataPersister->process($progress, new \ApiPlatform\Metadata\Post(), [], []);
    }

    // Ensure a new progress is correctly persisted
    public function testPersistNewProgress(): void
    {
        $progress = new UserPhraseProgress();
        $progress->setUser($this->user);
        $progress->setPhraseTranslation($this->createMock(PhraseTranslation::class));

        $this->repository->method('findOneBy')->willReturn(null);
        $this->security->method('getUser')->willReturn($this->user);

        $this->em->expects($this->once())->method('persist')->with($progress);
        $this->em->expects($this->once())->method('flush');

        $result = $this->dataPersister->process($progress, new \ApiPlatform\Metadata\Post(), [], []);
        $this->assertSame($progress, $result);
    }

    // Ensure a progress is removed correctly
    public function testRemoveProgress(): void
    {
        $progress = new UserPhraseProgress();

        $this->em->expects($this->once())->method('remove')->with($progress);
        $this->em->expects($this->once())->method('flush');

        $this->dataPersister->remove($progress, new \ApiPlatform\Metadata\Delete(), [], []);
    }
}