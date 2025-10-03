<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Clientes;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class ClientesType extends AbstractType
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
                ['label' => 'Nombre:', 
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'direccion', 
                TextType::class,
                ['label' => 'Direccion:',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]

                )
            ->add(
                'codigo_postal', 
                TextType::class,
                ['label' => 'Codigo_Postal',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'pais', 
                TextType::class,
                ['label' => 'Pais',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'localidad', 
                TextType::class,
                ['label' => 'Localidad',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'notas', 
                TextareaType::class,
                ['label' => 'Notas',
                'attr' => [
                    'class' => 'form-control',
                    'required' => false
                        ]
                    ]
                )
            ->add(
                'cif', 
                TextType::class,
                ['label' => 'Cif',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'FechCreacion', 
                DateType::class,
                ['label' => 'Fecha_Creacion', 
                'attr' => [
                    'readonly' => true,
                    'required' => false,
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'Modificacion', 
                DateType::class,
                ['label' => 'Modificacion', 
                'attr' => [
                    'readonly' => true,
                    'required' => false,
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'provincia', 
                TextType::class,
                ['label' => 'Provincia',
                'attr' => [
                    'class' => 'form-control',
                    'required' => false
                        ]
                    ]
                )
            ->add(
                    'activo',
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
            'data_class' => Clientes::class,
        ]);
    }
}