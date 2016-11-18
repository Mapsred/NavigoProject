<?php
/**
 * Created by PhpStorm.
 * User: Maps_red
 * Date: 11/10/2016
 * Time: 00:31
 */

namespace AppBundle\Admin;

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
            ->add('email', 'email')
            ->end()
            ->with("Statut", ['class' => "col-md-6"])
            ->add('enabled', 'checkbox', ['label' => 'Activé'])
            ->end()
            ->end();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('username')->add('email');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username', null, ['label' => 'Nom d\'utilisateur'])
            ->add('email')
            ->add('enabled', null, ['label' => 'Activé'])
            ->add('locked', null, ['label' => 'Bloqué'])
            ->add('created_at', 'datetime', ['label' => 'Créé le'])

        ;
    }

}



