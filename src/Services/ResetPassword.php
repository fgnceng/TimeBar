<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Entity\User;

class ResetPassword
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $templating;

    /**
     * @var  TokenGeneratorInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $token;

    /**
     * @param EntityManagerInterface $em
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $templating
     * @param TokenGeneratorInterface $generator
     */
    public function __construct(
        EntityManagerInterface $em,
        \Swift_Mailer $mailer,
        \Twig_Environment $templating,
        TokenGeneratorInterface $generator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->generator = $generator;
    }


    /**
     * @param $user
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     * @throws  \Twig_Error_Runtime
     */


    public function reset($user): void
    {
        $this->addToken($user);
        $this->sendResetPasswordEmail($user, $this->token);

    }

    public function addToken($user)
    {
        $this->token->$this->generateToken();
        $user->setResetToken($this->token);
        $user->setTokenExprationDate();
        $this->em->flush();
    }

    /**
     * @param $user
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     */
    private function sendResetPasswordEmail($user, $token)
    {


        $message = (new \Swift_Message('Request to reset password'))
            ->setFrom("figen@iyimakina.com")
            ->setTo($user->getEmail())
            ->setBody($this->templating->render('security/password/email/password_reset_email',
                [
                    'username' => $user->getUsername(),
                    'token' => $token,
                ]),
        'text/html'
    );
        $this->mailer->send($message);


    }

    /**
     * @return string
     */
    private function generateToken():string

    {
        return $this->generator->generateToken();

    }

}
