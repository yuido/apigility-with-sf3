<?php

namespace Yuido;

use ZF\Rest\AbstractResourceListener;
use Doctrine\Common\Annotations\AnnotationRegistry;  


class ResourceListener extends AbstractResourceListener{
    protected $doctrine;

    public function __construct() {
        $symfonyApp = __DIR__ . '/../../../sf';
        require_once $symfonyApp . '/app/AppKernel.php';
        $kernel = new \AppKernel('prod', true);
        $kernel->loadClassCache();
        $kernel->boot();
        
        AnnotationRegistry::registerFile(__DIR__. "/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
        
        $this->doctrine = $kernel->getContainer()->get('doctrine')->getManager();
    }
}
