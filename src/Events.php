<?php


namespace App;

/**
 * This class defines the names of all the events dispatched in
 * the Symfony Demo application. It's not mandatory to create a
 * class like this, but it's considered a good practice.
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
final class Events
{
    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     *
     * @var string
     */
    public const ARTICLE_CREATED = 'article.created';
}
