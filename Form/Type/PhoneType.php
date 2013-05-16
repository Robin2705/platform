<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;

class PhoneType extends CollectionItemAbstract
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('data', 'text');
        $builder->add(
            'type',
            'choice',
            array(
                'empty_value'   => 'Choose phone type...',
                'empty_data'    => null,
                'choice_list'   => new ChoiceList(
                    array_keys(self::getTypesArray()),
                    array_values(self::getTypesArray())
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTypesArray()
    {
        return array(
            1 => 'Test phone type'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_phone';
    }
}
