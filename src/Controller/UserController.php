<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\BilliardEloHistoryRepository;
use App\Repository\BilliardGameRepository;
use App\Repository\EloHistoryRepository;
use App\Repository\GameRepository;
use App\Repository\TableTennisEloHistoryRepository;
use App\Repository\TableTennisGameRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods="GET")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', ['users' => $userRepository->findAll()]);
    }

    /**
     * @Route("/new", name="user_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods="GET")
     * @param User $user
     * @param UserRepository $userRepository
     * @param TableTennisGameRepository $tableTennisGameRepository
     * @param TableTennisEloHistoryRepository $tableTennisEloHistoryRepository
     * @param BilliardGameRepository $billiardGameRepository
     * @param BilliardEloHistoryRepository $billiardEloHistoryRepository
     * @return Response
     */
    public function show(
        User $user,
        UserRepository $userRepository,
        TableTennisGameRepository $tableTennisGameRepository,
        TableTennisEloHistoryRepository $tableTennisEloHistoryRepository,
        BilliardGameRepository $billiardGameRepository,
        BilliardEloHistoryRepository $billiardEloHistoryRepository
    ): Response
    {
        $tableTennisWinLooseStats = $tableTennisGameRepository->getStatsAgainstPlayers($user->getId());
        $billiardWinLooseStats = $billiardGameRepository->getStatsAgainstPlayers($user->getId());
        $monthsEloHistory = [];
        $dateEloHistory = new \DateTime("- 5 months");
        for ($i = 1; $i <= 6; $i++) {
            $monthsEloHistory[] = $dateEloHistory->format("Y-m");
            $dateEloHistory->add(new \DateInterval("P1M"));
        }
        $tableTennisEloHistory = $tableTennisEloHistoryRepository->getEloHistory($user->getId(), $monthsEloHistory);
        $billiardEloHistory = $billiardEloHistoryRepository->getEloHistory($user->getId(), $monthsEloHistory);
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'monthsEloHistory' => $monthsEloHistory,
            'tableTennisWinLooseStats' => $tableTennisWinLooseStats,
            'tableTennisEloHistory' => $tableTennisEloHistory,
            'billiardWinLooseStats' => $billiardWinLooseStats,
            'billiardEloHistory' => $billiardEloHistory
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods="DELETE")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
