<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\EventListener\EventPriorities;

use App\Entity\User;


class UserPasswordSubscriber implements EventSubscriberInterface
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function cryptPassword(GetResponseForControllerResultEvent $event)
    {
        
        $entity = $event->getControllerResult();// resultat (objet) d'une méthode executé dans le contrôleur
        $method = $event->getRequest()->getMethod(); // la méthode http utilisée

        if($entity instanceof User && ($method == Request::METHOD_POST)){
            $entity->setPassword($this->passwordEncoder->encodePassword(
                $entity,
                $entity->getPassword()
            ));
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.view' => ['cryptPassword', EventPriorities::PRE_WRITE],
        ];
    }
}
