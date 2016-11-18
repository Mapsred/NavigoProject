<?php
/**
 * Created by PhpStorm.
 * User: Maps_red
 * Date: 11/10/2016
 * Time: 00:31
 */

namespace AppBundle\Admin;

use Sonata\DoctrineORMAdminBundle\Admin\FieldDescription;
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
        $fileFieldOptions = ['label' => 'Image', 'required' => false];
        $user = $this->getSubject();
        if ($user && ($path = $user->getPath())) {
            $container = $this->getConfigurationPool()->getContainer();
            $fullPath = $container->get('request_stack')->getCurrentRequest()->getBasePath().'/uploads/images/'.$path;
            $fileFieldOptions['sonata_help'] = '<img src="'.$fullPath.'" class="admin-preview" />';
        }

        $formMapper
            ->tab('Utilisateur')
            ->with("Profil", ['class' => "col-md-6"])
            ->add('username', 'text', ['label' => 'Nom d\'utilisateur'])
            ->end()
            ->with("Statut", ['class' => "col-md-6"])
            ->add('enabled', 'checkbox', ['label' => 'Activé'])
            ->end()
            ->with("Image")
            ->end();

        $formMapper->add('file', 'file', $fileFieldOptions);
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
            ->add('created_at', 'datetime', ['label' => 'Créé le']);
    }

    /**
     * @param User $user
     */
    public function prePersist($user)
    {
        $this->manageFileUpload($user);
    }

    /**
     * @param User $user
     */
    private function manageFileUpload($user)
    {
        if ($user->getFile()) {
            $user->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @param User $user
     */
    public function preUpdate($user)
    {
        $this->manageFileUpload($user);
    }
}



