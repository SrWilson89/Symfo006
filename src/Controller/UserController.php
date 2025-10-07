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
    // RUTA CORREGIDA: 'app_add_usuario' y 'app_edit_usuario'
    #[Route('/usuario', name: 'app_add_usuario')]
    #[Route('/usuario/{id}', name: 'app_edit_usuario')]
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

        $form = $this->createForm(UserType::class, $object);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $this->em->persist($object);
                $this->addFlash('success', 'Usuario creado correctamente!');
            }
            $object->setModificacion(new \DateTime);
            $this->em->flush();

            if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
            else $this->globals['breadcrums'] = 'Editar';

            $this->addFlash('warning', 'Usuario actualizado correctamente!');
            
            // CORRECCIÓN: Usar la ruta recién definida 'app_edit_usuario' y redirigir al listado.
            // Es más común redirigir al listado después de editar.
            return $this->redirectToRoute('app_list', ['entity' => 'usuarios']);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

        return $this->render('entities/user.html.twig', [
            'form' => $form->createView(), // Añadir .createView()
            'globals' => $this->globals
        ]);
    }

    // RUTA CORREGIDA: 'app_delete_usuario'
    #[Route('/usuario/delete/{id}', name: 'app_delete_usuario')]
    public function clientes_delete(User $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Usuario eliminado correctamente!'
        );
        
        // CORRECCIÓN: Redirigir al listado usando el nombre de entidad 'usuarios' (plural)
         return $this->redirectToRoute('app_list', ['entity' => 'usuarios']);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $rq): Response // Añadir ': Response'
    {
    
        $user = $this->getUser();
        
        // Comprobación para evitar errores si el usuario no tiene cliente asociado
        $cliente = $user ? $user->getCliente() : null;

        $companeros = $cliente ? $cliente->getUsuarios() : [];

        // Excluir al usuario actual
        $companerosSinActual = [];
        foreach ($companeros as $item) {
            if ($item->getId() !== $user->getId()) { // Usar !== para comparación estricta
                $companerosSinActual[] = $item;
            }
        }
        
        // ... (resto de tu lógica)

        // Asumo que tienes un template para el perfil
        return $this->render('page-user-profile.html.twig', [
            'user' => $user,
            'companeros' => $companerosSinActual,
            'globals' => $this->globals
            // ... otras variables necesarias
        ]);
    }
}