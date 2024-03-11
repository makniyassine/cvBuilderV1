<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints as MisdAssert;

#[ORM\HasLifecycleCallbacks()]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers"])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "Le FirstName est obligatoire")]
    #[Assert\Length(min:8, minMessage:"Your password must be at least 8 characters long")]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Groups(["getUsers"])]
    #[Assert\Length(min: 1, max: 15, minMessage: "Le firstName doit faire au moins {{ limit }} caractères",
    maxMessage: "Le firstName ne peut pas faire plus de {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le FirstName est obligatoire")]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(["getUsers"])]
    private ?string $lastName = null;

    #[ORM\Column(length: 20)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide")]
    #[Assert\Regex(
        pattern: "/^\+\d{1,3}\s\d{2}\s\d{3}\s\d{3}$/",
        message: "Please enter a valid phone number in the format '+216 12 456 789'"
    )]
    private ?string $tel = null;

    #[ORM\Column( nullable: true)]
    private ?bool $enabled = null;

    #[ORM\Column( nullable: true)]
    private ?bool $blocked = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(["getUsers"])]
    private ?string $token = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $tokenCreation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $activationCode = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $color = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    #[ORM\OneToMany(targetEntity: AccessTier::class, mappedBy: 'user')]
    private Collection $accessTier;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Language $language = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Person $person = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Person $no = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ResetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $TokenDate = null;

    public function __construct()
    {
        $this->accessTier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): static
    {
        $this->blocked = $blocked;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenCreation(): ?string
    {
        return $this->tokenCreation;
    }

    public function setTokenCreation(?string $tokenCreation): static
    {
        $this->tokenCreation = $tokenCreation;

        return $this;
    }

    public function getActivationCode(): ?string
    {
        return $this->activationCode;
    }

    public function setActivationCode(?string $activationCode): static
    {
        $this->activationCode = $activationCode;

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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, AccessTier>
     */
    public function getAccessTier(): Collection
    {
        return $this->accessTier;
    }

    public function addAccessTier(AccessTier $accessTier): static
    {
        if (!$this->accessTier->contains($accessTier)) {
            $this->accessTier->add($accessTier);
            $accessTier->setUser($this);
        }

        return $this;
    }

    public function removeAccessTier(AccessTier $accessTier): static
    {
        if ($this->accessTier->removeElement($accessTier)) {
            // set the owning side to null (unless already changed)
            if ($accessTier->getUser() === $this) {
                $accessTier->setUser(null);
            }
        }

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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getNo(): ?Person
    {
        return $this->no;
    }

    public function setNo(?Person $no): static
    {
        // unset the owning side of the relation if necessary
        if ($no === null && $this->no !== null) {
            $this->no->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($no !== null && $no->getUser() !== $this) {
            $no->setUser($this);
        }

        
        $this->no = $no;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->ResetToken;
    }

    public function setResetToken(?string $ResetToken): static
    {
        $this->ResetToken = $ResetToken;

        return $this;
    }

    public function getTokenDate(): ?\DateTimeInterface
    {
        return $this->TokenDate;
    }

    public function setTokenDate(?\DateTimeInterface $TokenDate): static
    {
        $this->TokenDate = $TokenDate;

        return $this;
    }

    #[ORM\PrePersist()]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate()]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
