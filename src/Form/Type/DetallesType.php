<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Detalles;
use App\Entity\Productos;

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


class DetallesType extends AbstractType
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
                'producto',
                EntityType::class,
                [ 'class' => Productos::class,
                'choice_label' => function($object) {
                    return ($object != null) ? $object->getNombre():'SIN RESPUESTA';
                },
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.nombre', 'ASC');
                },
                'attr' => ['class' => 'form-control',
                'readonly' => true]
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
                'cantidad', 
                 NumberType::class,
                ['label' => 'cantidad',
                'attr' => [
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
            'data_class' => Detalles::class,
        ]);
    }
}