<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DetallesRepository;

use App\Entity\Detalles;
use App\Entity\Productos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Utils\Paginator;

use App\Form\Type\DetallesType;

final class DetalleController extends SuperController
{
    #[Route('/detalle', name: 'app_add_detalle')]
    #[Route('/detalle/{id}', name: 'app_edit_detalle')]
    public function detalle(Detalles $object = null, request $request): Response
    {
       $this->globals['breadcrums'][] = "Empresa";
        $isNew = false;
        $producto = $this->em->getRepository(Productos::class)->findOneBy([]);

       if($object == null) {
        $object = new Detalles();
        $object->setProducto($producto);
        $isNew = true;
       }

        $form = $this->createForm(
            DetallesType::class,
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
                    'Detalles guardado correctamente!'
                );
            } else {
                $this->addFlash(
                    'warning',
                    'Detalle actualizado correctamente!'
                );
            }

            
            // $this->addFlash(
            // 'notice',
            // 'Your changes were saved!'
            // );

            return $this->redirectToRoute('app_edit_detalle', ['id' => $object->getId()]);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

        return $this->render('entities/detalles.html.twig', [
            'form' => $form,
            'globals' => $this->globals
        ]);
    }


    
    #[Route('/detalle/delete/{id}', name: 'app_delete_detalle')]
    public function detalle_delete(Detalles $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Detalle eliminado correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'Detalle']);
    }


}