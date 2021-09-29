<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction('/register')
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email:',
                'attr' => [
                    'placeholder' => 'Votre email'
                ]
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'Prénom:',
                'attr' => [
                    'placeholder' => 'Votre prénom'
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'Nom:',
                'attr' => [
                    'placeholder' => 'Votre nom'
                ]
            ])
            ->add('pseudonym', TextType::class, [
                'required' => true,
                'label' => 'Pseudonyme:',
                'attr' => [
                    'placeholder' => 'Comment aimeriez-vous être appelé ?'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label'=>'Mot de passe:',

                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Votre mot de passe',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit avoir au minimum {{ limit }} caractères.',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
