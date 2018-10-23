<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Please enter your firstname.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max=255,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     *
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $compagny;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Game", mappedBy="users")
     */
    private $games;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="winnerUser")
     */
    private $wonGames;

    /**
     * @ORM\Column(type="integer")
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

    public function __construct()
    {
        parent::__construct();
        $this->games = new ArrayCollection();
        $this->wonGames = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getCompagny(): ?string
    {
        return $this->compagny;
    }

    public function setCompagny(?string $compagny): self
    {
        $this->Compagny = $compagny;

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->addUser($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->contains($game)) {
            $this->games->removeElement($game);
            $game->removeUser($this);
        }

        return $this;
    }

    public function getDisplayName(): string {
            return $this->getFirstname() . " " . $this->getLastName();
    }

    /**
     * @return Collection|Game[]
     */
    public function getWonGames(): Collection
    {
        return $this->wonGames;
    }

    public function addWonGame(Game $wonGame): self
    {
        if (!$this->wonGames->contains($wonGame)) {
            $this->wonGames[] = $wonGame;
            $wonGame->setWinnerUser($this);
        }

        return $this;
    }

    public function removeWonGame(Game $wonGame): self
    {
        if ($this->wonGames->contains($wonGame)) {
            $this->wonGames->removeElement($wonGame);
            // set the owning side to null (unless already changed)
            if ($wonGame->getWinnerUser() === $this) {
                $wonGame->setWinnerUser(null);
            }
        }

        return $this;
    }

    public function getEloRating(): ?int
    {
        return $this->eloRating;
    }

    public function setEloRating(int $eloRating): self
    {
        $this->eloRating = $eloRating;

        return $this;
    }

    public function getNbWon(): ?int
    {
        return $this->nbWon ? $this->nbWon : 0;
    }

    public function setNbWon(?int $nbWon): self
    {
        $this->nbWon = $nbWon;

        return $this;
    }

    public function getNbLost(): ?int
    {
        return $this->nbLost ? $this->nbLost : 0;
    }

    public function setNbLost(?int $nbLost): self
    {
        $this->nbLost = $nbLost;

        return $this;
    }

    public function hasWon()
    {
        $this->nbWon++;
    }

    public function hasLost()
    {
        $this->nbLost++;
    }
}
