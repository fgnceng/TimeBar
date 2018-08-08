<?php
namespace App\Form;

use App\Entity\CommentUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextareaType::class, array(
                'label' => 'Name:',
                'attr' => array(
                    'placeholder' => 'Enter your name...',

                )
            ))
            ->add('surname', TextareaType::class,array(
                'label'=>'Surname:',
                'attr' => array(
                    'placeholder' => 'Enter your surname...',

                )
            ))

            ->add('comment', TextareaType::class,array(
                'label'=>'Comment:',
                'attr'=>array(
                    'placeholder'=>'Enter your comment',
                )
            ))

            ->add('saveComment',SubmitType::class,['label'=>' Add Comment',
            'attr'=>array(

            )
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommentUser::class,
        ]);
    }
}