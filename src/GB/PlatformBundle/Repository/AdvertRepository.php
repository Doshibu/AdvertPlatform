<?php

namespace GB\PlatformBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * AdvertRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertRepository extends EntityRepository
{
	public function myFindAll()
	{
    // Méthode 1 : en passant par l'EntityManager
		$queryBuilder = $this->_em->createQueryBuilder()
		->select('a')
		->from($this->_entityName, 'a')
		;
    // Dans un repository, $this->_entityName est le namespace de l'entité gérée
    // Ici, il vaut donc OC\PlatformBundle\Entity\Advert

    // Méthode 2 : en passant par le raccourci (je recommande)
		$queryBuilder = $this->createQueryBuilder('a');

    // On n'ajoute pas de critère ou tri particulier, la construction
    // de notre requête est finie

    // On récupère la Query à partir du QueryBuilder
		$query = $queryBuilder->getQuery();

    // On récupère les résultats à partir de la Query
		$results = $query->getResult();

    // On retourne ces résultats
		return $results;
	}

	public function myFindAllDQL()
	{
		$query = $this->_em->createQuery('SELECT a FROM OCPlatformBundle:Advert a');
		$results = $query->getResult();

		return $results;
	}

	public function myFindOne($id)
	{
		$qb = $this->createQueryBuilder('a');
		$qb
		->where('a.id = :id')
		->setParameter('id', $id);

		return $qb
		->getQuery()
		->getResult();
	}

	public function myFindDQL($id)
	{
		$query = $this->_em->createQuery('SELECT a FROM Advert a WHERE a.id = :id');
		$query->setParameter('id', $id);

	  // Utilisation de getSingleResult car la requête ne doit retourner qu'un seul résultat
		return $query->getSingleResult();
	}

	public function findByAuthorAndDate($author, $year)
	{
		$qb = $this->createQueryBuilder('a');

		$qb->where('a.author = :author')
		->setParameter('author', $author)
		->andWhere('a.date < :year')
		->setParameter('year', $year)
		->orderBy('a.date', 'DESC');

		return $qb
		->getQuery()
		->getResult();
	}

	public function whereCurrentYear(\Doctrine\ORM\QueryBuilder $qb)
	{
		$qb
		->andWhere('a.date BETWEEN :start AND :end')
	      ->setParameter('start', new \Datetime(date('Y').'-01-01'))  // Date entre le 1er janvier de cette année
	      ->setParameter('end',   new \Datetime(date('Y').'-12-31'))  // Et le 31 décembre de cette année
	      ;
	}

	public function whereCurrentMonth(\Doctrine\ORM\QueryBuilder $qb)
	{
		$qb
		->where('a.date BETWEEN :start AND :end')
	      ->setParameter('start', new \Datetime(date('Y-m').'-01'))  // Date entre le 1er janvier de cette année
	      ->setParameter('end',   new \Datetime(date('Y-m').'-31'))  // Et le 31 décembre de cette année
	      ;
	}

	public function findAdvertForCurrentMonth($orderBy='DESC', $limit=10)
	{
		$qb = $this->createQueryBuilder('a');
		$this->whereCurrentMonth($qb);

	  	$orderBy = $orderBy === 'DESC' || $orderBy === 'ASC' ? $orderBy : 'DESC'; 
		$qb->orderBy('a.updatedAt', $orderBy);

		$limit = $limit > 0 && $limit <= 10 ? $limit : 10;
		$qb->setMaxResults($limit);

		return $qb
		->getQuery()
		->getResult()
		;
	}

	public function getAdvertWithApplications()
	{
		$qb = $this
		->createQueryBuilder('a')
		->leftJoin('a.applications', 'app')
		->addSelect('app')
		;

		return $qb
		->getQuery()
		->getResult()
		;
	}

	public function getAdvertWithCategories(array $categoryNames)
	{
	    $qb = $this->createQueryBuilder('a');

	    // On fait une jointure avec l'entité Category avec pour alias « c »
	    $qb
	      ->join('a.categories', 'c')
	      ->addSelect('c')
	    ;

	    // Puis on filtre sur le nom des catégories à l'aide d'un IN
	    $qb->where($qb->expr()->in('c.name', $categoryNames));
	    // La syntaxe du IN et d'autres expressions se trouve dans la documentation Doctrine

	    // Enfin, on retourne le résultat
	    return $qb
	      ->getQuery()
	      ->getResult()
	    ;
	}

	public function getAdverts($page, $nbPerPage)
	{
		$query = $this->createQueryBuilder('a')
			->leftJoin('a.image', 'i')
			->addSelect('i')
			->leftJoin('a.categories', 'c')
			->addSelect('c')
			->orderBy('a.date', 'DESC')
			->getQuery();

		$query->setFirstResult(($page-1) * $nbPerPage)
			->setMaxResults($nbPerPage);

		return new Paginator($query, true);
	}

	public function getPublishedQueryBuilder()
	{
		return $this->createQueryBuilder('a')
				->where('a.published = :published')
				->setParameter('published', true);
	}
}