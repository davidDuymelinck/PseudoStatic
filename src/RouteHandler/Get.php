<?php

namespace PseudoStatic\RouteHandler;


use Psr\Container\ContainerInterface;

class Get
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args) {
        return $this->container->view->render($response, $request->getAttribute('template'), $request->getAttribute('data'));
    }
}