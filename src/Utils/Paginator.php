<?php

namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Clientes;
use App\Entity\User;
use App\Entity\Estados;
use App\Entity\Tareas;
use App\Entity\Productos;
use App\Entity\Detalles;
use App\Entity\Presupuestos;

class Paginator
{
    private EntityManagerInterface $em;
    private string $entityClass;

    /**
     * Definiciones de campos por entidad. CRÍTICO para que el listado sepa
     * qué columnas mostrar (field_titles) y cómo aplicar filtros.
     */
    private array $fieldDefinitions = [
        Clientes::class => [
            'id' => ['title' => 'ID', 'type' => 'int'],
            'nombre' => ['title' => 'Nombre Cliente', 'type' => 'text'],
            'email' => ['title' => 'Email', 'type' => 'text'],
        ],
        User::class => [
            'id' => ['title' => 'ID', 'type' => 'int'],
            'email' => ['title' => 'Email', 'type' => 'text'],
            'nombre' => ['title' => 'Nombre', 'type' => 'text'],
            'apellidos' => ['title' => 'Apellidos', 'type' => 'text'],
            'super' => ['title' => 'Rol', 'type' => 'int'],
        ],
        Tareas::class => [
            'id' => ['title' => 'ID', 'type' => 'int'],
            'titulo' => ['title' => 'Título', 'type' => 'text'],
            // Las relaciones deben tener un type 'relation' para hacer el JOIN
            'estado' => ['title' => 'Estado', 'type' => 'relation'],
            'usuario' => ['title' => 'Asignado a', 'type' => 'relation'], 
            'fechaCreacion' => ['title' => 'F. Creación', 'type' => 'date'],
        ],
        Estados::class => [
            'id' => ['title' => 'ID', 'type' => 'int'],
            'nombre' => ['title' => 'Nombre', 'type' => 'text'],
        ],
        // Añade el resto de entidades (Productos, Detalles, Presupuestos...)
        // para que también funcionen si las necesitas.
    ];

    public function __construct(EntityManagerInterface $em, string $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;

        if (!isset($this->fieldDefinitions[$this->entityClass])) {
            throw new \InvalidArgumentException(
                sprintf('No se han definido campos para la entidad "%s" en Paginator.php', $entityClass)
            );
        }
    }

    public function paginate(int $page = 1, ?int $limit = 10, array $filters = []): array
    {
        $fields = $this->fieldDefinitions[$this->entityClass];
        $alias = 'e';
        
        $qb = $this->em->getRepository($this->entityClass)->createQueryBuilder($alias);
        
        // 1. Manejo de relaciones (CRÍTICO para Tareas/User)
        // Hacemos JOIN y SELECT para evitar problemas de carga perezosa (Lazy Loading)
        foreach ($fields as $fieldName => $options) {
            if ($options['type'] === 'relation') {
                $qb->leftJoin($alias . '.' . $fieldName, $fieldName) 
                   ->addSelect($fieldName);
            }
        }
        
        // 2. Aplicar Filtros (lógica básica)
        foreach ($filters as $field => $value) {
            if (!empty($value) && isset($fields[$field])) {
                // Si el campo es relacional, filtramos por la representación de la relación (ej. __toString())
                $fieldAlias = ($fields[$field]['type'] === 'relation') ? $field : $alias;
                
                // Usamos LIKE para búsquedas parciales
                $qb->andWhere($fieldAlias . '.' . $field . ' LIKE :' . $field)
                   ->setParameter($field, '%' . $value . '%');
            }
        }

        // 3. Obtener el total de elementos (para la paginación)
        $countQueryBuilder = clone $qb;
        $countQueryBuilder->select('COUNT(' . $alias . '.id)')
                          ->resetDQLPart('orderBy')
                          ->setMaxResults(null)
                          ->setFirstResult(null);

        $totalItems = $countQueryBuilder->getQuery()->getSingleScalarResult();
        
        // 4. Calcular la paginación
        $totalPages = (int) ceil($totalItems / ($limit ?? $totalItems));
        $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
        $offset = ($page - 1) * $limit;

        if ($limit !== null) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }
        
        // 5. Obtener los resultados finales
        $results = $qb->getQuery()->getResult();
        
        return [
            'results' => $results,
            'total_items' => (int) $totalItems,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'field_titles' => $fields, // Ahora contiene las definiciones para Tareas y User
        ];
    }
}