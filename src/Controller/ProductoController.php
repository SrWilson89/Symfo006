<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductosRepository;

use App\Entity\Productos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Utils\Paginator;

use App\Form\Type\ProductosType;

final class ProductoController extends SuperController
{
    #[Route('/producto', name: 'app_add_producto')]
    #[Route('/producto/{id}', name: 'app_edit_producto')]
    public function producto(Productos $object = null, request $request): Response
    {
       $this->globals['breadcrums'][] = "Empresa";
        $isNew = false;

       if($object == null) {
        $object = new Productos();
        $object->setFechacreacion(new \DateTime);
        $object->setModificacion(new \DateTime);
        $isNew = true;
       }

        $form = $this->createForm(
            ProductosType::class,
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
                    'Producto guardado correctamente!'
                );
            } else {
                $this->addFlash(
                    'warning',
                    'Producto actualizado correctamente!'
                );
            }

            
            // $this->addFlash(
            // 'notice',
            // 'Your changes were saved!'
            // );

            return $this->redirectToRoute('app_edit_producto', ['id' => $object->getId()]);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

        return $this->render('entities/productos.html.twig', [
            'form' => $form,
            'globals' => $this->globals
        ]);
    }


    
    #[Route('/producto/delete/{id}', name: 'app_delete_producto')]
    public function producto_delete(Productos $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Producto eliminado correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'Producto']);
    }


}
    
