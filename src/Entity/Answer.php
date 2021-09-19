<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use App\Repository\AnswerRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AnswerRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Answer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(
     *              message = "Vous devez fournir une réponse valide."
     * )
     * @Assert\NotNull(
     *              message = "Vous devez fournir une réponse."
     * )
     * @Assert\Regex(
     *              pattern = "/\w/",
     *              match = true,
     *              message = "Vous devez fournir une réponse valide."
     * )
     */
    private $answer;

    /**
     * @ORM\Column(type="integer")
     */
    private $viewsNumber;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Question::class, inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="answer", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="likedAnswers")
     * @JoinTable(name="answers_liked")
     */
    private $likedUsers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="dislikedAnswers")
     * @JoinTable(name="answers_disliked")
     */
    private $dislikedUsers;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likedUsers = new ArrayCollection();
        $this->dislikedUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getViewsNumber(): ?int
    {
        return $this->viewsNumber;
    }

    public function setViewsNumber(int $viewsNumber): self
    {
        $this->viewsNumber = $viewsNumber;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

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
            $comment->setAnswer($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAnswer() === $this) {
                $comment->setAnswer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getLikedUsers(): Collection
    {
        return $this->likedUsers;
    }

    public function addLikedUser(User $likedUser): self
    {
        if (!$this->likedUsers->contains($likedUser)) {
            $this->likedUsers[] = $likedUser;
        }

        return $this;
    }

    public function removeLikedUser(User $likedUser): self
    {
        $this->likedUsers->removeElement($likedUser);

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getDislikedUsers(): Collection
    {
        return $this->dislikedUsers;
    }

    public function addDislikedUser(User $dislikedUser): self
    {
        if (!$this->dislikedUsers->contains($dislikedUser)) {
            $this->dislikedUsers[] = $dislikedUser;
        }

        return $this;
    }

    public function removeDislikedUser(User $dislikedUser): self
    {
        $this->dislikedUsers->removeElement($dislikedUser);

        return $this;
    }
}
