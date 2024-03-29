<?php

namespace App\Entity;

use App\Repository\TierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TierRepository::class)]
class Tier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getTiers"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getTiers"])]
    #[Assert\Length(min: 1, max: 15, minMessage: "Le nom doit faire au moins {{ limit }} caractères",
    maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    private ?string $raisonSociale = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getTiers"])]
    #[Assert\Url]
    private ?string $siteWeb = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getTiers"])]
    #[Assert\NotBlank]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.'
    )]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide")]
    #[Assert\Regex(
        pattern: "/^\+\d{1,3}\s\d{2}\s\d{3}\s\d{3}$/",
        message: "Please enter a valid phone number in the format '+216 12 456 789'"
    )]
    private ?string $tel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationality = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $town = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column( nullable: true)]
    private ?bool $active = null;

    #[ORM\Column]
    #[Groups(["getTiers"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(["getTiers"])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: AccessTier::class, mappedBy: 'tier')]
    private Collection $accesstier;

    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'tier')]
    private Collection $person;

    public function __construct()
    {
        $this->accesstier = new ArrayCollection();
        $this->person = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): static
    {
        $this->town = $town;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, AccessTier>
     */
    public function getAccesstier(): Collection
    {
        return $this->accesstier;
    }

    public function addAccesstier(AccessTier $accesstier): static
    {
        if (!$this->accesstier->contains($accesstier)) {
            $this->accesstier->add($accesstier);
            $accesstier->setTier($this);
        }

        return $this;
    }

    public function removeAccesstier(AccessTier $accesstier): static
    {
        if ($this->accesstier->removeElement($accesstier)) {
            // set the owning side to null (unless already changed)
            if ($accesstier->getTier() === $this) {
                $accesstier->setTier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPerson(): Collection
    {
        return $this->person;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->person->contains($person)) {
            $this->person->add($person);
            $person->setTier($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->person->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getTier() === $this) {
                $person->setTier(null);
            }
        }

        return $this;
    }
}
