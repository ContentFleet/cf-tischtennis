<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Form\GameType;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\Slack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/games")
 */
class GameController extends AbstractController
{
    /**
     * @Route("/", name="game_index", methods="GET")
     */
    public function index(GameRepository $gameRepository): Response
    {
        return $this->render('game/index.html.twig', ['games' => $gameRepository->findBy(array(), array('id' => 'DESC'), 100)]);
    }

    /**
     * @Route("/new", name="game_new", methods="GET|POST")
     * @param Request $request
     * @param Slack $slackService
     * @return Response
     */
    public function new(Request $request, Slack $slackService): Response
    {
        $em = $this->getDoctrine()->getManager();
        $game = new Game();
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
                $eloScores = $userRepository->getUpdatedEloScore($winnerUser, $looserUser);

                $winnerUser->hasWon();
                $looserUser->hasLost();
                $winnerUser->setEloRating($eloScores['a'] ? $eloScores['a'] : 0);
                $looserUser->setEloRating($eloScores['b'] ? $eloScores['b'] : 0);
                $em->persist($winnerUser);
                $em->persist($looserUser);

                $ranking = $userRepository->findBy(array(), array('eloRating' => 'DESC'), 150);
                $slackService->sendVictoryMessage($winnerUser,$looserUser,$ranking);
            }


            $game->setWinnerUser($winnerUser);
            $em->persist($game);
            $em->flush();

            return $this->redirectToRoute('game_index');
        }

        $currentUser = $this->getUser();

        return $this->render('game/new.html.twig', [
            'game'        => $game,
            'form'        => $form->createView(),
            'currentUser' => $currentUser
        ]);
    }

    /**
     * @Route("/{id}", name="game_show", methods="GET")
     */
    public function show(Game $game): Response
    {
        return $this->render('game/show.html.twig', ['game' => $game]);
    }

    /**
     * @Route("/{id}/edit", name="game_edit", methods="GET|POST")
     */
    public function edit(Request $request, Game $game): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('game_edit', ['id' => $game->getId()]);
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="game_delete", methods="DELETE")
     */
    public function delete(Request $request, Game $game): Response
    {
        if ($this->isCsrfTokenValid('delete' . $game->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($game);
            $em->flush();
        }

        return $this->redirectToRoute('game_index');
    }
}
