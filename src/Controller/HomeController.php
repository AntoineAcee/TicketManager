<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Message;

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
                'route_name' => 'Home',
            ]);
        }
        
        $em = $this->getDoctrine()->getManager();

        $userId = $user->getId();
        $currentUser = $em->getRepository(User::class)->findOneById($userId);
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $tickets = $em->getRepository(Ticket::class)->findAll();
        } else {
            $tickets = $em->getRepository(Ticket::class)->findBy(["owner" => $currentUser]);
        }

        if (empty($tickets)) {
            return $this->render('home/index.html.twig', [
                'route_name' => 'Home',
                'curentUser' => $currentUser->getEmail(),
                'tickets' => $tickets,
            ]);
        }

        foreach ($tickets as $key => $ticket) {
            $firstMessages[] = $ticket->getFirstMessage();
        }

        return $this->render('home/index.html.twig', [
            'route_name' => 'Home',
            'curentUser' => $currentUser->getEmail(),
            'tickets' => $tickets,
            'firstMessages' => $firstMessages,
        ]);
    }
}
