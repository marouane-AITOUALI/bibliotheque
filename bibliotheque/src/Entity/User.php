<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function setIsBanned(bool $isBanned): self 
    {
        if ($isBanned) {
            // Si l'utilisateur a le rôle ROLE_USER, on le remplace par ROLE_BANNED
            if (in_array('ROLE_USER', $this->roles, true)) {
                $this->roles = array_map(fn($role) => $role === 'ROLE_USER' ? 'ROLE_BANNED' : $role, $this->roles);
            } else {
                // Si l'utilisateur n'a pas ROLE_USER, on ajoute ROLE_BANNED
                if (!in_array('ROLE_BANNED', $this->roles, true)) {
                    $this->roles[] = 'ROLE_BANNED';
                }
            }
        } else {
            // Si on débannit l'utilisateur, on retire ROLE_BANNED
            $this->roles = array_filter($this->roles, fn($role) => $role !== 'ROLE_BANNED');

            // Si l'utilisateur n'a pas ROLE_USER, on ajoute ROLE_USER
            if (!in_array('ROLE_USER', $this->roles, true)) {
                $this->roles[] = 'ROLE_USER';
            }
        }

        return $this;
    }

    public function getIsBanned(): bool
    {
        return in_array('ROLE_BANNED', $this->roles, true);
    }
}
