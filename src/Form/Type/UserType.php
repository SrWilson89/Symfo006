<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Clientes;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['cliente'];

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
                'apellidos', 
                TextType::class,
                ['label' => 'Apellidos:',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]

                )
            ->add(
                'nif', 
                TextType::class,
                ['label' => 'Nif',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'telefono', 
                TextType::class,
                ['label' => 'Telefono',
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
                'email', 
                TextType::class,
                ['label' => 'Email',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'password', 
                TextType::class,
                ['label' => 'Password', 
                'attr' => [
                    'required' => false,
                    'class' => 'form-control'
                        ]
                    ]
                )
            ->add(
                'super', 
                TextType::class,
                ['label' => 'Super', 
                'attr' => [
                    'required' => false,
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
                ->add('imagen', FileType::class, [
                    'label' => 'Foto de perfil',
                    'mapped' => false,  // No mapea directamente con la entidad
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/img',
                            ],
                            'mimeTypesMessage' => 'Por favor sube una imagen válida (jpeg o png)',
                            ])
                        ],
                    ]
                )
            ->add(
            'cliente',
            EntityType::class,
            [
                'class' => Clientes::class,
                'choice_label' => function ($object) {
                    return ($object != null) ? $object->getNombre() : 'SIN RESPUESTA';
                },
                'query_builder' => function (EntityRepository $er) use ($user): QueryBuilder {
                    $qb = $er->createQueryBuilder('c');
                    if($user !== null){
                        $qb->where('c.id = :cliente')
                        ->setParameter('cliente', $user);
                    }
                    return $qb->orderBy('c.nombre', 'ASC');
                },
                'attr' => ['class' => 'form-control populate', 'data-plugin-selectTwo' => '']
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
            'data_class' => User::class,
             'user' => null,
             'cliente' => null
        ]);
    }
}