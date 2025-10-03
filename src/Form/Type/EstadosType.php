<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Estados;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;


class EstadosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add(
                'id', 
                TextType::class,
                ['label' => 'ID:',  
                'attr' => [
                    'required' => false,
                    'class' => 'form-control',
                    'readonly' => true
                        ]
                    ]
                )
            ->add(
                'nombre',
                TextType::class,
                ['label' => 'nombre',  
                'attr' => [
                    'required' => false,
                    'class' => 'form-control',
                        ]
                    ]
            )
            ->add(
                'color', 
                 ColorType::class,
                ['label' => 'Color:',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]

            )
            ->add(
                    'fin',
                    CheckboxType::class,
                    [
                        'required' => false,
                        /*'label' => 'Activo',
                        'choices' => [
                            'Activo' => true,
                            'No activo' => false,
                        ],
                        'expanded' => true,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'form-check-input'
                        ]*/
                    ]
                )
            ->add(
                'save', 
                SubmitType::class,
                ['label' => 'Enviar',
                'attr' => [
                    'class' => 'mb-1 mt-1 btn btn-success float-end'
                        ]
                    ]
           )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Estados::class,
        ]);
    }
}