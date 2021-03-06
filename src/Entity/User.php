<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @Vich\Uploadable()
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements UserInterface, \Serializable
{

    public const Sexe = [
        '1' => 'Man',
        '2' => 'Woman'
    ];

    public const Role = [
        'Super Administrator' => 'ROLE_SUPER_ADMIN',
        'Administrator' => 'ROLE_ADMIN',
        'Moderator' => 'ROLE_MODERATEUR',
        'User' => 'ROLE_USER'
    ];

    public const DEFAULT_ROLE = 'ROLE_USER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotCompromisedPassword()
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email(message="The email '{{ value }}' is not valid email.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z-\séèùçïëÉÈËÏ]+$/")
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z-\séèùçïëÉÈËÏ]+$/")
     */
    private $last_name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sexe;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $edited_at;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_login;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Blog", mappedBy="author")
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogLike", mappedBy="user")
     */
    private $blogLikes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogComment", mappedBy="author")
     */
    private $blogComments;

    /**
     * @ORM\Column(type="boolean")
     */
    private $oauth;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LoginAttempt", mappedBy="user")
     */
    private $loginAttempts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserActivity", mappedBy="user", orphanRemoval=true)
     */
    private $userActivities;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatarFilename;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="user_avatar", fileNameProperty="avatarFilename")
     * @Assert\Image(mimeTypes="image/jpeg", mimeTypesMessage="The file must be in JPG format")
     */
    private $avatarFile;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bannerFilename;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="user_banner", fileNameProperty="bannerFilename")
     * @Assert\Image(mimeTypes="image/jpeg", mimeTypesMessage="The file must be in JPG format")
     */
    private $bannerFile;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PasswordToken", mappedBy="user", cascade={"persist", "remove"})
     */
    private $requestPasswordToken;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ConfirmationToken", mappedBy="user", cascade={"persist", "remove"})
     */
    private $confirmationToken;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\AvatarGenerator", mappedBy="user", cascade={"persist", "remove"})
     */
    private $defaultAvatar;

    public function __construct() {
        $this->author = new ArrayCollection();
        $this->blogLikes = new ArrayCollection();
        $this->blogComments = new ArrayCollection();
        $this->loginAttempts = new ArrayCollection();
        $this->userActivities = new ArrayCollection();
        $this->created_at = new \DateTime();
        $this->edited_at = new \DateTime();
        $this->enabled = false;
        $this->oauth = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getSexe(): ?int
    {
        return $this->sexe;
    }

    public function setSexe(int $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->edited_at;
    }

    public function setEditedAt(?\DateTimeInterface $edited_at): self
    {
        $this->edited_at = $edited_at;

        return $this;
    }

    public function getEnabled(): ?int
    {
        return $this->enabled;
    }

    public function setEnabled(int $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTimeInterface $last_login): self
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function getOauth(): ?bool
    {
        return $this->oauth;
    }

    public function setOauth(bool $oauth): self
    {
        $this->oauth = $oauth;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAvatarFilename(): ?string
    {
        return $this->avatarFilename;
    }

    /**
     * @param string|null $avatarFilename
     * @return User
     */
    public function setAvatarFilename(?string $avatarFilename): User
    {
        $this->avatarFilename = $avatarFilename;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    /**
     * @param File|null $avatarFile
     * @return User
     * @throws \Exception
     */
    public function setAvatarFile(?File $avatarFile): User
    {
        $this->avatarFile = $avatarFile;
        if ($this->avatarFile instanceof UploadedFile) {
            $this->edited_at = new \DateTime();
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBannerFilename(): ?string
    {
        return $this->bannerFilename;
    }

    /**
     * @param string|null $bannerFilename
     * @return User
     */
    public function setBannerFilename(?string $bannerFilename): User
    {
        $this->bannerFilename = $bannerFilename;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getBannerFile(): ?File
    {
        return $this->bannerFile;
    }

    /**
     * @param File|null $bannerFile
     * @return User
     * @throws \Exception
     */
    public function setBannerFile(?File $bannerFile): User
    {
        $this->bannerFile = $bannerFile;
        if ($this->bannerFile instanceof UploadedFile) {
            $this->edited_at = new \DateTime();
        }
        return $this;
    }

    /**
     * @return Collection|Blog[]
     */
    public function getAuthor(): Collection
    {
        return $this->author;
    }

    public function addAuthor(Blog $author): self
    {
        if (!$this->author->contains($author)) {
            $this->author[] = $author;
            $author->setAuthor($this);
        }

        return $this;
    }

    public function removeAuthor(Blog $author): self
    {
        if ($this->author->contains($author)) {
            $this->author->removeElement($author);
            // set the owning side to null (unless already changed)
            if ($author->getAuthor() === $this) {
                $author->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BlogLike[]
     */
    public function getBlogLikes(): Collection
    {
        return $this->blogLikes;
    }

    public function addBlogLike(BlogLike $blogLike): self
    {
        if (!$this->blogLikes->contains($blogLike)) {
            $this->blogLikes[] = $blogLike;
            $blogLike->setUser($this);
        }

        return $this;
    }

    public function removeBlogLike(BlogLike $blogLike): self
    {
        if ($this->blogLikes->contains($blogLike)) {
            $this->blogLikes->removeElement($blogLike);
            // set the owning side to null (unless already changed)
            if ($blogLike->getUser() === $this) {
                $blogLike->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BlogComment[]
     */
    public function getBlogComments(): Collection
    {
        return $this->blogComments;
    }

    public function addBlogComment(BlogComment $blogComment): self
    {
        if (!$this->blogComments->contains($blogComment)) {
            $this->blogComments[] = $blogComment;
            $blogComment->setAuthor($this);
        }

        return $this;
    }

    public function removeBlogComment(BlogComment $blogComment): self
    {
        if ($this->blogComments->contains($blogComment)) {
            $this->blogComments->removeElement($blogComment);
            // set the owning side to null (unless already changed)
            if ($blogComment->getAuthor() === $this) {
                $blogComment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LoginAttempt[]
     */
    public function getLoginAttempts(): Collection
    {
        return $this->loginAttempts;
    }

    public function addLoginAttempt(LoginAttempt $loginAttempt): self
    {
        if (!$this->loginAttempts->contains($loginAttempt)) {
            $this->loginAttempts[] = $loginAttempt;
            $loginAttempt->setUser($this);
        }

        return $this;
    }

    public function removeLoginAttempt(LoginAttempt $loginAttempt): self
    {
        if ($this->loginAttempts->contains($loginAttempt)) {
            $this->loginAttempts->removeElement($loginAttempt);
            // set the owning side to null (unless already changed)
            if ($loginAttempt->getUser() === $this) {
                $loginAttempt->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserActivity[]
     */
    public function getUserActivities(): Collection
    {
        return $this->userActivities;
    }

    public function addUserActivity(UserActivity $userActivity): self
    {
        if (!$this->userActivities->contains($userActivity)) {
            $this->userActivities[] = $userActivity;
            $userActivity->setUser($this);
        }

        return $this;
    }

    public function removeUserActivity(UserActivity $userActivity): self
    {
        if ($this->userActivities->contains($userActivity)) {
            $this->userActivities->removeElement($userActivity);
            // set the owning side to null (unless already changed)
            if ($userActivity->getUser() === $this) {
                $userActivity->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt() {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() {
    }

    /**
     * @return string
     */
    public function serialize() {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->roles,
            $this->email,
            $this->birthday,
            $this->first_name,
            $this->last_name,
            $this->sexe,
            $this->created_at,
            $this->edited_at,
            $this->enabled
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized) {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->roles,
            $this->email,
            $this->birthday,
            $this->first_name,
            $this->last_name,
            $this->sexe,
            $this->created_at,
            $this->edited_at,
            $this->enabled) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function __toString() {
        return (string)$this->username;
    }

    public function getDefaultAvatar(): ?AvatarGenerator
    {
        return $this->defaultAvatar;
    }

    public function setDefaultAvatar(AvatarGenerator $defaultAvatar): self
    {
        $this->defaultAvatar = $defaultAvatar;

        // set the owning side of the relation if necessary
        if ($defaultAvatar->getUser() !== $this) {
            $defaultAvatar->setUser($this);
        }

        return $this;
    }
}