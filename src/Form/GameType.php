<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\GameSet;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                'allow_add'    => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => false
            ])
            ->add('users', EntityType::class, array(
                'class'    => User::class,
                'choice_label' => 'displayName',
                'label' => "Opponent",
                'expanded' => false,
                'multiple' => true
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
