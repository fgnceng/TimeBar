<?php


namespace App;


final class Events
{
    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     *
     * @var string
     */
    public const ARTICLE_CREATED = 'article.created';
}
