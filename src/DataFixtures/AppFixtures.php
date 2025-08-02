<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Language;
use App\Entity\Theme;
use App\Entity\ThemeTranslation;
use App\Entity\Phrase;
use App\Entity\PhraseTranslation;
use App\Entity\UserPhraseProgress;
use App\Entity\Group;
use App\Entity\GroupMembership;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        //Languages
        $language1 = new Language();
        $language1->setCode('fr')->setLabel('Français');

        $language2 = new Language();
        $language2->setCode('en')->setLabel('English');

        $language3 = new Language();
        $language3->setCode('es')->setLabel('Español');

        $manager->persist($language1);
        $manager->persist($language2);
        $manager->persist($language3);

        //Users
        $user = new User();
        $user  ->setEmail('admin@admin.com')
                ->setName('admin')
                ->setRoles(['ROLE_ADMIN'])
                ->setInterfaceLanguage($language1)
                ->setTargetLanguage($language2)
                ->setPassword($this->passwordHasher->hashPassword($user, 'pass123'));

        $user1 = new User();
        $user1  ->setEmail('user@user.com')
                ->setName('user')
                ->setInterfaceLanguage($language1)
                ->setTargetLanguage($language2)
                ->setPassword($this->passwordHasher->hashPassword($user1, 'pass123'));

        $user2= new User();
        $user2  ->setEmail('user2@user.com')
                ->setName('user2')
                ->setInterfaceLanguage($language2)
                ->setTargetLanguage($language3)
                ->setPassword($this->passwordHasher->hashPassword($user2, 'pass123'));

        $user3= new User();
        $user3  ->setEmail('user3@user.com')
                ->setName('user3')
                ->setInterfaceLanguage($language2)
                ->setTargetLanguage($language3)
                ->setPassword($this->passwordHasher->hashPassword($user3, 'pass123'));

        
        $user4= new User();
        $user4  ->setEmail('user4@user.com')
                ->setName('user4')
                ->setInterfaceLanguage($language2)
                ->setTargetLanguage($language3)
                ->setPassword($this->passwordHasher->hashPassword($user4, 'pass123'));

        $manager->persist($user);
        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->persist($user4);



        //Themes
        $theme1 = new Theme();
        $theme1->setCode('AIRPORT');
        
        $theme2 = new Theme();
        $theme2->setCode('RESTAURANT');

        $manager->persist($theme1);
        $manager->persist($theme2);

        $theme_translation1 = new ThemeTranslation();
        $theme_translation1 ->setLabel('Aéroport')
                            ->setTheme($theme1)
                            ->setLanguage($language1);

        $theme_translation2 = new ThemeTranslation();
        $theme_translation2 ->setLabel('Airport')
                            ->setTheme($theme1)
                            ->setLanguage($language2);

        $theme_translation3 = new ThemeTranslation();
        $theme_translation3 ->setLabel('Aeropuerto')
                            ->setTheme($theme1)
                            ->setLanguage($language3);

        $theme_translation4 = new ThemeTranslation();
        $theme_translation4 ->setLabel('Restaurant')
                            ->setTheme($theme2)
                            ->setLanguage($language1);  

        $theme_translation5 = new ThemeTranslation();
        $theme_translation5 ->setLabel('Restaurant')
                            ->setTheme($theme2)
                            ->setLanguage($language2);  


        $theme_translation6 = new ThemeTranslation();
        $theme_translation6 ->setLabel('Restaurante')
                            ->setTheme($theme2)
                            ->setLanguage($language3);

        $manager->persist($theme_translation1);
        $manager->persist($theme_translation2);
        $manager->persist($theme_translation3);
        $manager->persist($theme_translation4);
        $manager->persist($theme_translation5);
        $manager->persist($theme_translation6);

        //Phrases
        $phrase1 = new Phrase();
        $phrase1->setCode('WHERE_IS_GATE')
                 ->setTheme($theme1);

        $phrase2 = new Phrase();
        $phrase2->setCode('I_HAVE_A_TICKET')
                 ->setTheme($theme1);

        $phrase3 = new Phrase();
        $phrase3->setCode('I_WOULD_LIKE_TO_ORDER')
                 ->setTheme($theme2);

        $phrase4 = new Phrase();
        $phrase4->setCode('THE_BILL_PLEASE')
                 ->setTheme($theme2);

        $manager->persist($phrase1);
        $manager->persist($phrase2);
        $manager->persist($phrase3);
        $manager->persist($phrase4);

        // Translations for AIRPORT theme
        $phrase_translation1 = new PhraseTranslation();
        $phrase_translation1->setText('Où est la porte ?')->setPhrase($phrase1)->setLanguage($language1);

        $phrase_translation2 = new PhraseTranslation();
        $phrase_translation2->setText('Where is the gate?')->setPhrase($phrase1)->setLanguage($language2);

        $phrase_translation3 = new PhraseTranslation();
        $phrase_translation3->setText('¿Dónde está la puerta?')->setPhrase($phrase1)->setLanguage($language3);

        $phrase_translation4 = new PhraseTranslation();
        $phrase_translation4->setText("Mon vol est retardé")->setPhrase($phrase2)->setLanguage($language1);

        $phrase_translation5 = new PhraseTranslation();
        $phrase_translation5->setText("My flight is delayed")->setPhrase($phrase2)->setLanguage($language2);

        $phrase_translation6 = new PhraseTranslation();
        $phrase_translation6->setText('Tengo un billete')->setPhrase($phrase2)->setLanguage($language3);

        // Translations for RESTAURANT theme
        $phrase_translation7 = new PhraseTranslation();
        $phrase_translation7->setText("Je voudrais commander")->setPhrase($phrase3)->setLanguage($language1);

        $phrase_translation8 = new PhraseTranslation();
        $phrase_translation8->setText("I would like to order")->setPhrase($phrase3)->setLanguage($language2);

        $phrase_translation9 = new PhraseTranslation();
        $phrase_translation9->setText("Quisiera pedir")->setPhrase($phrase3)->setLanguage($language3);

        $phrase_translation10 = new PhraseTranslation();
        $phrase_translation10->setText("L’addition, s’il vous plaît")->setPhrase($phrase4)->setLanguage($language1);

        $phrase_translation11 = new PhraseTranslation();
        $phrase_translation11->setText("The bill, please")->setPhrase($phrase4)->setLanguage($language2);

        $phrase_translation12 = new PhraseTranslation();
        $phrase_translation12->setText("La cuenta, por favor")->setPhrase($phrase4)->setLanguage($language3);

        $manager->persist($phrase_translation1);
        $manager->persist($phrase_translation2);
        $manager->persist($phrase_translation3);
        $manager->persist($phrase_translation4);
        $manager->persist($phrase_translation5);
        $manager->persist($phrase_translation6);
        $manager->persist($phrase_translation7);
        $manager->persist($phrase_translation8);
        $manager->persist($phrase_translation9);
        $manager->persist($phrase_translation10);
        $manager->persist($phrase_translation11);
        $manager->persist($phrase_translation12);

        //UserPhraseProgress
        $userPhraseProgress1 = new UserPhraseProgress();
        $userPhraseProgress1->setUser($user1)
                            ->setPhraseTranslation($phrase_translation2);

        $userPhraseProgress2 = new UserPhraseProgress();
        $userPhraseProgress2->setUser($user1)
                            ->setPhraseTranslation($phrase_translation11);

        $userPhraseProgress3 = new UserPhraseProgress();
        $userPhraseProgress3->setUser($user2)
                            ->setPhraseTranslation($phrase_translation6);

        $userPhraseProgress4 = new UserPhraseProgress();
        $userPhraseProgress4->setUser($user2)
                            ->setPhraseTranslation($phrase_translation9);

        $userPhraseProgress5 = new UserPhraseProgress();
        $userPhraseProgress5->setUser($user3)
                            ->setPhraseTranslation($phrase_translation3);

        $userPhraseProgress6 = new UserPhraseProgress();
        $userPhraseProgress6->setUser($user3)
                            ->setPhraseTranslation($phrase_translation9);

        $userPhraseProgress7 = new UserPhraseProgress();
        $userPhraseProgress7->setUser($user4)
                            ->setPhraseTranslation($phrase_translation9);

        $manager->persist($userPhraseProgress1);
        $manager->persist($userPhraseProgress2);
        $manager->persist($userPhraseProgress3);
        $manager->persist($userPhraseProgress4);
        $manager->persist($userPhraseProgress5);
        $manager->persist($userPhraseProgress6);
        $manager->persist($userPhraseProgress7);

        // Create a group
        $group2 = new Group();
        $group2->setName('Group 2');
        $group2->setCreatedBy($user2);
        $group2->setTargetLanguage($language3);
        $group2->setInvitationCode('GROUP1234');

        $group1 = new Group();
        $group1->setName('Group 1');
        $group1->setCreatedBy($user1);
        $group1->setTargetLanguage($language2);
        $group1->setInvitationCode('GROUP2345');

        $manager->persist($group1);
        $manager->persist($group2);

        // Create a group membership

        $membership1 = new GroupMembership();
        $membership1->setUser($user1);
        $membership1->setTargetGroup($group1);
        $membership1->setJoinedAt(new \DateTimeImmutable());
        $membership1->setIsAdmin(true);

        $membership2 = new GroupMembership();
        $membership2->setUser($user2);
        $membership2->setTargetGroup($group2);
        $membership2->setJoinedAt(new \DateTimeImmutable());
        $membership2->setIsAdmin(true);

        $membership3 = new GroupMembership();
        $membership3->setUser($user4);
        $membership3->setTargetGroup($group2);
        $membership3->setJoinedAt(new \DateTimeImmutable());
        $membership3->setIsAdmin(true);

        $manager->persist($membership1);
        $manager->persist($membership2);
        $manager->persist($membership3);

        $manager->flush();
    }
}