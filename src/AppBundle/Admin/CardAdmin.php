<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 18/11/16
 * Time: 11:44
 */

namespace AppBundle\Admin;


use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use UserBundle\Entity\Card;

class CardAdmin extends AbstractAdmin
{
    /**
     * @param Card|mixed $object
     * @return string
     */
    public function toString($object)
    {
        return $object instanceof Card ? $object->__toString() : "Carte";
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with("Carte navigo", ['class' => "col-md-6"])
            ->add('uuid', 'text', ['label' => 'UUID Navigo'])
            ->add("firstname", "text", ['label' => "Prenom"])
            ->add("lastname", "text", ['label' => "Nom"])
            ->end();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('uuid')->add("firstname")->add("lastname");
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('uuid', null, ['label' => 'UUID Navigo'])
            ->add("firstname", null, ['label' => "Prenom"])
            ->add('lastname', null, ['label' => 'Nom'])
            ->add('user.username', null, ['label' => 'Pseudo']);
    }
}