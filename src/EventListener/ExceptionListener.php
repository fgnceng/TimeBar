<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)

    {
        $exception = $event->getException(); // Alınan olaydan özel durum nesnesini alırsınız
        $message=sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
            );

        $response=new Response(); //Özel durum ayrıntılarını görüntülemek için yanıt nesnesini özelleştirin
        $response->setContent($message);

       //HttpExceptionInterface, özel bir özel durum türüdür,
        //durum kodunu ve başlık ayrıntılarını tutar


        if($exception instanceof HttpExceptionInterface )
        {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());

        }else{
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $event->setResponse($response);
        //değiştirilen yanıt nesnesini olaya gönderir
    }

}