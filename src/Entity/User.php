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
     * @ORM\OneToMany(targetEntity="App\Entity\TableTennisGame", mappedBy="winnerUser")
     */
    private $tableTennisWonGames;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BilliardGame", mappedBy="winnerUser")
     */
    private $billiardWonGames;

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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EloHistory", mappedBy="user")
     */
    private $eloHistories;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TableTennisStats", mappedBy="user", cascade={"persist", "remove"})
     */
    private $tableTennisStats;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\TableTennisGame", mappedBy="users")
     */
    private $tableTennisGames;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\BilliardStats", mappedBy="user", cascade={"persist", "remove"})
     */
    private $billiardStats;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BilliardGame", mappedBy="users")
     */
    private $billiardGames;

    public function __construct()
    {
        parent::__construct();
        $this->games = new ArrayCollection();
        $this->wonGames = new ArrayCollection();
        $this->eloHistories = new ArrayCollection();
        $this->tableTennisGames = new ArrayCollection();
        $this->billiardGames = new ArrayCollection();
        $this->tableTennisWonGames = new ArrayCollection();
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
            return ucfirst($this->getFirstname()) . " " . ucfirst($this->getLastName());
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

    /**
     * @return Collection|EloHistory[]
     */
    public function getEloHistories(): Collection
    {
        return $this->eloHistories;
    }

    public function addEloHistory(EloHistory $eloHistory): self
    {
        if (!$this->eloHistories->contains($eloHistory)) {
            $this->eloHistories[] = $eloHistory;
            $eloHistory->setUser($this);
        }

        return $this;
    }

    public function removeEloHistory(EloHistory $eloHistory): self
    {
        if ($this->eloHistories->contains($eloHistory)) {
            $this->eloHistories->removeElement($eloHistory);
            // set the owning side to null (unless already changed)
            if ($eloHistory->getUser() === $this) {
                $eloHistory->setUser(null);
            }
        }

        return $this;
    }

    public function getTableTennisStats(): ?TableTennisStats
    {
        if ($this->tableTennisStats) {
            return $this->tableTennisStats;
        }

        $this->setTableTennisStats(new TableTennisStats());
        return $this->tableTennisStats;
    }

    public function setTableTennisStats(TableTennisStats $tableTennisStats): self
    {
        $this->tableTennisStats = $tableTennisStats;

        // set the owning side of the relation if necessary
        if ($this !== $tableTennisStats->getUser()) {
            $tableTennisStats->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|TableTennisGame[]
     */
    public function getTableTennisGames(): Collection
    {
        return $this->tableTennisGames;
    }

    public function addTableTennisGame(TableTennisGame $tableTennisGame): self
    {
        if (!$this->tableTennisGames->contains($tableTennisGame)) {
            $this->tableTennisGames[] = $tableTennisGame;
            $tableTennisGame->addWinnerUser($this);
        }

        return $this;
    }

    public function removeTableTennisGame(TableTennisGame $tableTennisGame): self
    {
        if ($this->tableTennisGames->contains($tableTennisGame)) {
            $this->tableTennisGames->removeElement($tableTennisGame);
            $tableTennisGame->removeWinnerUser($this);
        }

        return $this;
    }

    public function getBilliardStats(): ?BilliardStats
    {
        if($this->billiardStats){
            return $this->billiardStats;
        }

        $this->setBilliardStats(new BilliardStats());
        return $this->billiardStats;
    }

    public function setBilliardStats(BilliardStats $billiardStats): self
    {
        $this->billiardStats = $billiardStats;

        // set the owning side of the relation if necessary
        if ($this !== $billiardStats->getUser()) {
            $billiardStats->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|BilliardGame[]
     */
    public function getBilliardGames(): Collection
    {
        return $this->billiardGames;
    }

    public function addBilliardGame(BilliardGame $billiardGame): self
    {
        if (!$this->billiardGames->contains($billiardGame)) {
            $this->billiardGames[] = $billiardGame;
            $billiardGame->addUser($this);
        }

        return $this;
    }

    public function removeBilliardGame(BilliardGame $billiardGame): self
    {
        if ($this->billiardGames->contains($billiardGame)) {
            $this->billiardGames->removeElement($billiardGame);
            $billiardGame->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|TableTennisGame[]
     */
    public function getTableTennisWonGames(): Collection
    {
        return $this->tableTennisWonGames;
    }

    public function addTableTennisWonGame(TableTennisGame $tableTennisWonGame): self
    {
        if (!$this->tableTennisWonGames->contains($tableTennisWonGame)) {
            $this->tableTennisWonGames[] = $tableTennisWonGame;
            $tableTennisWonGame->setWinnerUser($this);
        }

        return $this;
    }

    public function removeTableTennisWonGame(TableTennisGame $tableTennisWonGame): self
    {
        if ($this->tableTennisWonGames->contains($tableTennisWonGame)) {
            $this->tableTennisWonGames->removeElement($tableTennisWonGame);
            // set the owning side to null (unless already changed)
            if ($tableTennisWonGame->getWinnerUser() === $this) {
                $tableTennisWonGame->setWinnerUser(null);
            }
        }

        return $this;
    }
}
