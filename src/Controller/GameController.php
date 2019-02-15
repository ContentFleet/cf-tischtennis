<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Form\GameType;
use App\Form\ScoreType;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\Slack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
        return $this->render(
            'game/index.html.twig', [
                'games' => $gameRepository->findBy(
                    array(),
                    array('id' => 'DESC'),
                    25)
        ]);
    }

    /**
     * @Route("/new", name="game_new", methods="GET|POST")
     * @param Request $request
     * @param Slack $slackService
     * @return Response
     */
    public function new(Request $request, Slack $slackService, GameRepository $gameRepository): Response
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
            $gameScore = $game->getScore();
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


            $isWinnerScoreCorrect = $gameRepository->isWinnerWithScoreCorrect($gameScore, $game->getUsers(), $winnerUser);
            if(!$isWinnerScoreCorrect){
                $form->addError(new FormError("The score doesn't match the chosen winner - ". $winnerUser->getDisplayName()));
            }

            if(!$form->getErrors()->count()) {
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

                    $game->setWinnerUser($winnerUser);
                    $em->persist($game);
                    $em->flush();

                    $ranking = $userRepository->getAllEnabledUsers(array(), array('eloRating' => 'DESC'), 150);
                    $slackService->sendVictoryMessage($winnerUser, $looserUser, $ranking);
                }
                else{
                    $form->addError(new FormError("Problem by retrieving the players"));
                }

                return $this->redirectToRoute('game_index');
            }
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
     * @IsGranted("ROLE_ADMIN")
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
     * @IsGranted("ROLE_ADMIN")
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

    /**
     * @Route("/api/rendered/score", name="game_score_rendered_form", methods="GET")
     */
    public function getScoreRenderedForm(Request $request, GameRepository $gameRepository) : Response
    {
        $eloRating1 = $request->get('elorating1');
        $eloRating2 = $request->get('elorating2');

        if($eloRating1 && $eloRating2) {
            $possibleScore = $gameRepository->getPossibleScore($eloRating1,$eloRating2);
        }
        else{
            $possibleScore  = [
                '3-0' => '3-0',
                '3-1' => '3-1',
                '3-2' => '3-2',
                '2-3' => '2-3',
                '1-3' => '1-3',
                '0-3' => '0-3'
            ];
        }

        $form = $this->createForm(
            ScoreType::class,
            null,
            [
                'choices' => $possibleScore
            ]
        );

        $renderedForm = $this->renderView(
            'game/_score_part_form.html.twig',
            [
                'form' => $form->createView()
            ]
        );

        $response = new JsonResponse();
        $response->setData(['renderedForm' => $renderedForm]);
        return $response;
    }
}
