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
    public function searchArticles(string $search){
        return $this->createQueryBuilder('a')
            //start the SQL request with the where
            ->where('a.title LIKE :search')
            //a second condition for search in the content
            ->orWhere('a.description LIKE :search')
            //parametring the variable 'search'
            ->setParameter('search', '%'.$search.'%')
            //construct the query
            ->getQuery()
            //get the result filtrered by the DB
            ->getResult();

        // SELECT * FROM article AS WHERE a.title LIKE %search% OR WHERE a.content LIKE %search%
    }

    public function getLastArticlePublished() :?Article
    {
        return $this->findOneBy(['status'=>'published'], ['createdAt' => 'DESC']);
    }

    public function getLast5ArticlePublished():? array {
      return $this->createQueryBuilder('article')
        //start the SQL request with the where
        ->where('article.status LIKE \'published\'')
        //limit the number of result at 5
        ->setMaxResults(5)
        //construct the query
        ->getQuery()
        //get the result filtrered by the DB
        ->getResult();
    }

    public function getArticleByCategory (int $catId) : ?array {
      return $this->createQueryBuilder('article')
        ->where('category.id = :id')
        ->innerJoin('article.category', 'category')
        ->andWhere('category.id = :id')
        ->setParameter(':id', $catId)
        ->getQuery()
        ->getResult();
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
