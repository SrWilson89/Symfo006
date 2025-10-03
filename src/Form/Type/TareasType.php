<?php
// src/Form/Type/ProductType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Tareas;
use App\Entity\Clientes;
use App\Entity\User;
use App\Entity\Estados;


use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;


class TareasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $object = $builder->getData();
        //$tarea = $options['Tarea'];
        if ($object->getUsuario() === null && $user !== null){
            $object->getUsuario($user);
        }
        $builder
            ->add(
                
                'estado',
                EntityType::class, [
                'class' => Estados::class,
                'choice_label' => fn($estado) => $estado->getNombre(),
                'query_builder' => function (EntityRepository $er) use ($object): QueryBuilder{
                    $qb = $er->createQueryBuilder('e')->orderBy('e.Nombre', 'ASC');
                    if ($object->getCliente()){
                        $qb->where('e.cliente = :cliente')
                            ->setParameter('cliente', $object->getCliente());
                    } else {
                        $qb->where('1=0');
                    }
                    return $qb;
                },
                  
                'attr' => [
                    'required' => true,
                    'class' => 'form-control populate', 'data-plugin-selectTwo' => ''
                ],
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
                'titulo', 
                 textType::class,
                ['label' => 'Titulo:',
                'attr' => [
                    'class' => 'form-control'
                        ]
                    ]    

                )
            ->add(
                'notas',
                textAreaType::class,
                    ['label' => 'Notas',
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
            'data_class' => Tareas::class,
            //'Tarea' => null
            'user' => null
        ]);
    }
}