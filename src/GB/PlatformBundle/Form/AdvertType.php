<?php

namespace GB\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('date',      'date')
        ->add('title',     'text')
        ->add('author',    'text')
        ->add('content',   'textarea')
        ->add('published', 'checkbox', array('required' => false))
        ->add('image',      new ImageType())
        ->add('categories', 'entity', array(
            'class'         => 'GBPlatformBundle:Category',
            'property'      => 'name',
            'multiple'      => true
            ))
        ->add('save',      'submit');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GB\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'gb_platformbundle_advert';
    }

    /*public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          'data_class' => 'GB\PlatformBundle\Entity\Advert'
        ));
    }

    public function getName()
    {
        return 'gb_platformbundle_advert';
    }*/
}
