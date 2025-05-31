<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Recherche avancée pour DataTables : recherche, tri, pagination.
     */
    public function findForDataTable(int $start, int $length, ?string $search, string $orderColumn, string $orderDir): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->leftJoin('a.comments', 'com')
            ->leftJoin('a.likes', 'l')
            ->addSelect('c', 'com', 'l')
            ->groupBy('a.id');

        if ($search) {
            $qb->andWhere('a.title LIKE :search OR c.title LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $totalCount = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $filteredCountQb = $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->groupBy('a.id');

        if ($search) {
            $filteredCountQb->andWhere('a.title LIKE :search OR c.title LIKE :search')
                            ->setParameter('search', '%' . $search . '%');
        }

        $filteredCount = count($filteredCountQb->getQuery()->getResult());

       
        if ($orderColumn === 'commentsCount') {
            $qb->addSelect('COUNT(com.id) AS HIDDEN commentsCount')
               ->orderBy('commentsCount', $orderDir);
        } elseif ($orderColumn === 'likesCount') {
            $qb->addSelect('COUNT(l.id) AS HIDDEN likesCount')
               ->orderBy('likesCount', $orderDir);
        } elseif ($orderColumn === 'categories') {
            $qb->orderBy('c.title', $orderDir);
        } else {
            $qb->orderBy('a.' . $orderColumn, $orderDir);
        }

        $qb->setFirstResult($start)
           ->setMaxResults($length);

        return [
            'data' => $qb->getQuery()->getResult(),
            'totalCount' => $totalCount,
            'filteredCount' => $filteredCount
        ];
    }

    /**
     * Recherche des articles par titre
     */
    public function searchByTitle(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->where('a.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Exemple de méthode personnalisée par défaut
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
