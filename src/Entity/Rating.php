<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RatingRepository::class)
 */
class Rating
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Songs::class, inversedBy="ratings")
     * @ORM\JoinColumn(nullable=false)
     * 
     */
    private $song;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ratings")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\JoinColumn(name="song_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $rate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSong(): ?Songs
    {
        return $this->song;
    }

    public function setSong(?Songs $song): self
    {
        $this->song = $song;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }
}
