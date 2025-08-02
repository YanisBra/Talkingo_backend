<?php

namespace App\Tests\DataPersister;

use App\DataPersister\GroupDataPersister;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Language;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class GroupDataPersisterTest extends TestCase
{
    private EntityManagerInterface $em;
    private Security $security;
    private GroupDataPersister $persister;
    private EntityRepository $groupRepository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);

        $this->groupRepository = $this->createMock(EntityRepository::class);
        $this->groupRepository->method('findOneBy')->willReturn(null);

        $this->em
            ->method('getRepository')
            ->with(Group::class)
            ->willReturn($this->groupRepository);

        $this->persister = new GroupDataPersister($this->em, $this->security);
    }

    public function testCreateGroupAssignsFieldsCorrectly(): void
    {
        // Arrange: create a user with a target language
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');

        $language = new Language();
        $language->setCode('en');
        $language->setLabel('English');
        $user->setTargetLanguage($language);

        $this->security->method('getUser')->willReturn($user);

        // Ensure the generated invitation code is considered unique
        // Expect persist to be called for both Group and GroupMembership entities
        $this->em->expects($this->exactly(2))
            ->method('persist')
            ->with($this->callback(function ($entity) use ($user, $language) {
                if ($entity instanceof Group) {
                    // Assert proper values are automatically assigned to the Group entity
                    $this->assertNotNull($entity->getInvitationCode());
                    $this->assertSame($user, $entity->getCreatedBy());
                    $this->assertSame($language, $entity->getTargetLanguage());
                    return true;
                }

                if ($entity instanceof GroupMembership) {
                    // Assert that the creator becomes an admin member of the group
                    $this->assertSame($user, $entity->getUser());
                    $this->assertTrue($entity->isIsAdmin());
                    $this->assertNotNull($entity->getTargetGroup());
                    return true;
                }

                return false;
            }));

        $this->em->expects($this->once())
            ->method('flush');

        // Act: trigger the data persister to process the group
        $group = new Group();
        $this->persister->process($group, new \ApiPlatform\Metadata\Post());
    }
}