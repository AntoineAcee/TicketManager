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
use App\Form\AddUserTicketType;

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
            $message->setUser($user);
            $ticket->addMessage($message);
            $ticket->setOwner($this->get('security.token_storage')->getToken()->getUser());

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
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findOneById($userId);

        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository(Ticket::class)->findOneById($id);
        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = new Message();
            $message->setTitle($form->get('title')->getData());
            $message->setContent($form->get('content')->getData());
            $message->setUser($user);
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

    /**
     * @Route("/ticket/delete/{id}", name="ticket_delete")
     */
    public function deleteTicket($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository(Ticket::class)->findOneById($id);
        
        if (!$ticket) {
            throw $this->createNotFoundException('No ticket found');
        }

        $em->remove($ticket);
        $em->flush();

        return $this->redirect('/');
    }

    /**
     * @Route("/message/delete/{id}", name="message_delete")
     */
    public function deleteMessage($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository(Message::class)->findOneById($id);
        $ticket = $message->getTicket();

        if (!$message) {
            throw $this->createNotFoundException('No message found');
        }
        
        if (count($ticket->getMessages()) === 1) {
            $em->remove($ticket);
        }
       
        $em->remove($message);
        $em->flush();

        return $this->redirect('/');
    }

    /**
     * @Route("/message/edit/{id}", name="message_edit")
     */
    public function editMessage($id, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository(Message::class)->findOneById($id);
        $ticket = $message->getTicket();

        if (!$message) {
            throw $this->createNotFoundException('No message found');
        }

        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->persist($message);
            $em->flush();

            return $this->redirect('/ticket/'.$ticket->getId());
        }

        return $this->render('ticket/edit_message.html.twig', [
            'controller_name' => 'TicketController',
            'editMessage' => $form->createView(),
            'ticket' => $ticket,
        ]);
    }

    /**
     * @Route("/add/user/{id}", name="add_user_ticket")
     */
    public function addUserTicket($id, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository(Ticket::class)->findOneById($id);

        if (!$ticket) {
            throw $this->createNotFoundException('No ticket found');
        }

        $form = $this->createForm(AddUserTicketType::class, $ticket, ['ticket' => $ticket]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->addUser($form->get('users')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();
        }

        return $this->render('ticket/add_user_ticket.html.twig', [
            'controller_name' => 'TicketController',
            'addUserTicketForm' => $form->createView(),
        ]);
    }
}
