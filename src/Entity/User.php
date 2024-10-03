<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\bridge\Doctrine\Types\UuidType;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Merci de renseigner votre email.')]
    #[Assert\Email(message: 'Merci de renseigner une adresse email valide.')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\NotCompromisedPassword(
        message: 'Votre mot de passe a déjà été compromis. Veuillez en choisir un autre.'
    )]
    #[Assert\PasswordStrength([
        'minScore' => Assert\PasswordStrength::STRENGTH_MEDIUM,
        'message' => 'Votre mot de passe doit être plus fort.',
    ])]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?InfoUser $infoUser = null;

    #[ORM\Column(nullable: true)]
    private ?bool $apiAccess = null;

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
     *
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
    public function getPassword(): ?string
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

    public function getInfoUser(): ?InfoUser
    {
        return $this->infoUser;
    }

    public function setInfoUser(?InfoUser $infoUser): static
    {
        // unset the owning side of the relation if necessary
        if ($infoUser === null && $this->infoUser !== null) {
            $this->infoUser->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($infoUser !== null && $infoUser->getUser() !== $this) {
            $infoUser->setUser($this);
        }

        $this->infoUser = $infoUser;

        return $this;
    }

    public function isApiAccess(): ?bool
    {
        return $this->apiAccess;
    }

    public function setApiAccess(?bool $apiAccess): static
    {
        $this->apiAccess = $apiAccess;

        return $this;
    }
}
