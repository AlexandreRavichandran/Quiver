<?php

namespace App\Entity;

use Serializable;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class User implements Serializable, UserInterface, PasswordAuthenticatedUserInterface
{

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,

        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->password,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudonym;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="author", orphanRemoval=true)
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="author", orphanRemoval=true)
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="author", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=SubComment::class, mappedBy="author", orphanRemoval=true)
     */
    private $subComments;

    /**
     * @ORM\ManyToMany(targetEntity=Answer::class, mappedBy="likedUsers")
     * @JoinTable(name="answers_liked")
     */
    private $likedAnswers;

    /**
     * @ORM\ManyToMany(targetEntity=Answer::class, mappedBy="dislikedUsers")
     * @JoinTable(name="answers_disliked")
     */
    private $dislikedAnswers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="subscriber")
     * @JoinTable(name="user_user")
     */
    private $subscription;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="subscription")
     * @JoinTable(name="user_user")
     */
    private $subscriber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $qualification;

    /**
     * @ORM\ManyToMany(targetEntity=Space::class, mappedBy="subscribers")
     */
    private $subscribedSpaces;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $imageName;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->subComments = new ArrayCollection();
        $this->likedAnswers = new ArrayCollection();
        $this->dislikedAnswers = new ArrayCollection();
        $this->subscription = new ArrayCollection();
        $this->subscriber = new ArrayCollection();
        $this->subscribedSpaces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudonym(): ?string
    {
        return $this->pseudonym;
    }

    public function setPseudonym(string $pseudonym): self
    {
        $this->pseudonym = $pseudonym;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setAuthor($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getAuthor() === $this) {
                $question->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setAuthor($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getAuthor() === $this) {
                $answer->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SubComment[]
     */
    public function getSubComments(): Collection
    {
        return $this->subComments;
    }

    public function addSubComment(SubComment $subComment): self
    {
        if (!$this->subComments->contains($subComment)) {
            $this->subComments[] = $subComment;
            $subComment->setAuthor($this);
        }

        return $this;
    }

    public function removeSubComment(SubComment $subComment): self
    {
        if ($this->subComments->removeElement($subComment)) {
            // set the owning side to null (unless already changed)
            if ($subComment->getAuthor() === $this) {
                $subComment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getLikedAnswers(): Collection
    {
        return $this->likedAnswers;
    }

    public function addLikedAnswers(Answer $likedAnswers): self
    {
        if (!$this->likedAnswers->contains($likedAnswers)) {
            $this->likedAnswers[] = $likedAnswers;
            $likedAnswers->addLikedUser($this);
        }

        return $this;
    }

    public function removeLikedAnswers(Answer $likedAnswers): self
    {
        if ($this->likedAnswers->removeElement($likedAnswers)) {
            $likedAnswers->removeLikedUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getDislikedAnswers(): Collection
    {
        return $this->dislikedAnswers;
    }

    public function addDislikedUser(Answer $dislikedAnswers): self
    {
        if (!$this->dislikedAnswers->contains($dislikedAnswers)) {
            $this->dislikedAnswers[] = $dislikedAnswers;
            $dislikedAnswers->addDislikedUser($this);
        }

        return $this;
    }

    public function removeDislikedAnswer(Answer $dislikedAnswers): self
    {
        if ($this->dislikedAnswers->removeElement($dislikedAnswers)) {
            $dislikedAnswers->removeDislikedUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscription;
    }

    public function addSubscription(self $subscription): self
    {
        if (!$this->subscription->contains($subscription)) {
            $this->subscription[] = $subscription;
        }

        return $this;
    }

    public function removeSubscription(self $subscription): self
    {
        $this->subscription->removeElement($subscription);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getSubscribers(): Collection
    {
        return $this->subscriber;
    }

    public function addSubscriber(self $subscriber): self
    {
        if (!$this->subscriber->contains($subscriber)) {
            $this->subscriber[] = $subscriber;
            $subscriber->addSubscription($this);
        }

        return $this;
    }

    public function removeSubscriber(self $subscriber): self
    {
        if ($this->subscriber->removeElement($subscriber)) {
            $subscriber->removeSubscription($this);
        }

        return $this;
    }

    public function isSubscribedTo(self $subscription): bool
    {
        $isSubscribed = $subscription->getSubscribers()->contains($this);

        return $isSubscribed;
    }

    public function getQualification(): ?string
    {
        return $this->qualification;
    }

    public function setQualification(?string $qualification): self
    {
        $this->qualification = $qualification;

        return $this;
    }

    /**
     * @return Collection|Space[]
     */
    public function getSubscribedSpaces(): Collection
    {
        return $this->subscribedSpaces;
    }

    public function addSubscribedSpace(Space $subscribedSpaces): self
    {
        if (!$this->subscribedSpaces->contains($subscribedSpaces)) {
            $this->subscribedSpaces[] = $subscribedSpaces;
            $subscribedSpaces->addSubscriber($this);
        }

        return $this;
    }

    public function removeSubscribedSpace(Space $subscribedSpaces): self
    {
        if ($this->subscribedSpaces->removeElement($subscribedSpaces)) {
            $subscribedSpaces->removeSubscriber($this);
        }

        return $this;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }
}
