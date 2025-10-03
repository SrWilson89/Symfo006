<?php

namespace App\Controller;


use App\Entity\Estados;
use App\Entity\Clientes;
use App\Form\Type\EstadosType;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


final class EstadoController extends SuperController
{
    #[Route('/estado', name: 'app_add_estado')]
    #[Route('/estado/{id}', name: 'app_edit_estado')]
    public function estado(Estados $object = null, request $request): Response
    {   
        $this->globals['breadcrums'][] = "Empresa";
        $isNew = false;
        $cliente = $this->em->getRepository(Clientes::class)->findOneBy([]);

       if($object == null) {
        $object = new Estados();
        $object->setCliente($cliente);
        $isNew = true;
       }

        $form = $this->createForm(
            EstadosType::class,
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
                    'Estado guardado correctamente!'
                );
            } else {
                $this->addFlash(
                    'warning',
                    'Estado actualizado correctamente!'
                );
            }

            
            // $this->addFlash(
            // 'notice',
            // 'Your changes were saved!'
            // );

            return $this->redirectToRoute('app_edit_estado', ['id' => $object->getId()]);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

        return $this->render('entities/estados.html.twig', [
            'form' => $form,
            'globals' => $this->globals
        ]);
    }

     #[Route('/estado/delete/{id}', name: 'app_delete_estado')]
    public function estado_delete(Estados $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Estado eliminado correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'Estado']);
    }
}