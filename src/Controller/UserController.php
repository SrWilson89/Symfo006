<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\Type\UserType;
use App\Entity\Clientes;
use App\Repository\UserRepository;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends SuperController
{
    #[Route('/user', name: 'app_add_user')]
    #[Route('/user/{id}', name: 'app_edit_user')]
    public function usuario(User $object = null, request $request, UserPasswordHasherInterface $passwordHasher): Response
    {   
        $this->globals['breadcrums'][] = "Empresa";
    $isNew = false;
    $cliente = $this->em->getRepository(Clientes::class)->findOneBy([]);
    $this->passwordHasher = $passwordHasher;

    if ($object == null) {
        $object = new User();
        $object->setFechaCreacion(new \DateTime);
        $object->setModificacion(new \DateTime);
        $object->setCliente($cliente);
        $object->setActivo(1);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $object,
            '123456'
        );
        $object->setPassword($hashedPassword);
        $isNew = true;
    }

    $form = $this->createForm(
        UserType::class,
        $object,
        [
            'user' => $this->getUser(),
            'cliente' => $cliente,
            'method' => 'POST', 
            'attr' => ['class' => 'form-horizontal form-bordered']
        ]
    );

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Manejo del archivo imagen
        /** @var UploadedFile $imagenFile */
        $imagenFile = $form->get('imagen')->getData();

        if ($imagenFile) {
            $nuevoNombre = uniqid().'.'.$imagenFile->guessExtension();

            // Mueve la imagen a la carpeta pública 'uploads/perfiles'
            $imagenFile->move(
                $this->getParameter('perfiles_directory'), // Parámetro que defines en config/services.yaml
                $nuevoNombre
            );

            // Guarda el nombre en la entidad User
            $object->setImagen($nuevoNombre);
        }

        $this->em->persist($object);
        $this->em->flush();

        if ($isNew){
            $this->addFlash('warning', 'Usuario guardado correctamente!');
        } else {
            $this->addFlash('warning', 'Usuario actualizado correctamente!');
        }

        return $this->redirectToRoute('app_edit_user', ['id' => $object->getId()]);
    }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

        return $this->render('entities/user.html.twig', [
            'form' => $form,
            'globals' => $this->globals
        ]);
    }

     #[Route('/user/delete/{id}', name: 'app_delete_user')]
    public function clientes_delete(User $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Usuario eliminado correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'User']);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $rq){
    
    $user = $this->getUser();
    $cliente = $user->getCliente();

    $companeros = $cliente->getUsuarios();

    // Excluir al usuario actual
    $companerosSinActual = [];
    foreach ($companeros as $item) {
        if ($item->getId() != $user->getId())
        {
            $companerosSinActual[] = $item;
        }
    }


    return $this->render('page-user-profile.html.twig', [
        'companeros' => $companerosSinActual,
        'user' => $user,
        'cliente' => $cliente
    ]);
    } 
           
}