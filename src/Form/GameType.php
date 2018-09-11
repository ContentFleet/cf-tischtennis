<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\GameSet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sets', CollectionType::class, [
                'entry_type' => GameSetType::class,
                'entry_options' => array('label' => false),
                'allow_add'    => true,
                'by_reference' => false,
                'allow_delete' => true
            ])
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
