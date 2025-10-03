<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Clientes;
use App\Entity\User;
use App\Entity\Estados;
use App\Entity\Tareas;
use App\Entity\Productos;
use App\Entity\Detalles;
use App\Entity\Presupuestos;

use App\Utils\Paginator;

final class ListController extends SuperController
{
    #[Route('/list/{entity}', name: 'app_list')]
    public function list(string $entity, Request $rq): Response
    {
        
        $this->globals['breadcrumbs'][] = 'Listado';

        $path_to_add = "";
        $method = $rq->getMethod();
        $filters = [];

        $page = (int) $rq->query->get('page', 1);
        $limit = (int) $rq->query->get('limit', $rq->request->get('limit', 10));
        $format = $rq->query->get('format'); // <- NUEVO: recogemos formato desde query string

        $isExport = in_array($format, ['pdf', 'excel']);
        if ($isExport) {
            $limit = null; // Exporta todo
        }

        if ($method === 'POST') {
            $filters = $rq->request->all('filters', []);
        }

        $pagination = [];
        $paginator = null;

        switch ($entity) {
            case 'cliente':
                $paginator = new Paginator($this->em, Clientes::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles([
                        'id' => 'ID',
                        'activo' => ['title' => 'Activo', 'type' => 'bool'],
                        'nombre' => 'Nombre',
                        'cif' => 'CIF',
                        'codigopostal' => 'Cod Postal',
                        'pais' => 'Pais',
                        'localidad' => 'Localidad',
                        'provincia' => 'Provincia',
                        'fechcreacion' => 'Creacion',
                        'modificacion' => 'Modificacion'
                    ]);

                if (!$isExport) {
                    $paginator
                        ->setLinksGenerator(fn($entity) => [
                            [
                                'text' => 'EDITAR',
                                'url' => $this->generateUrl('app_edit_cliente', ['id' => $entity->getId()]),
                                'color' => 'primary',
                                'icon' => 'fa-edit',
                            ],
                            [
                                'text' => 'BORRAR',
                                'url' => $this->generateUrl('app_delete_cliente', ['id' => $entity->getId()]),
                                'color' => 'danger',
                                'icon' => 'fa-remove',
                            ]
                        ])
                        ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_cliente', ['id' => $entity->getId()]));
                }

                $this->globals['nombre'] = "Listado Clientes";
                $this->globals['breadcrumbs'][] = 'Clientes';
                $path_to_add = $this->generateUrl('app_add_cliente');
                break;

            case 'User':
                if ($this->getUser()->getSuper() !== 0){
                    $filters['cliente'] = $this->getUser()->getCliente();
                }
                $FieldTitles = [];
                if ($this->getUser()->getSuper() == 0){

                    $FieldTitles = [
                        'id' => 'ID',
                        'activo' => ['title' => 'Activo', 'type' => 'bool'],
                        'cliente' => 'Empresa',
                        'super' => 'Super',
                    ];
                 }
                 $FieldTitles += [
                        'nombre' => 'Nombre',
                        'apellidos' => 'Apellidos',
                        'nif' => 'Nif',
                        'email' => 'Email',
                        'telefono' => 'Teléfono',
                        'fechacreacion' => 'Creacion',
                        'modificacion' => 'Modificacion'
                    ];
                $paginator = new Paginator($this->em, User::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles($FieldTitles);
        
                if (!$isExport) {
                    $paginator
                        ->setLinksGenerator(function ($entity) {
                            $links = [
                                [
                                    'text' => 'EDITAR',
                                    'url' => $this->generateUrl('app_edit_user', ['id' => $entity->getId()]),
                                    'color' => 'primary',
                                    'icon' => 'fa-edit',
                                ],
                                [
                                    'text' => 'BORRAR',
                                    'url' => $this->generateUrl('app_delete_user', ['id' => $entity->getId()]),
                                    'color' => 'danger',
                                    'icon' => 'fa-remove',
                                ],
                            ];

                            if (
                                $this->isGranted('ROLE_ADMIN')
                                && $this->getUser()
                                && $this->getUser()->getId() !== $entity->getId()
                            ){
                                $links[] = [
                                    
                                    'text' => 'IMPERSONAR',
                                    'url' => $this->generateUrl('app_index', ['_switch_user' => $entity->getEmail()]),
                                    'color' => 'warning',
                                    'icon' => 'fa-user-secret',
                                ];

                            }
                            return $links;
                        })
                        ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_user', ['id' => $entity->getId()]));
                }

                $this->globals['nombre'] = "Listado Usuarios";
                $path_to_add = $this->generateUrl('app_add_user');
                $this->globals['breadcrumbs'][] = 'Usuario';
                break;

            case 'Estado':
                if ($this->getUser()->getSuper() !== 0){
                    $filters['cliente'] = $this->getUser()->getCliente();
                }
                $FieldTitles = [];

                if ($this->getUser()->getSuper() == 0){
                    
                    $FieldTitles = [
                        'id' => 'ID',
                        'cliente' => 'Empresa',
                        'nombre' => ['title' => 'Nombre', 'type' => 'badge'],
                        
                    ];
                } 
                    $FieldTitles += [
                        'cliente' => 'Empresa',
                        'nombre' => ['title' => 'Nombre', 'type' => 'badge'],
                        
                    ];

                $paginator = new Paginator($this->em, Estados::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles($FieldTitles);

                if (!$isExport) {
                    $paginator
                        ->setLinksGenerator(fn($entity) => [
                            [
                                'text' => 'EDITAR',
                                'url' => $this->generateUrl('app_edit_estado', ['id' => $entity->getId()]),
                                'color' => 'primary',
                                'icon' => 'fa-edit',
                            ],
                            [
                                'text' => 'BORRAR',
                                'url' => $this->generateUrl('app_delete_estado', ['id' => $entity->getId()]),
                                'color' => 'danger',
                                'icon' => 'fa-remove',
                            ]
                        ])
                        ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_estado', ['id' => $entity->getId()]));
                }

                $this->globals['nombre'] = "Listado de Estados";
                $this->globals['breadcrumbs'][] = 'Estados';
                $path_to_add = $this->generateUrl('app_add_estado');
                break;
            case 'Tarea':
                if ($this->getUser()->getSuper() !== 0){
                    $filters['cliente'] = $this->getUser()->getCliente();
                }
                $FieldTitles = [];
                /*if ($this->getUser()->getSuper() == 0){

                    $FieldTitles = [
                        'id' => 'ID',
                        'estado' => 'Estado',
                        'fechacreacion' => 'Fecha_Creacion',
                        'modificacion' => 'Modificacion',
                        'titulo' => 'Titulo',
                        'notas' => 'Notas',
                        'usuario' => 'Usuario'
                    ];
                }*/
                    //TODO Ajustar solo DEV
                    if ($this->getUser()->getSuper() == 0){
                    $FieldTitles = [
                        'id' => 'ID',
                        'estado' => 'Estado',
                        'fechacreacion' => 'Fecha_Creacion',
                        'modificacion' => 'Modificacion',
                        'titulo' => 'Titulo',
                        'notas' => 'Notas',
                        'usuario' => 'Usuario'
                    ];
                }
                    $FieldTitles += [
                        'estado' => 'Estado',
                        'fechacreacion' => 'Fecha_Creacion',
                        'modificacion' => 'Modificacion',
                        'titulo' => 'Titulo',
                        'notas' => 'Notas',
                        'usuario' => 'Usuario'
                    ];
                
                 
                
                $paginator = new Paginator($this->em, Tareas::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles($FieldTitles);

                if (!$isExport) {
                    $paginator
                        ->setLinksGenerator(fn($entity) => [
                            [
                                'text' => 'EDITAR',
                                'url' => $this->generateUrl('app_edit_tarea', ['id' => $entity->getId()]),
                                'color' => 'primary',
                                'icon' => 'fa-edit',
                            ],
                            [
                                'text' => 'BORRAR',
                                'url' => $this->generateUrl('app_delete_tarea', ['id' => $entity->getId()]),
                                'color' => 'danger',
                                'icon' => 'fa-remove',
                            ]
                        ])
                        ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_tarea', ['id' => $entity->getId()]));
                }

                $this->globals['nombre'] = "Listado de Tareas";
                $this->globals['breadcrumbs'][] = 'Tareas';
                $path_to_add = $this->generateUrl('app_add_tarea');
                break;

            case 'Producto':
                if ($this->getUser()->getSuper() !== 0){
                    $filters['cliente'] = $this->getUser()->getCliente();
                }
                $FieldTitles = [];

                if ($this->getUser()->getSuper() == 0){
                    
                    $FieldTitles = [
                        'id' => 'ID',
                        'nombre' => 'Nombre',
                        'precio' => 'Precio',
                        'iva' => 'Iva',
                        'fechacreacion' => 'FechaCreacion',
                        'modificacion' => 'Modificacion'
                        
                    ];
                } 
                    $FieldTitles += [
                        
                        
                    ];

                $paginator = new Paginator($this->em, Productos::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles($FieldTitles);

                if (!$isExport) {
                    $paginator
                        ->setLinksGenerator(fn($entity) => [
                            [
                                'text' => 'EDITAR',
                                'url' => $this->generateUrl('app_edit_producto', ['id' => $entity->getId()]),
                                'color' => 'primary',
                                'icon' => 'fa-edit',
                            ],
                            [
                                'text' => 'BORRAR',
                                'url' => $this->generateUrl('app_delete_producto', ['id' => $entity->getId()]),
                                'color' => 'danger',
                                'icon' => 'fa-remove',
                            ]
                        ])
                        ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_producto', ['id' => $entity->getId()]));
                }

                $this->globals['nombre'] = "Listado de Productos";
                $this->globals['breadcrumbs'][] = 'Productos';
                $path_to_add = $this->generateUrl('app_add_producto');
                break;

            case 'Detalle':
                if ($this->getUser()->getSuper() !== 0){
                    $filters['cliente'] = $this->getUser()->getCliente();
                }
                $FieldTitles = [];

                if ($this->getUser()->getSuper() == 0){
                    
                    $FieldTitles = [
                        'id' => 'ID',
                        'producto' => 'Producto',
                        'precio' => 'Precio',
                        'iva' => 'Iva',
                        'cantidad' => 'Cantidad',
                    ];
                } 
                    $FieldTitles += [];

                $paginator = new Paginator($this->em, Detalles::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles($FieldTitles);

                if (!$isExport) {
                    $paginator
                        ->setLinksGenerator(fn($entity) => [
                            [
                                'text' => 'EDITAR',
                                'url' => $this->generateUrl('app_edit_detalle', ['id' => $entity->getId()]),
                                'color' => 'primary',
                                'icon' => 'fa-edit',
                            ],
                            [
                                'text' => 'BORRAR',
                                'url' => $this->generateUrl('app_delete_detalle', ['id' => $entity->getId()]),
                                'color' => 'danger',
                                'icon' => 'fa-remove',
                            ]
                        ])
                        ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_detalle', ['id' => $entity->getId()]));
                }

                $this->globals['nombre'] = "Listado de Detalles";
                $this->globals['breadcrumbs'][] = 'Detalles';
                $path_to_add = $this->generateUrl('app_add_detalle');
                break;
            case 'Presupuesto':
                if ($this->getUser()->getSuper() !== 0){
                    $filters['cliente'] = $this->getUser()->getCliente();
                }
                $FieldTitles = [];

                if ($this->getUser()->getSuper() == 0){
                    
                    $FieldTitles = [
                        'id' => 'ID',
                        'direccion' => 'Direccion',
                        'fechacreacion' => 'FechaCreacion',
                        'modificacion' => 'Modificacion',
                        'detalle' => 'Detalle',
                        'tipo' => ['title' => 'Tipo', 'type' => 'bool'],
                        'numref' => 'NumRef',
                        'estado' => 'Estado'
                    ];
                } 
                    $FieldTitles += [];

                $paginator = new Paginator($this->em, Presupuestos::class);

                $paginator
                    ->setPage($page)
                    ->setLimit($limit)
                    ->setOrderBy('id', 'ASC')
                    ->setCriteria($filters)
                    ->setFieldTitles($FieldTitles);

                if (!$isExport) {
                    $paginator
                        ->setLinksGenerator(fn($entity) => [
                            [
                                'text' => 'EDITAR',
                                'url' => $this->generateUrl('app_edit_presupuesto', ['id' => $entity->getId()]),
                                'color' => 'primary',
                                'icon' => 'fa-edit',
                            ],
                            [
                                'text' => 'BORRAR',
                                'url' => $this->generateUrl('app_delete_presupuesto', ['id' => $entity->getId()]),
                                'color' => 'danger',
                                'icon' => 'fa-remove',
                            ]
                        ])
                        ->setRowClickUrlGenerator(fn($entity) => $this->generateUrl('app_edit_presupuesto', ['id' => $entity->getId()]));
                }

                $this->globals['nombre'] = "Listado de Presupuestos";
                $this->globals['breadcrumbs'][] = 'Presupuestos';
                $path_to_add = $this->generateUrl('app_add_presupuesto');
                break;    
            default:
                throw $this->createNotFoundException("Entidad '$entity' no es soportada");
                break;
        }

        $pagination = $paginator->paginate();

        if ($isExport) {
            if (!isset($pagination['field_titles']) || !isset($pagination['items'])) {
                throw new \Exception("No se encontraron datos para exportar");
            }

            $fieldKeys = array_keys($pagination['field_titles']);
            $headers = [];

            foreach ($pagination['field_titles'] as $key => $opt) {
                $headers[] = is_array($opt) ? $opt['title'] : $opt;
            }

            $data = [];
            foreach ($pagination['items'] as $itemData) {
                $entityObj = $itemData['entity'];
                $row = [];

                foreach ($fieldKeys as $field) {
                    $getter = 'get' . ucfirst($field);
                    $value = method_exists($entityObj, $getter) ? $entityObj->$getter() : null;
                    $type = $pagination['field_titles'][$field]['type'] ?? 'text';

                    switch ($type) {
                        case 'bool':
                            $value = $value ? 'Sí' : 'No';
                            break;
                        case 'date':
                            $value = $value instanceof \DateTimeInterface ? $value->format('d/m/Y H:i') : '';
                            break;
                        case 'list':
                            $value = is_iterable($value) ? implode(', ', (array)$value) : $value;
                            break;
                        case 'html':
                            $value = strip_tags((string)$value);
                            break;
                        default:
                            if ($value instanceof \DateTimeInterface) {
                                $value = $value->format('d/m/Y H:i');
                            } elseif (is_object($value)) {
                                $value = method_exists($value, '__toString') ? (string)$value : '[objeto]';
                            } elseif (is_array($value)) {
                                $value = implode(', ', $value);
                            } else {
                                $value = (string)$value;
                            }
                            break;
                    }

                    $row[$field] = $value;
                }

                $data[] = $row;
            }

            if ($format === 'pdf') {
                return $this->exportService->exportToPdf($this->globals['nombre'], $data, $headers);
            } elseif ($format === 'excel') {
                return $this->exportService->exportToExcel($this->globals['nombre'], $data, $headers);
            } else {
                throw new \Exception("Formato de exportación no soportado");
            }
        }

        return $this->render('list.html.twig', [
            "globals" => $this->globals,
            "entity" => $entity,
            "path_to_add" => $path_to_add,
            'pagination' => $pagination,
            'filters' => $filters,
            'limit' => $limit,
        ]);
    }
}
