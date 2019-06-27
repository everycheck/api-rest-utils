<?php
namespace EveryCheck\ApiRest\Utils;


trait PaginatedRepositoryTrait 
{
	public function findAllPaginated($limit=10,$offset=0,$order=null)
	{
        return [
            'limit'=>$limit,
            'offset'=>$offset,
            'count'=>$this->count([]),
            'entities'=>$this->findBy([],$order,$limit,$offset)

        ];
	}

	public function findPaginatedBy($criteria,$order=null,$limit=10,$offset=0)
	{
        return [
            'limit'=>$limit,
            'offset'=>$offset,
            'count'=>$this->count([]),
            'entities'=>$this->findBy($criteria,$order,$limit,$offset)
        ];
	}
}