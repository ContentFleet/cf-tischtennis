<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameRepository;
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
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->getDoctrine()->getRepository(Game::class);
        /** @var GameRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $data = [];
        $data['users'] = $userRepository->findBy(array(), array('eloRating' => 'DESC'), 150);
        $data['games'] = $gameRepository->findBy(array(), array('id' => 'DESC'), 150);
        return $this->render('landing_page/index.html.twig', $data);
    }
}
