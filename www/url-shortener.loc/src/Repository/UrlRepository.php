<?php

namespace App\Repository;

use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    public function findOneByHash(string $value): ?Url
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.hash = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

	// задание 2
    public function findOneByUrl(string $value): ?Url
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.url = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
	
	// задание 5
	public function DBcount()
	{
		return $this->createQueryBuilder('u')
			->select('count(u.id)')
			->getQuery()
			->getSingleScalarResult()
		;
	}
	public function DBunique()
	{
		/*
		return $this->createQueryBuilder('u')
			->select('u.url')
			->distinct()
			->getQuery()
			->getResult()
		;
		return $this->getEntityManager()
			->createQuery('
				SELECT DISTINCT ON (url) *
			')
			->getQuery()
			->getResult()
		;
		return $this->createQueryBuilder('u')
			->select('DISTINCT ON (u.url)')
			->getQuery()
			->getResult()
		;
		*/
		return $this->createQueryBuilder('u')
			->select('u')
			->groupBy('u.url')
			->orderBy('u.id', 'DESC')
			->getQuery()
			->getResult()
		;
	}
}
