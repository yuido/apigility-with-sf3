<?php

namespace Yuido;

use ZF\Rest\AbstractResourceListener;

class ResourceListener extends AbstractResourceListener{
    protected $doctrine;

    public function __construct() {
        $symfonyApp = __DIR__ . '/../../../sf';
        require_once $symfonyApp . '/app/AppKernel.php';
        $kernel = new \AppKernel('prod', true);
        $kernel->loadClassCache();
        $kernel->boot();
        $this->doctrine = $kernel->getContainer()->get('doctrine')->getManager();
    }
}
