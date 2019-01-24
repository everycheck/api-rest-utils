# api-rest-utils

Some basic utils we use in every api rest project under symfony

For now there is one class responsible for building different kind of response needed by an api rest. 

All methode return a `Symfony\Component\HttpFoundation\Response`, with json content. 

## usage 

```php 
    public function getAction($id)
    {
        $response  = new ResponseBuilder($this->get('jms_serializer'));
        $entity = $this->getDoctrine()->getManager()->getRepository(Entity::class)->find($id);
        
        if(empty($entity)) return $response->notFound();
        
        return $response->ok($entities);
    }
```    

## Response available 

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
- formError


