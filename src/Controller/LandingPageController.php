<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
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

        $data = [];
        $data['games'] = $gameRepository->findBy(array(), array('id' => 'DESC'),20);
        return $this->render('landing_page/index.html.twig', $data);
    }
}
