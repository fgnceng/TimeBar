<?php

namespace App\Controller;

use App\Form\PasswordResetNewType;
use App\Form\PasswordResetRequestType;
use App\Repository\UserRepository;
use App\Services\ResetPassword;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Services\FlashMessage;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormError;
use App\Events;
use App\Entity\User;

class SecurityController extends Controller
{
    /**
     * @var FlashMessage
     */
    private $flashMessage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $checker;


    public function __construct(
        AuthenticationUtils $authenticationUtils,
        AuthorizationCheckerInterface $checker,
        FlashMessage $flashMessage
    )
    {

        $this->checker = $checker;
        $this->flashMessage = $flashMessage;
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $utils, Request $request)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_Username' => $lastUsername,
            'error' => $error,
        ]);

        $this->redirectToRoute('app_homepage');
    }


    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * Form to send a password reset request.
     *
     * @Route("/password_reset/request", name="password_reset_request", methods={"GET", "POST"})
     *
     * @param Request        $request
     * @param UserRepository $userRepository
     * @param ResetPassword  $resetPassword
     *
     * @return Response
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function passwordResetRequest(Request $request, UserRepository $userRepository, ResetPassword $resetPassword): Response
    {
        $form = $this->createForm(PasswordResetRequestType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy([
                'email' => $form['email']->getData(),
            ]);
            if ($user) {
                $resetPassword->reset($user);
                $this->flashMessage->createMessage($request, FlashMessage::INFO_MESSAGE, 'Un mail de réinitialisation a été envoyé à cette adresse mail');

                return $this->redirectToRoute('login');
            }

            $form->addError(new FormError("The completed email is not linked to any account"));
        }

        return $this->render('blog/security/password/password_reset_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Form to create the new password.
     *
     * @Route("/password_reset/new", name="password_reset_new", methods={"GET", "POST"})
     *
     * @param Request                      $request
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $encoder
     * @param EventDispatcherInterface     $eventDispatcher
     *
     * @return Response
     */
    public function passwordResetNew(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder, EventDispatcherInterface $eventDispatcher): Response
    {
        $token = $request->query->get('resetPasswordToken');
        $user = $userRepository->getByValidToken($token);

        if (null === $token || empty($token) || null === $user) {
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(PasswordResetNewType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isTokenNotExpired($user)) {
                $em = $this->getDoctrine()->getManager();
                $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));

                $event = new GenericEvent($user);
                $eventDispatcher->dispatch(Events::TOKEN_RESET, $event);
                $em->flush();
                $this->flashMessage->createMessage($request, FlashMessage::INFO_MESSAGE, 'Le mot de passe a été réinitialisé avec succès !');

                return $this->redirectToRoute('login');
            }
            $this->flashMessage->createMessage($request, FlashMessage::ERROR_MESSAGE, 'Le token est expiré. Veuillez effectuer une nouvelle demande.');
        }

        return $this->render('blog/security/password/password_reset_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function isTokenNotExpired(User $user): bool

    {
        return $user->getTokenExpirationDate() > new \DateTime();

    }
}
