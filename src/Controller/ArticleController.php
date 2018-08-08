<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\CommentUser;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Service\MarkdownHelper;
use App\Service\SlackClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ArticleController extends AbstractController
{
    /**
     * Currently unused: just showing a controller with a constructor!
     */
    private $isDebug;

    public function __construct(bool $isDebug)
    {
        $this->isDebug = $isDebug;

    }


    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(ArticleRepository $repository)
    {
        //$repository = $em->getRepository(Article::class);

        $articles = $repository->findAllPublishedOrderedByNewest();//Bütün article'ları articles dizisinde tutuyor.
        return $this->render('article/homepage.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/news/{slug}", name="article_show")
     * @ParamConverter("article", class="App\Entity\Article")
     */

    public function show(Article $article, SlackClient $slack, EntityManagerInterface $entityManager, Request $request)
    {
        if ($article->getSlug() == 'khaaaaaan') {
            $slack->sendMessage('Kahn', 'Ah, Kirk, my old friend...');
        }
        if (!$article) {
            throw $this->createNotFoundException(sprintf('No article for slug "%s"', $article->getSlug()));
        }

        $commentuser = new CommentUser();
        $commentForm = $this->createForm(CommentType::class, $commentuser);

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $commentuser = $commentForm->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentuser);
            $entityManager->flush();
            $this->addFlash('succes', 'Comment created Succesfully');

            return $this->render('article/show.html.twig', array(
                'commentForm' => $commentForm->createView(),
                'article'=>$article,

            ));

        }

            return $this->render('article/show.html.twig', array(
                'commentForm' => $commentForm->createView(),
                'article'=>$article,

            ));
        }


       public function showComment() {
        $article = $this->getDoctrine()->getRepository(CommentUser::class)->findAll();
        return $this->render('articles/show.html.twig', array('commentuser' => $commentuser));
    }


    /**
     * @Route("/news/{slug}/heart", name="article_toggle_heart")
     *
     */

    public function toggleArticleHeart(Article $article, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $heartCount = $article->incrementHeartCount();
        $entityManager->persist($heartCount);
        $entityManager->flush();
        return new JsonResponse(['hearts' => $article->getHeartCount()]);

    }


}
