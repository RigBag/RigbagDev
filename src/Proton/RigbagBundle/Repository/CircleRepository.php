<?php

namespace Proton\RigbagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CircleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CircleRepository extends EntityRepository
{
	
	public function search( $q, $missCircles = array(), $params = array() ) {
	
		extract( $params );
		
		if( strlen( $q ) ) {
				
			$qb		= $this->createQueryBuilder('c');
			
			$query = $qb->where( '(' . $qb->expr()->like('c.name', ':q') . ' OR ' . $qb->expr()->like('c.description', ':q') . ')' );

			if( count( $missCircles ) ) {
				$query->andWhere( $qb->expr()->notIn( 'c.id', $missCircles ) );
			}
			
			$query->setParameter( 'q', '%' . $q . '%' );
				
			
			if( isset( $offset ) ) {
				$query->setFirstResult( $offset );
			}
			if( isset( $limit ) ) {
				$query->setMaxResults( $limit );
			}
	
			$query->addOrderBy('c.name', 'ASC');
				
	
			$result = $query->getQuery()->getResult();
	
			
			return $result;
				
		} 
		elseif( isset( $force ) && $force ) {
			$query		= $this->createQueryBuilder('c');
			
			if( isset( $offset ) ) {
				$query->setFirstResult( $offset );
			}
			if( isset( $limit ) ) {
				$query->setMaxResults( $limit );
			}
			
			$query->addOrderBy('c.name', 'ASC');
			
			//echo $query->getQuery()->getSql(); exit;
                        
			$result = $query->getQuery()->getResult();
			//print_r($result); exit;
				
			return $result;
		}
		return array();
	}
	
	public function findForInterests( $interests ) {
		
		$ids	= array();
		foreach( $interests as $interest ) {
			if( is_object( $interest ) ) {
				$ids[]	= $interest->getId();
			} else {
				$ids[]	= $interest;
			}
		}
		
		$qb	= $this->createQueryBuilder('c');
		if( count( $ids ) ) {
			$query 	= 	$qb->where( 'c.interest_id IN (' . implode(',', $ids ) . ')' );
		}
		
		
		return $query->getQuery()->getResult();
	}
	
	public function findForUser( $userId ) {
	
		if( $userId ) {
			$query	= $this->createQueryBuilder('c')->join( 'c.users', 'u' );
	
			$query->where( 'u.id=:userId' );
			$query->setParameter( 'userId', $userId );
				
			// EXECUTE
			$query->addOrderBy('c.name', 'ASC');
				
			return $query->getQuery()->getResult();
		}
	
		return array();
	}
	
	public function findAll() {
		
		$query	= $this->createQueryBuilder('c');
		
		$query->addOrderBy('c.name', 'ASC');
			
		return $query->getQuery()->getResult();
	}
	
}
