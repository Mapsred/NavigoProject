<?php
namespace UserBundle\EventListener;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use UserBundle\Entity\Image;

/**
 * Created by PhpStorm.
 * User: Maps_red
 * Date: 29/08/2016
 * Time: 20:11
 */
class UploadListener
{
    /** * @var string $path */
    private $path;

    /**
     * ProjectUploadListener constructor.
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->uploadFile($entity);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->uploadFile($entity);
    }

    /**
     * @param $entity
     */
    public function uploadFile($entity)
    {
        // upload only works for Product entities

        /** @var UploadedFile $file */
        if ($entity instanceof Image && $entity->getFile() instanceof UploadedFile) {
            $file = $entity->getFile();
            $fileName = $file->getClientOriginalName();
            $file->move($this->path, $fileName);
            $entity->setPath($fileName);
        }else {
            return;
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $entity->setFile(new File($this->path.'/'.$entity->getPath()));
        }
    }
}