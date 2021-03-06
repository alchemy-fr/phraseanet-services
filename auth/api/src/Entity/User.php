<?php

declare(strict_types=1);

namespace App\Entity;

use Arthem\Bundle\LocaleBundle\Model\UserLocaleInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="`user`")
 * @ORM\EntityListeners({"App\Doctrine\Listener\UserDeleteListener"})
 */
class User implements UserInterface, UserLocaleInterface, EquatableInterface
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $emailVerified = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected ?bool $enabled = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $securityToken = null;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $salt;

    /**
     * @ORM\Column(type="json_array")
     */
    protected array $roles = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $password = null;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected ?string $locale = null;

    protected ?string $plainPassword = null;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastInviteAt;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * Not mapped.
     */
    protected bool $inviteByEmail = false;

    /**
     * @var Group[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     */
    protected $groups;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->groups = new ArrayCollection();
    }

    public function getId(): string
    {
        if (null === $this->id) {
            return '';
        }

        return $this->id->__toString();
    }

    public function setId($id): void
    {
        if ($id instanceof Uuid) {
            $this->id = $id;
        } else {
            $this->id = Uuid::fromString($id);
        }
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->getUsername();
    }

    public function setEmail(string $email): void
    {
        $this->setUsername($email);
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getGroupRoles(): array
    {
        $roles = [];
        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        return array_unique($roles);
    }

    public function getRoles(): array
    {
        return array_unique(array_merge($this->getUserRoles(), $this->getGroupRoles()));
    }

    public function getUserRoles(): array
    {
        if (!in_array('ROLE_USER', $this->roles)) {
            $this->roles[] = 'ROLE_USER';
        }

        return $this->roles;
    }

    public function setUserRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function hasPassword(): bool
    {
        return null !== $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getSecurityToken(): ?string
    {
        return $this->securityToken;
    }

    public function setSecurityToken(?string $securityToken): void
    {
        $this->securityToken = $securityToken;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): void
    {
        $this->emailVerified = $emailVerified;
    }

    public function isInviteByEmail(): bool
    {
        return $this->inviteByEmail;
    }

    public function setInviteByEmail(bool $inviteByEmail): void
    {
        $this->inviteByEmail = $inviteByEmail;
    }

    public function getLastInviteAt(): DateTime
    {
        return $this->lastInviteAt;
    }

    public function setLastInviteAt(DateTime $lastInviteAt): void
    {
        $this->lastInviteAt = $lastInviteAt;
    }

    public function canBeInvited(int $allowedDelay): bool
    {
        return null === $this->lastInviteAt
            || $this->lastInviteAt->getTimestamp() < time() - $allowedDelay;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * Return groups indexed by id.
     */
    public function getIndexedGroups(): array
    {
        $groups = [];
        foreach ($this->getGroups() as $group) {
            $groups[$group->getId()] = $group->getName();
        }

        return $groups;
    }

    public function addGroup(Group $group): void
    {
        $group->addUser($this);
        $this->groups->add($group);
    }

    public function removeGroup(Group $group): void
    {
        $group->removeUser($this);
        $this->groups->removeElement($group);
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User
            || $this->getId() !== $user->getId()) {
            return false;
        }

        return count($this->getRoles()) === count($user->getRoles()) && empty(array_diff($this->getRoles(), $user->getRoles()));
    }
}
