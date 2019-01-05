<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Ticket;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;

class TicketController extends AbstractController
{
    /**
     * @Route("/ticket/new", name="new_ticket")
     */
    public function newTicket(Request $request): Response
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $ticket = new Ticket();
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findOneById($userId);
        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->addUser($user);
            
            $message = new Message();
            $message->setTitle($form->get('title')->getData());
            $message->setContent($form->get('content')->getData());
            $ticket->addMessage($message);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirect('/');
        }
            
        return $this->render('ticket/new_ticket.html.twig', [
            'controller_name' => 'TicketController',
            'ticketForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ticket/{id}", name="ticket_show")
     */
    public function showTicket(Request $request, $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository(Ticket::class)->findOneById($id);
        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = new Message();
            $message->setTitle($form->get('title')->getData());
            $message->setContent($form->get('content')->getData());
            $ticket->addMessage($message);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->persist($ticket);
            $entityManager->flush();
        }

        return $this->render('ticket/index.html.twig', [
            'controller_name' => 'TicketController',
            'ticketForm' => $form->createView(),
            'ticket' => $ticket,
        ]);
    }
}
