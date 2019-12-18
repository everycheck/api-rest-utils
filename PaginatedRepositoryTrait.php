<?php
namespace EveryCheck\ApiRest\Utils;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait PaginatedRepositoryTrait 
{
    public  function findPaginatedFromRequest(Request $request)
    {
        $limit  =  $request->query->get('limit')  ;
        $offset =  $request->query->get('offset') ;
        $filter =  $request->query->get('filter') ;
        $order  =  $request->query->get('order')  ;

        return $this->findPaginated($limit,$offset,$filter,$order);
    }

    protected function cleanArgument(&$limit,&$offset,&$filter,&$order)
    {
        $limit  = intval($limit);
        if($limit<1) $limit = 10;

        $offset = intval($offset); 
        if($offset<0) $offset = 0;

        if(empty($order) || !is_array($order))
        {
            $order = [];
        }

        if(empty($filter) || !is_array($filter))
        {
            $filter = [];
        }

        $this->baseQueryName     = empty($this::BASE_QUERY_NAME)      ? 'e' : $this::BASE_QUERY_NAME;
        $this->leftJoinAliasList = empty($this::LEFT_JOIN_ALIAS_LIST) ? []  : $this::LEFT_JOIN_ALIAS_LIST;
        $this->filterOption      = empty($this::FILTER_OPTION)        ? []  : $this::FILTER_OPTION;

        if(empty($this->parameterCount))
        {
            $this->parameterCount = 1;
        }
    }

    public function findPaginated($limit=10, $offset=0, $filter = [], $order = [])
    {   
        $this->cleanArgument($limit,$offset,$filter,$order);

        $queryBuilder = $this->createQueryBuilder($this->baseQueryName);

        foreach ($this->leftJoinAliasList as $key => $value)
        {
            $queryBuilder->leftJoin($key,$value);
        }

        foreach ($this->filterOption as $option)
        {   
            $field = $option['filterOn'];   
            $value = $option['filterName'];
            $kind  = $option['filterKind'];
            if (isset($filter[$value]) && is_string($filter[$value]))
            {               
                $search = addcslashes($filter[$value], "%_");
                switch($kind)
                {
                    case 'lowerThan'  : 
                        $queryBuilder->andWhere($field.' < ?'.$this->parameterCount); 
                        $queryBuilder->setParameter($this->parameterCount,$search);
                        break;
                    case 'greaterThan': 
                        $queryBuilder->andWhere($field.' > ?'.$this->parameterCount);
                        $queryBuilder->setParameter($this->parameterCount,$search);
                        break;
                    case 'lowerThanOrEqual'  : 
                        $queryBuilder->andWhere($field.' <= ?'.$this->parameterCount); 
                        $queryBuilder->setParameter($this->parameterCount,$search);
                        break;
                    case 'greaterThanOrEqual': 
                        $queryBuilder->andWhere($field.' >= ?'.$this->parameterCount);
                        $queryBuilder->setParameter($this->parameterCount,$search);
                        break;
                    case 'notNull': 
                        $queryBuilder->andWhere($field.' IS NOT NULL');
                        break;
                    case 'notLike': 
                        $queryBuilder->andWhere($field.' NOT LIKE  ?'.$this->parameterCount);
                        $queryBuilder->setParameter($this->parameterCount,'%'.$search.'%');
                        break;
                    default:
                        $queryBuilder->andWhere($field.' LIKE  ?'.$this->parameterCount);
                        $queryBuilder->setParameter($this->parameterCount,'%'.$search.'%');
                }               
          
                $this->parameterCount += 1;
            }
            if (isset($order[$value]) ) 
            {
                $queryBuilder->orderBy($field, $order[$value]=='DESC'?'DESC':'ASC');
            }
        }

        $cloneQueryBuilder = clone $queryBuilder;
        $count = intval($cloneQueryBuilder->select('count(DISTINCT '.$this->baseQueryName.'.id)')->getQuery()->getSingleScalarResult());

        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);
        $paginator = new Paginator($queryBuilder->getQuery(), $fetchJoinCollection = true);

        return [
            'limit'=>$limit,
            'offset'=>$offset,
            'count'=>$paginator->count(),
            'entities'=>$paginator->getIterator()->getArrayCopy()
        ];
    }
}


