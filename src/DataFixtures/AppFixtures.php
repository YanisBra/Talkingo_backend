<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Language;
use App\Entity\Theme;
use App\Entity\ThemeTranslation;
use App\Entity\Phrase;
use App\Entity\PhraseTranslation;
use UserPhraseProgress;

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

        $manager->persist($language1);
        $manager->persist($language2);

        //Users
        $user = new User();
        $user   ->setEmail('user@user.fr')
                ->setName('user')
                ->setInterfaceLanguage($language1)
                ->setTargetLanguage($language2)
                ->setPassword($this->passwordHasher->hashPassword($user, 'pass123'));

        $user2 = new User();
        $user2  ->setEmail('admin@admin.fr')
                ->setName('admin')
                ->setRoles(['ROLE_ADMIN'])
                ->setInterfaceLanguage($language1)
                ->setTargetLanguage($language2)
                ->setPassword($this->passwordHasher->hashPassword($user2, 'pass123'));

        $manager->persist($user);
        $manager->persist($user2);

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
        $theme_translation3 ->setLabel('Restaurant')
                            ->setTheme($theme2)
                            ->setLanguage($language1);  

        $theme_translation4 = new ThemeTranslation();
        $theme_translation4 ->setLabel('Restaurant')
                            ->setTheme($theme2)
                            ->setLanguage($language2);  

        $manager->persist($theme_translation1);
        $manager->persist($theme_translation2);
        $manager->persist($theme_translation3);
        $manager->persist($theme_translation4);

        //Phrases
        $phrase1 = new Phrase();
        $phrase1->setCode('HELLO')
                 ->setTheme($theme1);

        $phrase2 = new Phrase();
        $phrase2->setCode('GOODBYE')
                 ->setTheme($theme1);

        $phrase3 = new Phrase();
        $phrase3->setCode('THANK_YOU')
                 ->setTheme($theme2);

        $phrase4 = new Phrase();
        $phrase4->setCode('PLEASE')
                 ->setTheme($theme2);

        $manager->persist($phrase1);
        $manager->persist($phrase2);
        $manager->persist($phrase3);
        $manager->persist($phrase4);

        $phrase_translation1 = new PhraseTranslation();
        $phrase_translation1->setLabel('Bonjour')
                            ->setPhrase($phrase1)
                            ->setLanguage($language1);

        $phrase_translation2 = new PhraseTranslation();
        $phrase_translation2->setLabel('Hello')
                            ->setPhrase($phrase1)
                            ->setLanguage($language2);

        $phrase_translation3 = new PhraseTranslation();
        $phrase_translation3->setLabel('Au revoir')
                            ->setPhrase($phrase2)
                            ->setLanguage($language1);

        $phrase_translation4 = new PhraseTranslation();
        $phrase_translation4->setLabel('Goodbye')
                            ->setPhrase($phrase2)
                            ->setLanguage($language2);

        $phrase_translation5 = new PhraseTranslation();
        $phrase_translation5->setLabel('Merci')
                            ->setPhrase($phrase3)
                            ->setLanguage($language1);

        $phrase_translation6 = new PhraseTranslation();
        $phrase_translation6->setLabel('Thank you')
                            ->setPhrase($phrase3)
                            ->setLanguage($language2);

        $phrase_translation7 = new PhraseTranslation();
        $phrase_translation7->setLabel('S’il vous plaît')
                            ->setPhrase($phrase4)
                            ->setLanguage($language1);

        $phrase_translation8 = new PhraseTranslation();
        $phrase_translation8->setLabel('Please')
                            ->setPhrase($phrase4)
                            ->setLanguage($language2);

        $manager->persist($phrase_translation1);
        $manager->persist($phrase_translation2);
        $manager->persist($phrase_translation3);
        $manager->persist($phrase_translation4);
        $manager->persist($phrase_translation5);
        $manager->persist($phrase_translation6);
        $manager->persist($phrase_translation7);
        $manager->persist($phrase_translation8);

        //UserPhraseProgress
        $userPhraseProgress1 = new UserPhraseProgress();
        $userPhraseProgress1->setUser($user)
                            ->setPhraseTranslation($phrase_translation1);

        $userPhraseProgress2 = new UserPhraseProgress();
        $userPhraseProgress2->setUser($user)
                            ->setPhraseTranslation($phrase_translation3);

        $userPhraseProgress3 = new UserPhraseProgress();
        $userPhraseProgress3->setUser($user2)
                            ->setPhraseTranslation($phrase_translation2);

        $userPhraseProgress4 = new UserPhraseProgress();
        $userPhraseProgress4->setUser($user2)
                            ->setPhraseTranslation($phrase_translation4);

        $manager->persist($userPhraseProgress1);
        $manager->persist($userPhraseProgress2);
        $manager->persist($userPhraseProgress3);
        $manager->persist($userPhraseProgress4);

        $manager->flush();
    }
}