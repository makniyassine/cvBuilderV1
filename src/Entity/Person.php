<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPersons"])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(["getPersons"])]
    #[Assert\Length(min: 1, max: 15, minMessage: "Le firstName doit faire au moins {{ limit }} caractères",
    maxMessage: "Le firstName ne peut pas faire plus de {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le FirstName est obligatoire")]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(["getPersons"])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPersons"])]
    #[Assert\NotBlank]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
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

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Cv::class, mappedBy: 'person')]
    private Collection $cv;

    #[ORM\OneToOne(inversedBy: 'no', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'person')]
    private ?Tier $tier = null;

    public function __construct()
    {
        $this->cv = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

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
     * @return Collection<int, Cv>
     */
    public function getCv(): Collection
    {
        return $this->cv;
    }

    public function addCv(Cv $cv): static
    {
        if (!$this->cv->contains($cv)) {
            $this->cv->add($cv);
            $cv->setPerson($this);
        }

        return $this;
    }

    public function removeCv(Cv $cv): static
    {
        if ($this->cv->removeElement($cv)) {
            // set the owning side to null (unless already changed)
            if ($cv->getPerson() === $this) {
                $cv->setPerson(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTier(): ?Tier
    {
        return $this->tier;
    }

    public function setTier(?Tier $tier): static
    {
        $this->tier = $tier;

        return $this;
    }
}
