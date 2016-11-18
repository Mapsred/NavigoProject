<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 10/10/16
 * Time: 16:52
 */

namespace AppBundle\Admin;

use Sonata\DoctrineORMAdminBundle\Admin\FieldDescription;
use UserBundle\Entity\Image;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

/**
 * Class ImageAdmin
 * @package AppBundle\Admin
 */
class ImageAdmin extends AbstractAdmin
{
    /**
     * @param Image $image
     */
    public function prePersist($image)
    {
        $this->manageFileUpload($image);
    }

    /**
     * @param Image $image
     */
    private function manageFileUpload($image)
    {
        if ($image->getFile()) {
            $image->refreshUpdated();
        }
    }

    /**
     * @param Image $image
     */
    public function preUpdate($image)
    {
        $this->manageFileUpload($image);
    }

    /**
     * @param mixed $object
     * @return string
     */
    public function toString($object)
    {
        return $object instanceof Image ? $object->getPath() : 'Image'; // shown in the breadcrumb on the create view
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Image $image */
        $image = $this->getSubject();
        $fileFieldOptions = ['label' => 'Image', 'required' => false];
        if ($image && ($path = $image->getPath())) {
            $container = $this->getConfigurationPool()->getContainer();
            $fullPath = $container->get('request_stack')->getCurrentRequest()->getBasePath().'/uploads/images/'.$path;
            $fileFieldOptions['sonata_help'] = '<img src="'.$fullPath.'" class="admin-preview" />';
        }
        $formMapper->add('file', 'file', $fileFieldOptions);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('id')->add('path');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->add('id', null, ['label' => 'ID'])
            ->addIdentifier('path', 'image',
                ['prefix' => '/uploads/images/', 'width' => 200, 'height' => null, 'label' => "Image"]
            );
    }
}