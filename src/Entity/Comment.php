<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Answer::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $answer;

    /**
     * @ORM\OneToMany(targetEntity=SubComment::class, mappedBy="comment", orphanRemoval=true)
     */
    private $subComments;

    public function __construct()
    {
        $this->subComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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

    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    public function setAnswer(?Answer $answer): self
    {
        $this->answer = $answer;

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
            $subComment->setComment($this);
        }

        return $this;
    }

    public function removeSubComment(SubComment $subComment): self
    {
        if ($this->subComments->removeElement($subComment)) {
            // set the owning side to null (unless already changed)
            if ($subComment->getComment() === $this) {
                $subComment->setComment(null);
            }
        }

        return $this;
    }
}
