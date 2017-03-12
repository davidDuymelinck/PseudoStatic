<?php

namespace PseudoStatic\AdminAction;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class RefreshSite
{
    protected $projectRoot;

    public function __construct($projectRoot) {
        $this->projectRoot = $projectRoot;
    }

    function __invoke($request)
    {
        $adapter = new Local($this->projectRoot);
        $filesystem = new Filesystem($adapter);
        $filesystem->deleteDir('cache');

        return $request;
    }
}