<?php
/**
 * Created by PhpStorm.
 * User: iyimakina
 * Date: 9.08.2018
 * Time: 10:52
 */


namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Tests\Extension\Core\Type\CheckboxTypeTest;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'attr' => array(
                    'placeholder' => 'Enter your email',

                )
            ))
            ->add('username', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Enter your username',
                )
            ))
            ->add('plainPassword', RepeatedType::class, array(
                'attr' => array(
                    'placeholder' => 'Enter your Password'),

                'type' => PasswordType::class,
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('bulletin', CheckboxType::class, array(
                    'label' => 'Do you want to sign up for e newsletter?',
                    'required' => false,
                ))

            ->add('roles', ChoiceType::class, array(
                'attr' => array(
                    'required' => false,
                ),
                'multiple' => true,
                'expanded' => true, // render check-boxes
                'choices' => [
                    'admin' => 'ROLE_ADMIN',
                    'user' => 'ROLE_USER',
                ]
            )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}