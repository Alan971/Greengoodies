<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class JWTAuthenticationListener 
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * Control of access to API in case of AuthenticationSuccessEvent
     *
     * @param AuthenticationSuccessEvent $event
     * @return void
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getUser();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if(!$user->isApiAccess()){
            throw new AccessDeniedHttpException("Votre clef API n'est pas activée");
        }
    }
}