<?php

namespace App\Controller;


use App\Entity\Clientes;
use App\Form\Type\ClientesType;




use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


final class ClienteController extends SuperController
{
    #[Route('/cliente', name: 'app_add_cliente')]
    #[Route('/cliente/{id}', name: 'app_edit_cliente')]
    public function cliente(Clientes $object = null, request $request): Response
    {   
        $this->globals['breadcrums'][] = "Empresa";
        $isNew = false;

       if($object == null) {
        $object = new Clientes();
        $object->setFechCreacion(new \DateTime);
        $object->setModificacion(new \DateTime);
        $object->setActivo(1);
        $isNew = true;
       }

        $form = $this->createForm(
            ClientesType::class,
            $object,
            ['method' => 'POST', 'attr' => ['class' => 'form-horizontal form-bordered']]
            );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $object = $form->getData();
            $this->em->persist($object);
            $this->em->flush();

            if ($isNew == true){
                
                $this->addflash(
                    'warning',
                    'Empresa guardada correctamente!'
                );
            } else {
                $this->addFlash(
                    'warning',
                    'Empresa actualizada correctamente!'
                );
            }

            
            // $this->addFlash(
            // 'notice',
            // 'Your changes were saved!'
            // );

            return $this->redirectToRoute('app_edit_cliente', ['id' => $object->getId()]);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

        return $this->render('entities/clientes.html.twig', [
            'form' => $form,
            'globals' => $this->globals
        ]);
    }

     #[Route('/cliente/delete/{id}', name: 'app_delete_cliente')]
    public function clientes_delete(Clientes $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Empresa eliminada correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'Cliente']);
    }
}