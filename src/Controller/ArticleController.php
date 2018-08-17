<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Tag;
use App\Events;
use App\Form\ArticleType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
use App\Utils\Slugger;
use App\DataFixtures\ArticleFixtures;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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

        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setIpadress($request->getClientIp());
            $comment->setIsDeleted(false);
            $comment->setArticle($article);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);

            $entityManager->flush();
            return $this->render('article/show.html.twig', array(
                'commentForm' => $commentForm->createView(),
                'article' => $article,

            ));

        }
        $comment = $this->getDoctrine()->getRepository(Comment::class)->findAll();

        return $this->render('article/show.html.twig', array(
            'commentForm' => $commentForm->createView(),
            'article' => $article,

        ));
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


    /**
     * @Route("/user/article/new", name="user_article_new")
     */

    public function new(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $article = new Article();
        $user = $this->getUser();
        $article->setAuthor($user);

        if (is_null($user)) {
            // throw $this->createAccessDeniedException('Access Denied.');
            $this->addFlash('notice', 'To create an article, you must first log in.');
            return $this->redirectToRoute('app_homepage');
        }


        $form = $this->createForm(ArticleType::class, $article)
            ->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * var UploadFile $file;
             */
            $file = $article->getImageFile();
            $imageFilename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('images_directory'), $imageFilename
            );
            $article->setImageFilename($imageFilename);
            $article->setSlug(Slugger::slugify($article->getTitle()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('succes', 'Your article created successfully.');

            $event = new GenericEvent($article); // yeni bir event oluşturduk bizim article mızın oluşturulmasına göre tetiklenerek çalışmaya başlayacak.
            $eventDispatcher->dispatch(Events::ARTICLE_CREATED, $event);

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('app_homepage');
            }

            return $this->redirectToRoute('user_article_new');
        }

        return $this->render('article/new_article.html.twig', [
            'article' => $article,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

}
