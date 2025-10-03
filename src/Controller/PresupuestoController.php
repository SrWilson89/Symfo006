<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PresupuestosRepository;

use App\Entity\Detalles;
use App\Entity\Presupuestos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Utils\Paginator;

use App\Form\Type\PresupuestosType;

final class PresupuestoController extends SuperController
{
    #[Route('/presupuesto', name: 'app_add_presupuesto')]
    #[Route('/presupuesto/{id}', name: 'app_edit_presupuesto')]
    public function presupuesto(Presupuestos $object = null, request $request): Response
    {
       $this->globals['breadcrums'][] = "Empresa";
        $isNew = false;
        $detalle = $this->em->getRepository(Detalles::class)->findOneBy([]);

       if($object == null) {
        $object = new Presupuestos();
         // Obtener el último numref asignado
        $ultimoNumref = $this->em->getRepository(Presupuestos::class)
            ->createQueryBuilder('p')
            ->select('MAX(p.numref)')
            ->getQuery()
            ->getSingleScalarResult();

        $nuevoNumref = $ultimoNumref ? $ultimoNumref + 1 : 1;
        $object->setNumref($nuevoNumref);
        $object->setFechacreacion(new \DateTime());
        $object->setModificacion(new \DateTime());
        $object->setDetalle($detalle);
        $isNew = true;
       }

        $form = $this->createForm(
            PresupuestosType::class,
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
                    'Presupuesto guardado correctamente!'
                );
            } else {
                $this->addFlash(
                    'warning',
                    'Presupuesto actualizado correctamente!'
                );
            }

            
            // $this->addFlash(
            // 'notice',
            // 'Your changes were saved!'
            // );

            return $this->redirectToRoute('app_edit_presupuesto', ['id' => $object->getId()]);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';

        return $this->render('entities/presupuestos.html.twig', [
            'form' => $form,
            'globals' => $this->globals
        ]);
    }


    
    #[Route('/presupuesto/delete/{id}', name: 'app_delete_presupuesto')]
    public function presupuesto_delete(Detalles $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'presupuesto eliminado correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'Presupuesto']);
    }


}