<?php

namespace App\Entity;

use App\Repository\ResponseQRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use App\Entity\User;
use App\Entity\Question;

#[ORM\Entity(repositoryClass: ResponseQRepository::class)]
class ResponseQ
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;


    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'responses')]
    private ?User $user_id = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'id')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id')]
    #[Groups(['comment:read'])]
    private ?Question $question_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUser(): ?String
    {
        return $this->user_id->__toString();
    }
    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }
public function setQuestionId(?Question $question_id): self
    {
        $this->question_id = $question_id;

        return $this;
    }
    //getter and setter for date
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }
    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

  
}
