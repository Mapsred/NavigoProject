<?php
/**
 * Created by PhpStorm.
 * User: Maps_red
 * Date: 11/10/2016
 * Time: 00:31
 */

namespace AppBundle\Admin;

use Sonata\DoctrineORMAdminBundle\Admin\FieldDescription;
use UserBundle\Entity\Image;
use UserBundle\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends AbstractAdmin
{
    /**
     * @param User|mixed $object
     * @return string
     */
    public function toString($object)
    {
        return $object instanceof User ? $object->getUsername() : 'Utilisateur';
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Utilisateur')
            ->with("Profil", ['class' => "col-md-6"])
            ->add('username', 'text', ['label' => 'Nom d\'utilisateur'])
            ->end()
            ->with("Statut", ['class' => "col-md-6"])
            ->add('enabled', 'checkbox', ['label' => 'Activé'])
            ->end()
            ->with("Image")
            ->add('image','sonata_type_admin', ['delete' => false])
            ->end();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('username');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username', null, ['label' => 'Nom d\'utilisateur'])
            ->add("card.uuid", null, ['label' => "UUID Navigo"])
            ->add('enabled', null, ['label' => 'Activé'])
            ->add('created_at', 'datetime', ['label' => 'Créé le'])

        ;
    }

    /**
     * @param User $object
     */
    public function prePersist($object)
    {
        $this->manageEmbeddedImageAdmins($object);
    }

    /**
     * @param User $object
     */
    public function preUpdate($object)
    {
        $this->manageEmbeddedImageAdmins($object);
    }

    /**
     * @param $page
     */
    private function manageEmbeddedImageAdmins($page)
    {
        // Cycle through each field
        /** @var FieldDescription $fieldDescription */
        foreach ($this->getFormFieldDescriptions() as $fieldName => $fieldDescription) {
            // detect embedded Admins that manage Images
            if ($fieldDescription->getType() === 'sonata_type_admin' &&
                ($associationMapping = $fieldDescription->getAssociationMapping()) &&
                $associationMapping['targetEntity'] === 'UserBundle\Entity\Image'
            ) {
                $getter = 'get'.$fieldName;
                $setter = 'set'.$fieldName;

                /** @var Image $image */
                $image = $page->$getter();

                if ($image) {
                    if ($image->getFile()) {
                        // update the Image to trigger file management
                        $image->refreshUpdated();
                    } elseif (!$image->getFile() && !$image->getPath()) {
                        // prevent Sf/Sonata trying to create and persist an empty Image
                        $page->$setter(null);
                    }
                }
            }
        }
    }
}



