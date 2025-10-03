<?php

namespace App\Controller;


use App\Entity\Estados;
use App\Entity\Clientes;
use App\Entity\Tareas;
use App\Entity\User;
use App\Entity\Hilos;
use App\Form\Type\TareasType;
use App\Utils\Paginator;



use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


final class TareaController extends SuperController
{
    #[Route('/tarea', name: 'app_add_tarea')]
    #[Route('/tarea/{id}', name: 'app_edit_tarea')]
    public function tarea(Tareas $object = null, request $request): Response
    {   
        $this->globals['breadcrums'][] = "Empresa";
        $isNew = false;
        $path_to_add = "";
        
            if($object == null) {
                $object = new Tareas();
                $object->setFechaCreacion(new \DateTime);
                $object->setModificacion(new \DateTime);
                $managerUser = $this->em->getRepository(User::class)->find($this->getUser()->getId());
                $object->setUsuario($managerUser);
                $object->setCliente($managerUser->getCliente()->getId());
                $isNew = true;
            }
        

                $form = $this->createForm(
            TareasType::class,
            $object,
            [
                'method' => 'POST',
                'attr' => ['class' => 'form-horizontal form-bordered'],
                'user' => $this->getUser()
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $object = $form->getData();

            
            $estadoNombre = $object->getEstado()->getNombre();
            
            

            if ($estadoNombre === 'finalizado' && $object->getFechafin() === null) {
                $object->setFechafin(new \DateTime());  

            }

            $this->em->persist($object);
            $this->em->flush();

            $hilo = new Hilos();
            $hilo->setTarea($object);
            $hilo->setUsuario($this->getUser());

            $mensaje = '';

            switch ($estadoNombre) {
                case 'finalizado':
                    $mensaje = 'La tarea ha sido finalizada.';
                    break;

                case 'activo':
                    $mensaje = 'La tarea ha sido iniciada.';
                    break;

                case 'pendiente':
                $mensaje = 'La tarea esta pendiente';
                break;

                case 'inactivo':
                $mensaje = 'La tarea esta inactiva';
                break;

                default:
                    $mensaje = 'Estado cambiado a: ' . ucfirst($estadoNombre);
            }

            $hilo->setNotas($mensaje);
            $hilo->setFechaCreacion(new \DateTime());

            $this->em->persist($hilo);
            $this->em->flush();

            if ($isNew == true){
                $this->addflash('warning', 'Tarea guardada correctamente!');
            } else {
                $this->addFlash('warning', 'Tarea actualizada correctamente!');
            }

            $filters = [];
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', $request->request->get('limit', 10));
            $paginator = null;

            if($object != null){
                $paginator = new Paginator($this->em, Hilos::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles([
                        'id' => 'ID',
                        'tarea' => 'Tareas',
                        'usuario' => 'Usuario',
                        'notas' => 'Notas',
                        'fechacreacion' => 'Fecha_Creacion',
                        'modificacion' => 'Modificacion'
                    ])
                    ->setLinksGenerator(fn($entity) => [
                        [
                            'text' => 'EDITAR',
                            'url' => $this->generateUrl('app_edit_hilo', ['id' => $entity->getId()]),
                            'color' => 'primary',
                            'icon' => 'fa-edit',
                        ],
                        [
                            'text' => 'BORRAR',
                            'url' => $this->generateUrl('app_delete_hilo', ['id' => $entity->getId()]),
                            'color' => 'danger',
                            'icon' => 'fa-remove',
                        ]
                    ])
                    ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_hilo', ['id' => $entity->getId()]));
            }

            $path_to_add = $this->generateUrl('app_add_hilo');

            return $this->redirectToRoute('app_edit_tarea', ['id' => $object->getId()]);
        }

        if ($isNew == true) $this->globals['breadcrums'] = 'nuevo';
        else $this->globals['breadcrums'] = 'Editar';


        return $this->render('entities/tareas.html.twig', [
            'form' => $form,
            "path_to_add" => $path_to_add,
            'globals' => $this->globals,
            'object' => $object,
            
        ]);
    }

     #[Route('/tarea/delete/{id}', name: 'app_delete_tarea')]
    public function tarea_delete(Tareas $object, request $request): Response
    {   
        $this->em->remove($object);
        $this->em->flush();

        $this->addFlash(
            'warning',
            'Tarea eliminada correctamente!'
        );
        
         return $this->redirectToRoute('app_list', ['entity' => 'Tarea']);
    }
}