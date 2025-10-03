<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Productos;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;


class ProductosType extends AbstractType
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
                'precio', 
                 NumberType::class,
                ['label' => 'precio',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]

            )
            ->add(
                'iva', 
                 NumberType::class,
                ['label' => 'iva',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]

            )
            ->add(
                'Fechacreacion', 
                DateType::class,
                ['label' => 'Fechacreacion', 
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
            'data_class' => Productos::class,
        ]);
    }
}
