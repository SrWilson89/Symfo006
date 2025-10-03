<?php

namespace App\Controller;


use App\Entity\Hilos;
use App\Entity\User;
use App\Entity\Tareas;
use App\Form\Type\HilosType;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


final class HiloController extends SuperController
{
    #[Route('/hilo', name: 'app_add_hilo')]
    #[Route('/hilo/{id}', name: 'app_edit_hilo')]
    public function hilo(Hilos $object = null, request $request): Response
    {   
        $this->globals['breadcrums'][] = "Empresa";
        $isNew = false;
        $tarea = $this->em->getRepository(Tareas::class)->findOneBy([]);

       if($object == null) {
        $object = new Hilos();
        $object->setTarea($tarea);
        $isNew = true;
       }

        $form = $this->createForm(
            HilosType::class,
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
                    'Hilo guardado correctamente!'
                );
            } else {
                $this->addFlash(
                    'warning',
                    'Hilo actualizado correctamente!'
                );
            }

            
            // $this->addFlash(
            // 'notice',
            // 'Your changes were saved!'
            // );

            return $this->redirectToRoute('app_edit_hilo', ['id' => $object->getId()]);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

    
        return $this->render('entities/hilo.html.twig', [
            'form' => $form,
            'globals' => $this->globals
        ]);
    }

     #[Route('/hilo/delete/{id}', name: 'app_delete_hilo')]
    public function estado_delete(Estados $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Hilo eliminado correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'Hilo']);
    }
}