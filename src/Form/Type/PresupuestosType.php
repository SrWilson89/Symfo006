<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Detalles;
use App\Entity\Presupuestos;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\QueryBuilder;


class PresupuestosType extends AbstractType
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
            ->add('estado', ChoiceType::class, [
                'choices' => [
                    'abierto' => 'Abierto',
                    'cerrado' => 'Cerrado',
                    'facturado' => 'Facturado',
                ],
                'placeholder' => 'Seleccione un estado',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add(
                'direccion', 
                 TextType::class,
                ['label' => 'direccion',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]

            )
            ->add(
                    'tipo',
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
            //     ->add(
            //     'numref', 
            //      NumberType::class,
            //     ['label' => 'numref',
            //     'attr' => [
            //         'class' => 'form-control',
            //         'readonly' => true
            //             ]
            //         ]

            // )
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
            'data_class' => Presupuestos::class,
        ]);
    }
}