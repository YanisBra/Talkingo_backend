<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\{Post, Get, Put, Patch, Delete, GetCollection};
use App\Repository\ThemeTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;


#[ApiFilter(SearchFilter::class, properties: ['theme' => 'exact'])]
#[ApiResource(
    normalizationContext: ['groups' => ['theme_translation:read']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
#[ORM\Entity(repositoryClass: ThemeTranslationRepository::class)]
class ThemeTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['theme_translation:read'])]
    private ?int $id = null;
    

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Label is required.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Label cannot be longer than {{ limit }} characters."
    )]
    #[Groups(['theme_translation:read'])]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'themeTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['theme_translation:read'])]
    private ?Theme $theme = null;

    #[ORM\ManyToOne(inversedBy: 'themeTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['theme_translation:read'])]
    private ?Language $language = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }
}
