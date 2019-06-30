# api-rest-utils

Some basic utils we use in every api rest project under symfony

## Response builder

For now there is one class responsible for building different kind of response needed by an api rest. 

All methode return a `Symfony\Component\HttpFoundation\Response`, with json content. 

### usage 

```php 
    public function getAction($id)
    {
        $response  = new ResponseBuilder($this->get('jms_serializer'));
        $entity = $this->getDoctrine()->getManager()->getRepository(Entity::class)->find($id);
        
        if(empty($entity)) return $response->notFound();
        
        return $response->ok($entities);
    }
```    

### Response available 

- json
- empty 
- notFound
- ok
- created
- deleted
- conflict
- forbiddenRoute
- forbiddenAcl
- forbidden
- badRequest
- formError __for parsing symfony form error__
- unauthorized

### Configuring headers

Use the `addHeaders` method : 


```php 
    public function getAction($id)
    {
        $response  = new ResponseBuilder($this->get('jms_serializer'));
        $entity = $this->getDoctrine()->getManager()->getRepository(Entity::class)->find($id);
        
        if(empty($entity)) return $response->notFound();
        
        return $response->addHeaders('X-something','some-value')->ok($entities);
    }
```    

## Find paginated with filter and order by

Another common thing required is a find all entity matching certain filter and ordered by some criteria

Just add the trait in your repository like this : 

```php
<?php

namespace ACMEBundle\Repository;

use EveryCheck\ApiRest\Utils\PaginatedRepositoryTrait;

class PostRepository extends \Doctrine\ORM\EntityRepository
{
    use PaginatedRepositoryTrait;

    const BASE_QUERY_NAME = 'post';

    const LEFT_JOIN_ALIAS_LIST = [
        'post.author'          => 'author',
        'post.responses'        => 'response',
    ];

    const FILTER_OPTION = [
        ['filterOn'=>'author.username'   , 'filterName' => 'username'         , 'filterKind'=>'like'         ],
        ['filterOn'=>'response.message'  , 'filterName' => 'response_message' , 'filterKind'=>'like'         ],
        ['filterOn'=>'response.date'     , 'filterName' => 'date'             , 'filterKind'=>'greaterThan'  ],
    ];

}
```

Here an exaple where you enable search on 3 fields on left join. You can also order by those fields.

Controler side :

```php
    /**
     * @Route("/posts", name="get_post_list", methods={"GET"})
     */
    public function getPostListAction(Request $request)
    {       
        $posts = $this->getEntityManager()->getRepository(Post::class)->findPaginatedFromRequest($request);
        return $this->getResponseBuilder()->ok($posts);
    }
```

Response example :

```json
{
    "limit": 10,
    "offset": 0,
    "count": 1,
    "entities": [
        {
            "message":"something",
            "author":{
                "username":"someone"
            },
            "responses":[
                {
                    "message":"something else",
                    "author":{
                        "username":"someone_else"
                    }
                }
            ]
        }
    ]
}
```

Easy ? 

