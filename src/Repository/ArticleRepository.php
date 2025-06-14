<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Récupère les articles avec pagination et recherche pour DataTables.
     * 
     * @param int $start Offset de départ
     * @param int $length Nombre d'articles à récupérer
     * @param string|null $search Terme de recherche
     * 
     * @return Article[]
     */
     public function findForDataTable(int $start, int $length, ?string $search, string $orderColumn, string $orderDir): array
{
    $columnMapping = [
        0 => 'a.id',
        1 => 'a.title',
        2 => 'c.title',
        5 => 'a.createdAt'
    ];

    $qb = $this->createQueryBuilder('a')
        ->leftJoin('a.categories', 'c')
        ->leftJoin('a.comments', 'cm')
        ->leftJoin('a.likes', 'l');

    if ($search) {
        $qb->andWhere('a.title LIKE :search OR c.title LIKE :search')
           ->setParameter('search', '%'.$search.'%');
    }

    if (isset($columnMapping[$orderColumn])) {
        $qb->orderBy($columnMapping[$orderColumn], $orderDir);
    } else {
        $qb->orderBy('a.id', 'DESC');
    }

    $query = $qb->getQuery()
        ->setFirstResult($start)
        ->setMaxResults($length);

    $results = $query->getResult();

    $totalQb = $this->createQueryBuilder('a')
        ->select('COUNT(a.id)');
    
    if ($search) {
        $totalQb->leftJoin('a.categories', 'c')
                ->andWhere('a.title LIKE :search OR c.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
    }

    $totalCount = $totalQb->getQuery()->getSingleScalarResult();

    return [
        'data' => $results,
        'totalCount' => (int)$totalCount,
        'filteredCount' => $search ? count($results) : $totalCount
    ];
}

    /**
     * Compte tous les articles sans filtre.
     * 
     * @return int
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les articles filtrés par une recherche.
     * 
     * @param string|null $search Terme de recherche
     * @return int
     */
    public function countFiltered(?string $search): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)');

        if (!empty($search)) {
            $qb->andWhere('LOWER(a.title) LIKE :search')
               ->setParameter('search', '%' . strtolower($search) . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
