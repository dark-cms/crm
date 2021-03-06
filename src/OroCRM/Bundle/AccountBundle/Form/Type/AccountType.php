<?php

namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

class AccountType extends FlexibleType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        // name
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'Name',
                'required' => true,
            )
        );

        // tags
        $builder->add(
            'tags',
            'oro_tag_select'
        );

        // contacts
        $builder
            ->add(
                'appendContacts',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroCRMContactBundle:Contact',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            )
            ->add(
                'removeContacts',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroCRMContactBundle:Contact',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            );

        // addresses
        $builder
            ->add(
                'shippingAddress',
                'oro_address',
                array(
                    'cascade_validation' => true,
                    'required' => false
                )
            )
            ->add(
                'billingAddress',
                'oro_address',
                array(
                    'cascade_validation' => true,
                    'required' => false
                )
            );
    }

    /**
     * Add entity fields to form builder
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            'collection',
            array(
                'type'         => $this->valueFormAlias,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr'          => array(
                    'data-col'  => 2,
                ),
                'cascade_validation' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->flexibleClass,
                'intention' => 'account',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_account';
    }
}
