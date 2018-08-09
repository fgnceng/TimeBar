<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function login( AuthenticationUtils $utils,Request $request)
    {
        $error=$utils->getLastAuthenticationError();
        $lastUsername=$utils->getLastUsername();

        return $this->render('security/login.html.twig', [
           'last_Username'=>$lastUsername,
            'error'=>$error,
        ]);
    }
}
