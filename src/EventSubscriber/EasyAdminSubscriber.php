<?php

namespace App\EventSubscriber;

use App\Entity\Header;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $appKernel;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setIllustration'],
            BeforeEntityUpdatedEvent::class => ['updateIllustration']
        ];
    }

    public function uploadIllustration($event, $entityName)
    {
        $entity = $event->getEntityInstance();

        $tmp_name = $_FILES[$entityName]['tmp_name']['illustration'];
        $filename = uniqid();
        $extension = pathinfo($_FILES[$entityName]['name']['illustration'], PATHINFO_EXTENSION);

        $project_dir = $this->appKernel->getProjectDir();

        move_uploaded_file($tmp_name, $project_dir.'/public/uploads/'.$filename.'.'.$extension);

        $entity->setIllustration($filename.'.'.$extension);
    }

    public function updateIllustration(BeforeEntityUpdatedEvent $event)
    {
        if (!($event->getEntityInstance() instanceof Product) && !($event->getEntityInstance() instanceof Header)) {
            return;
        }

        $reflexion = new \ReflectionClass($event->getEntityInstance());
        $entityName = $reflexion->getShortName();

        if ($_FILES[$entityName]['tmp_name']['illustration'] != '') {
            $this->uploadIllustration($event, $entityName);
        }
    }

    public function setIllustration(BeforeEntityPersistedEvent $event)
    {
        $reflexion = new \ReflectionClass($event->getEntityInstance());
        $entityName = $reflexion->getShortName();

        if (!($event->getEntityInstance() instanceof Product) && !($event->getEntityInstance() instanceof Header)) {
            return;
        }

        $this->uploadIllustration($event, $entityName);
    }
}