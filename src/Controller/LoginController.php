<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\User;
use App\Entity\Clientes;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;

final class LoginController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        Request $rq,
        Security $security
        ): Response
    {
        $form = $this->createFormBuilder()

            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('password', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('nombre', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('cif', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('direccion', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('codigopostal', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('localidad', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('provincia', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('nombre_user', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('nif_user', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('apellido_user', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add('telefono_user', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'required' => true
            ])
            ->add(
                'save', 
                SubmitType::class,
                ['label' => 'Registrar',
                'attr' => [
                    'class' => 'mb-1 mt-1 btn btn-dark mt-2'
                        ]
                    ]
           )
            ->getForm();

            $form->handleRequest($rq);
            if ($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
                if ($existingUser) {
                    $form->addError(new \Symfony\Component\Form\FormError('El email ya esta registrado.'));
                }

                $cliente = new Clientes();
                $cliente->setNombre($data['nombre']);
                $cliente->setCif($data['cif']);
                $cliente->setDireccion($data['direccion']);
                $cliente->setCodigoPostal($data['codigopostal']);
                $cliente->setLocalidad($data['localidad']);
                $cliente->setProvincia($data['provincia']);
                $cliente->setPais('España');
                $em->persist($cliente);

                $user = new User();
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $data['password']
                );
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_CUSTOMER', 'ROLE_USER']);
                $user->setNombre($data['nombre_user']);
                $user->setApellidos($data['apellido_user']);
                $user->setNif($data['nif_user']);
                $user->setEmail($data['email']);
                $user->setTelefono($data['telefono_user']);
                $user->setActivo(1);
                $user->setSuper(1);
                $user->setCliente($cliente);
                $em->persist($user);

                $em->flush();

                $security->login($user);

                return $this->redirectToRoute('app_index');
            }

        return $this->render('/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

     #[Route('/login', name: 'app_login')]
     public function login (
        EntityManagerInterface $em,
        AuthenticationUtils $au
        ): Response
    {
        

        return $this->render('/login.html.twig', [
            'last_username' => $au->getLastUsername(),
            'error' => $au->getLastAuthenticationError(),
        ]);
    }   



    #[Route('/recovery', name: 'app_recovery')]
    public function recovery(EntityManagerInterface $em): Response
    {
        return $this->render('/recovery.html.twig', []);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(EntityManagerInterface $em): Response
    {
        
    }
}
