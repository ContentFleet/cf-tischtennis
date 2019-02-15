<?php
/**
 * Created by PhpStorm.
 * User: lambeletjp
 * Date: 24.01.19
 * Time: 15:43
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(!isset($options['choices'])){
            $options['choices'] = [
                '3-0' => '3-0',
                '3-1' => '3-1',
                '3-2' => '3-2',
                '2-3' => '2-3',
                '1-3' => '1-3',
                '0-3' => '0-3',
            ];
        }
        $builder->add('score',
                ChoiceType::class,
                [
                    'choices'  => $options['choices'],
                    'placeholder' => false,
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                    'label' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [],
        ]);
    }
}