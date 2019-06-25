<?php
namespace EveryCheck\ApiRest\Utils;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

class ResponseBuilder 
{
    protected $serializer;
    protected $groups = null;
    protected $headers = ['Content-type'=>'application/json'];

    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    public function addHeaders($name,$value) : ResponseBuilder
    {
        $this->headers[$name]=$value;
        return $this;
    }

    public function setSerializationGroups($groups)
    {
        $this->groups = $groups;
    }

    public function json($entity,$code) : Response
    {
        $data = $this->serializer->serialize($entity,'json',$this->groups);
        return new Response($data,$code,$this->headers);
    }

    public function empty() : Response
    {
        return new Response('',Response::HTTP_NO_CONTENT);
    }

    public function notFound() : Response
    {
        return $this->json(['message'=>'Entity not found'],Response::HTTP_NOT_FOUND);
    }

    public function ok($entity) : Response
    {
        return $this->json($entity,Response::HTTP_OK);
    }

    public function created($entity) : Response
    {
        return $this->json($entity,Response::HTTP_CREATED);
    }

    public function deleted() : Response
    {
        return $this->json(['message'=> 'No content'],Response::HTTP_NO_CONTENT);
    }

    public function conflict($entity) : Response
    {
        return $this->json($entity,Response::HTTP_CONFLICT);
    }

    public function forbiddenRoute() : Response
    {
        return $this->forbidden('cannot access this route');
    }

    public function forbiddenAcl() : Response
    {
        return $this->forbidden('cannot access not owned entity');
    }

    public function forbidden($reason) : Response
    {
        return $this->json(['message'=>"Forbidden : $reason."],Response::HTTP_FORBIDDEN);
    }

    public function formError(\Symfony\Component\Form\Form $form) : Response
    {
        return $this->badRequest($this->getErrorMessages($form));
    }

    public function badRequest($entity) : Response
    {
        return $this->json($entity,Response::HTTP_BAD_REQUEST);
    }

    public function unauthorized() : Response
    {
        return $this->json(['message'=> 'Valid authentification required.'],Response::HTTP_UNAUTHORIZED);
    }

    private function getErrorMessages(Form $form) 
    {
        $errors = $this->getAllErrors($form);
        $this->getChildField($form,$errors);

        if (empty($errors['children']))
        {
            $errors['children'] = null;
        }
        return $errors;
    }

    private function getChildField(Form $form,&$errors)
    {
        foreach ($form->all() as $child) 
        {
            $errors['children'][$child->getName()] = $this->getErrorMessages($child);
        }
    }

    private function getAllErrors(Form $form)
    {
        $errors = array('children' => array());
        foreach ($form->getErrors() as $key => $error) 
        {
                if(empty($errors['errors'])) $errors['errors'] = array();
                array_push($errors['errors'], $error->getMessage());
        }
        return $errors;
    }

}
