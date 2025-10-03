<?php

namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;

class Paginator
{
    private EntityManagerInterface $em;
    private string $entityClass;
    private int $page = 1;
    private ?int $limit = 10;
    private ?string $orderBy = null;
    private string $orderDirection = 'ASC';
    private array $criteria = [];

    private $rowClickUrlGenerator = null;
    private array $fieldTitles = [];
    private $linksGenerator = null;

    public function __construct(EntityManagerInterface $em, string $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }

    public function setPage(int $page): self
    {
        $this->page = max(1, $page);
        return $this;
    }

    public function setLimit(?int $limit): self
    {
        // Si limit es null, significa sin límite
        $this->limit = $limit;
        return $this;
    }

    public function setOrderBy(?string $orderBy, string $orderDirection = 'ASC'): self
    {
        $this->orderBy = $orderBy;
        $this->orderDirection = strtoupper($orderDirection) === 'DESC' ? 'DESC' : 'ASC';
        return $this;
    }

    public function setCriteria(array $criteria): self
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * fieldTitles: array con keys de campos, y valores con array ['title' => string, 'type' => string]
     */
    public function setFieldTitles(array $fieldTitles): self
    {
        foreach ($fieldTitles as $field => $value) {
            if (is_string($value)) {
                $this->fieldTitles[$field] = ['title' => $value, 'type' => 'text'];
            } elseif (is_array($value) && isset($value['title'])) {
                if (!isset($value['type'])) {
                    $value['type'] = 'text';
                }
                $this->fieldTitles[$field] = $value;
            } else {
                throw new \InvalidArgumentException("El formato para el campo '$field' no es válido.");
            }
        }
        return $this;
    }

    public function setLinksGenerator(?callable $callback): self
    {
        $this->linksGenerator = $callback;
        return $this;
    }

    public function setRowClickUrlGenerator(?callable $callback): self
    {
        $this->rowClickUrlGenerator = $callback;
        return $this;
    }

    public function paginate(): array
    {
        $repository = $this->em->getRepository($this->entityClass);
        $qb = $repository->createQueryBuilder('e');

        foreach ($this->criteria as $field => $value) {
            if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
                continue;
            }

            if (is_array($value)) {
                $qb->andWhere($qb->expr()->in('e.' . $field, ':' . $field))
                    ->setParameter($field, $value);
            } elseif (is_string($value)) {
                $qb->andWhere($qb->expr()->like('LOWER(e.' . $field . ')', ':' . $field))
                    ->setParameter($field, '%' . strtolower($value) . '%');
            } else {
                $qb->andWhere('e.' . $field . ' = :' . $field)
                    ->setParameter($field, $value);
            }
        }

        if ($this->orderBy) {
            $qb->orderBy('e.' . $this->orderBy, $this->orderDirection);
        }

        $countQb = clone $qb;
        $countQb->select('COUNT(e)');
        $totalItems = (int) $countQb->getQuery()->getSingleScalarResult();

        if ($this->limit !== null) {
            $qb->setFirstResult(($this->page - 1) * $this->limit)
               ->setMaxResults($this->limit);
        }

        $items = $qb->getQuery()->getResult();

        $itemsWithLinks = [];

        foreach ($items as $item) {
            $rowClickUrl = null;

            if ($this->rowClickUrlGenerator !== null) {
                $rowClickUrl = call_user_func($this->rowClickUrlGenerator, $item);
            }

            $entry = ['entity' => $item];

            if ($this->linksGenerator !== null) {
                $entry['links'] = call_user_func($this->linksGenerator, $item);
            }

            if ($rowClickUrl !== null) {
                $entry['row_url'] = $rowClickUrl;
            }

            $itemsWithLinks[] = $entry;
        }

        return [
            'items' => $itemsWithLinks,
            'total_items' => $totalItems,
            'current_page' => $this->page,
            'limit' => $this->limit,
            'total_pages' => $this->limit !== null ? (int) ceil($totalItems / $this->limit) : 1,
            'field_titles' => $this->fieldTitles,
            'criteria' => $this->criteria,
        ];
    }
}
