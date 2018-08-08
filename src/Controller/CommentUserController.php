<?php


namespace App\Controller;

use App\Form\CommentType;
use App\Entity\CommentUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;


class CommentUserController extends Controller
{
    /**
     * @Route("/news/{slug}", name="comment_show")
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {

        $commentuser = new CommentUser();
        $form = $this->createForm(CommentType::class, $commentuser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commentuser = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentuser);
            $entityManager->flush();

           return $this->redirectToRoute('article_show');

        }

        return $this->redirectToRoute('article/show.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
