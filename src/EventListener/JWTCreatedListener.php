<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTCreatedListener extends JWTAuthenticatedEvent
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onJWTCreated(JWTCreatedEvent  $event) : JsonResponse
    {
            $user = $event->getUser();
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if(!$user->isApiAccess()){

                // $jsonResponse = json_encode(['message' => "Votre clef API n'est pas activée", 'status' => 403]);
                // $response->setContent($jsonResponse, 403, ['Content-Type' => 'application/json']);
                // $event->setResponse($response);
                $event->stopPropagation();
                //$jwt = new JWTAuthenticatedEvent(['token' =>'blabla', 'user' => 'test1'], "" ); 
                //$jwt->setPayload(['token' =>'blabla', 'user' => 'test1']);
                $jsonResponse = json_encode(['message' => "Votre clef API n'est pas activée", 'status' => 403]);
                return new JsonResponse($jsonResponse, 403, ['Content-Type' => 'application/json']);
            } 
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getUser();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if(!$user->isApiAccess()){
            $jsonResponse = json_encode(['message' => "Votre clef API n'est pas activée", 'status' => 403]);
            return new JsonResponse($jsonResponse, 403, ['Content-Type' => 'application/json']);
        }
    }

}