<?php

namespace App\Controller;

use App\Entity\EloHistory;
use App\Entity\Game;
use App\Entity\TableTennisEloHistory;
use App\Entity\TableTennisGame;
use App\Entity\TableTennisStats;
use App\Entity\User;
use App\Form\GameType;
use App\Repository\EloHistoryRepository;
use App\Repository\GameRepository;
use App\Repository\TableTennisEloHistoryRepository;
use App\Repository\TableTennisGameRepository;
use App\Repository\TableTennisStatsRepository;
use App\Repository\UserRepository;
use App\Service\Slack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class TableTennisController extends AbstractController
{
    /**
     * @Route("/tabletennis", name="tabletennis_home_page")
     */
    public function index()
    {
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->getDoctrine()->getRepository(TableTennisGame::class);
        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository(User::class);


        /** @var TableTennisStatsRepository $tableTennisStatsRepository */
        $tableTennisStatsRepository = $this->getDoctrine()->getRepository(TableTennisStats::class);

        $data = [];
        $data['usersTableTennis'] = $tableTennisStatsRepository->getUserRanking(150);
        $games = $gameRepository->findBy(array(), array('id' => 'DESC'), 5);
        $data['games'] = $games;
        return $this->render('tabletennis/index.html.twig', $data);
    }

    /**
     * @Route("tabletennis/rules", name="tabletennis_rules")
     */
    public function rules()
    {
        $data = [];
        return $this->render('tabletennis/rules.html.twig', $data);
    }

    /**
     * @Route("/tabletennis/games", name="tabletennis_game_index", methods="GET")
     * @param TableTennisGameRepository $gameRepository
     * @return Response
     */
    public function gameIndex(TableTennisGameRepository $gameRepository): Response
    {
        return $this->render('tabletennis/games.html.twig', ['games' => $gameRepository->findBy(array(), array('id' => 'DESC'), 100)]);
    }

    /**
     * @Route("tabletennis/new", name="tabletennis_game_new", methods="GET|POST")
     * @param Request $request
     * @param Slack $slackService
     * @return Response
     */
    public function new(Request $request, Slack $slackService): Response
    {
        $em = $this->getDoctrine()->getManager();
        $game = new TableTennisGame();
        $currentUser = $this->getUser();
        $form = $this->createForm(
            GameType::class,
            $game,
            [
                'current_user_id' => $currentUser->getId()
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game->addUser($currentUser);

            $players = $game->getUsers();
            $winnerKey = $game->getWinner() - 1;
            $winnerUser = null;
            $looserUser = null;
            foreach ($players as $key => $player) {
                if ($key == $winnerKey) {
                    $winnerUser = $player;
                }
                else{
                    $looserUser = $player;
                }
            }
            if ($looserUser && $winnerUser) {
                /** @var UserRepository $userRepository */
                $userRepository = $this->getDoctrine()->getRepository(User::class);
                /** @var TableTennisEloHistoryRepository $eloRepository */
                $eloRepository = $this->getDoctrine()->getRepository(TableTennisEloHistory::class);
                /** @var TableTennisStatsRepository $statsRepository */
                $statsRepository = $this->getDoctrine()->getRepository(TableTennisStats::class);

                $winnerStats = $winnerUser->getTableTennisStats();
                $looserStats = $looserUser->getTableTennisStats();
                $eloRepository->saveCurrentEloRating($winnerUser,$winnerStats);
                $eloRepository->saveCurrentEloRating($looserUser,$looserStats);

                $eloScores = $userRepository->getUpdatedEloScore($winnerStats, $looserStats);

                $statsRepository->userHasWon($winnerUser,$eloScores['a'] ? $eloScores['a'] : 0);
                $statsRepository->userHasLost($looserUser,$eloScores['b'] ? $eloScores['b'] : 0);

                $game->setWinnerUser($winnerUser);
                $em->persist($game);
                $em->flush();

                $ranking = $statsRepository->getUserRanking(150);
                $slackService->sendVictoryMessage($winnerUser,$looserUser,$ranking);
            }

            return $this->redirectToRoute('tabletennis_home_page');
        }

        $currentUser = $this->getUser();

        return $this->render('tabletennis/new.html.twig', [
            'game'        => $game,
            'form'        => $form->createView(),
            'currentUser' => $currentUser
        ]);
    }

    /**
     * @Route("tabletennis/{id}", name="tabletennis_game_show", methods="GET")
     */
    public function show(TableTennisGame $game): Response
    {
        return $this->render('tabletennis/show.html.twig', ['game' => $game]);
    }

    /**
     * @Route("tabletennis/{id}/edit", name="tabletennis_game_edit", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param TableTennisGame $game
     * @return Response
     */
    public function edit(Request $request, TableTennisGame $game): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tabletennis_game_edit', ['id' => $game->getId()]);
        }

        return $this->render('tabletennis/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("tabletennis/{id}", name="tabletennis_game_delete", methods="DELETE")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param TableTennisGame $game
     * @return Response
     */
    public function delete(Request $request, TableTennisGame $game): Response
    {
        if ($this->isCsrfTokenValid('delete' . $game->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($game);
            $em->flush();
        }

        return $this->redirectToRoute('tabletennis_game_index');
    }
}
