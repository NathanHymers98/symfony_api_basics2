<?php

namespace AppBundle\Serializer;

use AppBundle\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;

class LinkSerializationSubscriber implements EventSubscriberInterface
{
    private $router;

    private $annotationReader;

    private $expressionLanguage;

    public function __construct(RouterInterface $router, Reader $annotationReader)
    {
        $this->router = $router;
        $this->annotationReader = $annotationReader;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function onPostSerialize(ObjectEvent $event) // This method is what customizes the serialization process
    {
        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor(); // This $visitor object is in charge of the serialization process

        $object = $event->getObject();
        $annotations = $this->annotationReader
            ->getClassAnnotations(new \ReflectionObject($object));

        $links = array();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                $uri = $this->router->generate(
                    $annotation->route,
                    $this->resolveParams($annotation->params, $object)
                );
                $links[$annotation->name] = $uri;
            }
        }

        if ($links) {
            $visitor->addData('_links', $links); // Using the $visitor object to add a new _links field
        }
    }

    private function resolveParams(array $params, $object)
    {
        foreach ($params as $key => $param) {
            $params[$key] = $this->expressionLanguage
                ->evaluate($param, array('object' => $object));
        }

        return $params;
    }

    public static function getSubscribedEvents() // This method tells the serializer which events we want to hook into and customize
    {
        return array(
            array(
                'event' => 'serializer.post_serialize', // This is the event we want to hook into to change the serialization process
                'method' => 'onPostSerialize',
                'format' => 'json', // This means that this method will only be called when we are serializing into JSON
            )
        );
    }
}
