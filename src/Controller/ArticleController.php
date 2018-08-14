<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Tag;
use App\Form\ArticleType;
//use function Sodium\crypto_auth;
use Faker\Provider\Image;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    public function new(Request $request): Response
    {
        $article = new Article();
        $user = $this->getUser();
        if (is_null($user)) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        $article->setAuthor($user->getUsername());


        $form = $this->createForm(ArticleType::class, $article)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $article->setSlug(Slugger::slugify($article->getTitle()));
           // $image=$article->getImageFile();
            ////$article->setImageFilename($image);
            /**
             * var UploadFile $file;
             */
            $file=$article->getImageFile();
             $fileName=md5(uniqid()).'.'.$file->guessExtension();
             $article->setImageFilename($fileName);
             $article->getImageFilename();


            $em = $this->getDoctrine()->getManager();
            $em->persist($article);

            $em->flush();
            $this->addFlash( 'succes','Your article created successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {

                return $this->redirectToRoute('app_homepage');

            }

            return $this->redirectToRoute('user_article_new');
        }

        return $this->render('article/new_article.html.twig', [
            'article' => $article,
            'form' => $form->createView(),

        ]);
    }

}
