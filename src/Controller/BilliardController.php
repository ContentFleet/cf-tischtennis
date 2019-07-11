<?php

namespace App\Controller;

use App\Entity\BilliardEloHistory;
use App\Entity\BilliardGame;
use App\Entity\BilliardStats;
use App\Entity\User;
use App\Form\GameType;
use App\Repository\BilliardEloHistoryRepository;
use App\Repository\BilliardGameRepository;
use App\Repository\GameRepository;
use App\Repository\BilliardStatsRepository;
use App\Repository\UserRepository;
use App\Service\Slack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class BilliardController extends AbstractController
{
    /**
     * @Route("/billiard", name="billiard_home_page")
     */
    public function index()
    {
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->getDoctrine()->getRepository(BilliardGame::class);
        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository(User::class);


        /** @var BilliardStatsRepository $billiardStatsRepository */
        $billiardStatsRepository = $this->getDoctrine()->getRepository(BilliardStats::class);

        $data = [];
        $data['usersBilliard'] = $billiardStatsRepository->getUserRanking(150);
        return $this->render('billiard/index.html.twig', $data);
    }

    /**
     * @Route("billiard/rules", name="rules")
     */
    public function rules()
    {
        $data = [];
        return $this->render('landing_page/rules.html.twig', $data);
    }

    /**
     * @Route("/billiard/games", name="billiard_game_index", methods="GET")
     * @param BilliardGameRepository $gameRepository
     * @return Response
     */
    public function gameIndex(BilliardGameRepository $gameRepository): Response
    {
        $games = $gameRepository->findBy(array(), array('id' => 'DESC'), 50);
        return $this->render('billiard/games.html.twig', ['games' => $games]);
    }

    /**
     * @Route("billiard/new", name="billiard_game_new", methods="GET|POST")
     * @param Request $request
     * @param Slack $slackService
     * @return Response
     */
    public function new(Request $request, Slack $slackService): Response
    {
        $em = $this->getDoctrine()->getManager();
        $game = new BilliardGame();
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
                /** @var BilliardEloHistoryRepository $eloRepository */
                $eloRepository = $this->getDoctrine()->getRepository(BilliardEloHistory::class);
                /** @var BilliardStatsRepository $statsRepository */
                $statsRepository = $this->getDoctrine()->getRepository(BilliardStats::class);

                $winnerStats = $winnerUser->getBilliardStats();
                $looserStats = $looserUser->getBilliardStats();
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

            return $this->redirectToRoute('billiard_game_index');
        }

        $currentUser = $this->getUser();

        return $this->render('billiard/new.html.twig', [
            'game'        => $game,
            'form'        => $form->createView(),
            'currentUser' => $currentUser
        ]);
    }

    /**
     * @Route("billiard/{id}", name="billiard_game_show", methods="GET")
     */
    public function show(BilliardGame $game): Response
    {
        return $this->render('billiard/show.html.twig', ['game' => $game]);
    }

    /**
     * @Route("billiard/{id}/edit", name="billiard_game_edit", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param BilliardGame $game
     * @return Response
     */
    public function edit(Request $request, BilliardGame $game): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('billiard_game_edit', ['id' => $game->getId()]);
        }

        return $this->render('billiard/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("billiard/{id}", name="billiard_game_delete", methods="DELETE")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param BilliardGame $game
     * @return Response
     */
    public function delete(Request $request, BilliardGame $game): Response
    {
        if ($this->isCsrfTokenValid('delete' . $game->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($game);
            $em->flush();
        }

        return $this->redirectToRoute('billiard_game_index');
    }
}
