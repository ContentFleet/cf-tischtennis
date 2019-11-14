<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TableTennisStatsRepository")
 */
class TableTennisStats
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eloRating = 1500;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbWon;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbLost;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="tableTennisStats", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEloRating(): ?int
    {
        return $this->eloRating;
    }

    public function setEloRating(?int $eloRating): self
    {
        $this->eloRating = $eloRating;

        return $this;
    }

    public function getNbWon(): ?int
    {
        return $this->nbWon?: 0;
    }

    public function setNbWon(?int $nbWon): self
    {
        $this->nbWon = $nbWon;

        return $this;
    }

    public function getNbLost(): ?int
    {
        return $this->nbLost?: 0;
    }

    public function setNbLost(?int $nbLost): self
    {
        $this->nbLost = $nbLost;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
