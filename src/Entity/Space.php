<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SpaceRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SpaceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class Space
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(
     *              message = "Vous devez fournir un nom d'espace valide."
     * )
     * @Assert\NotNull(
     *              message = "Vous devez fournir un nom d'espace."
     * )
     * @Assert\Regex(
     *              pattern="/\w/",
     *              match=true,
     *              message="Vous devez fournir un nom d'espace valide."
     * )
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Question::class, mappedBy="space")
     */
    private $questions;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(
     *              message = "Vous devez fournir une description valide de l'espace."
     * )
     * @Assert\NotNull(
     *              message = "Vous devez fournir une description de l'espace."
     * )
     * @Assert\Regex(
     *              pattern = "/\w/",
     *              match = true,
     *              message = "Vous devez fournir une description valide de l'espace."
     * )
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="subscribedSpaces")
     */
    private $subscribers;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->subscribers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $imageName;

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
            $question->addSpace($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            $question->removeSpace($this);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getSubscribers(): Collection
    {
        return $this->subscribers;
    }

    public function addSubscriber(User $subscriber): self
    {
        if (!$this->subscribers->contains($subscriber)) {
            $this->subscribers[] = $subscriber;
        }

        return $this;
    }

    public function removeSubscriber(User $subscriber): self
    {
        $this->subscribers->removeElement($subscriber);

        return $this;
    }

    public function hasSubscriber(User $user): bool
    {
        $isSubscribed = $this->getSubscribers()->contains($user);
        return $isSubscribed;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }
}
