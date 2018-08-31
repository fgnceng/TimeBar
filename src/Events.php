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

    /**
     * @Eçvent("Symfony\Component\EventDispatcher\GenericEvent")
     *
     * @var string
     */
    public const TOKEN_RESET = 'token.reseted';


}
