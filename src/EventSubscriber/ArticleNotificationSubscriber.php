<?php

namespace App\EventSubscriber;

use App\Entity\Article;
use App\Entity\User;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;


class ArticleNotificationSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $translator;
    private $urlGenerator;
    private $sender;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, $sender)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->sender = $sender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ARTICLE_CREATED => 'onArticleCreated',
        ];
    }

    public function onArticleCreated(GenericEvent $event): void
    {
        /** @var Article $article */
        $article = $event->getSubject();
        $linkToPost = $this->urlGenerator->generate('article_show', [
            'slug' => $article->getSlug(),
            '_fragment' => 'article_'.$article->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = $this->translator->trans('notification.article_created');
        $body = $this->translator->trans('notification.article_created.description', [
            '%title%' => $article->getTitle(),
            '%link%' => $linkToPost,
        ]);

        $message = (new \Swift_Message())
            ->setSubject($subject)
          //  ->setTo("figensunal@gmail.com")
          ->setTo($article->getAuthor()->getEmail())
            ->setFrom($this->sender)

            ->setBody($body, 'text/html')
        ;

        $this->mailer->send($message);
    }
}
