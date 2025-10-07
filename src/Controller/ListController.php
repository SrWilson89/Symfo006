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
                $path_to_add = $this->generateUrl('app_create_cliente');
                break;
            case 'user': // Alias para 'usuarios'
            case 'usuarios':
                $paginator = new Paginator($this->em, User::class);
                $path_to_add = $this->generateUrl('app_create_usuario');
                break;
            case 'estados':
                $paginator = new Paginator($this->em, Estados::class);
                $path_to_add = $this->generateUrl('app_create_estado');
                break;
            case 'tareas':
                $paginator = new Paginator($this->em, Tareas::class);
                $path_to_add = $this->generateUrl('app_create_tarea');
                break;
            case 'productos':
                $paginator = new Paginator($this->em, Productos::class);
                $path_to_add = $this->generateUrl('app_create_producto');
                break;
            case 'detalles':
                $paginator = new Paginator($this->em, Detalles::class);
                $path_to_add = $this->generateUrl('app_create_detalle');
                break;
            case 'presupuestos':
                $paginator = new Paginator($this->em, Presupuestos::class);
                $path_to_add = $this->generateUrl('app_create_presupuesto');
                break;

            default:
                throw $this->createNotFoundException(sprintf('Entidad \'%s\' no es soportada', $entity));
        }
        
        // CORRECCIÓN CLAVE: Obtener los datos de paginación ANTES de usarlos
        if ($paginator !== null) {
            $pagination = $paginator->paginate($page, $limit, $filters);
        }

        if ($isExport) {
            $headers = [];
            
            // Si pagination tiene field_titles, los usamos para los encabezados de exportación
            if (isset($pagination['field_titles'])) {
                foreach ($pagination['field_titles'] as $options) {
                    $headers[] = $options['title'];
                }
            }

            $data = [];
            // Recorremos los resultados de la paginación para formatear los datos
            foreach ($pagination['results'] as $item) {
                $row = [];
                foreach ($pagination['field_titles'] as $field => $options) {
                    $value = $item->{'get' . ucfirst($field)}();

                    switch (true) {
                        case ($options['type'] === 'bool'):
                            $value = $value ? 'Sí' : 'No';
                            break;
                        case ($options['type'] === 'currency'):
                            $value = number_format($value, 2, ',', '.') . '€';
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