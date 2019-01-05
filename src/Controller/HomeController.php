<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Ticket;
use App\Entity\User;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $securityContext = $this->container->get('security.authorization_checker');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        if ($user === 'anon.') {
            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
        }
        
        $em = $this->getDoctrine()->getManager();

        $userId = $user->getId();
        $currentUser = $em->getRepository(User::class)->findOneById($userId);
        $tickets = $em->getRepository(Ticket::class)->findBy(["owner" => $currentUser]);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'curentUser' => $currentUser->getEmail(),
            'tickets' => $tickets,
        ]);
    }
}
