<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

use App\Entity\User;
use App\Form\RegisterType;

class SecurityController extends AbstractController
{
    /**
     * @Route("/security", name="security")
     */
    public function index()
    {
        $user = $this->getUser();

        if ($user) {
            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
        } else {
            return $this->render('security/index.html.twig', [
                'controller_name' => 'SecurityController',
            ]);
        }
    }

    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($form->get('email')->getData());
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirect('home');
        }
            
        return $this->render('security/register.html.twig', [
            'controller_name' => 'SecurityController',
            'registerForm' => $form->createView(),
        ]);
    }
}
