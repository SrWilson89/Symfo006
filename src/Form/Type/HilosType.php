<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Hilos;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;


class HilosType extends AbstractType
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
                'usuario',
                EntityType::class,
                [ 'class' => User::class,
                'choice_label' => function($object) {
                    return ($object != null) ? $object->getNombre():'SIN RESPUESTA';
                },
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nombre', 'ASC');
                },
                'attr' => ['class' => 'form-control']
                ]
            )
            ->add(
                'notas', 
                 TextType::class,
                ['label' => 'notas:',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]

                )
            ->add(
                'FechaCreacion', 
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
            'data_class' => Hilos::class,
        ]);
    }
}