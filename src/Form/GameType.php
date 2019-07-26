<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\GameInterface;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    protected $currentUserId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->currentUserId = $options['current_user_id'];
        
        $builder
            ->add('users', EntityType::class, array(
                'class'    => User::class,
                'choice_label' => 'displayName',
                'label' => false,
                'expanded' => false,
                'multiple' => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id != :current_user_id')
                        ->andWhere('u.enabled = 1')
                        ->orderBy('u.firstname', 'ASC')
                        ->setParameter('current_user_id',$this->currentUserId);
                },
            ))
            ->add('winner',
                ChoiceType::class,
                [
                'choices'  => [
                            'Player1' => 1,
                            'Player2' => 2
                        ],
                'placeholder' => false,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'label' => false
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Report Game']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GameInterface::class,
            'current_user_id' => null,
        ]);
    }
}
