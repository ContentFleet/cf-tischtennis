<?php

namespace App\Controller;

use App\Entity\BilliardStats;
use App\Entity\Game;
use App\Entity\TableTennisStats;
use App\Entity\User;
use App\Repository\BilliardStatsRepository;
use App\Repository\GameRepository;
use App\Repository\TableTennisStatsRepository;
use App\Repository\UserRepository;
use Chovanec\Rating\Rating;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    public function index()
    {
        /** @var TableTennisStatsRepository $tableTennisStatsRepository */
        $tableTennisStatsRepository = $this->getDoctrine()->getRepository(TableTennisStats::class);
        /** @var BilliardStatsRepository $billiardStatsRepository */
        $billiardStatsRepository = $this->getDoctrine()->getRepository(BilliardStats::class);

        $data = [];
        $data['usersTableTennis'] = $tableTennisStatsRepository->getUserRanking(10);
        $data['usersBilliard'] = $billiardStatsRepository->getUserRanking(10);

        return $this->render('landing_page/index.html.twig', $data);
    }
}
