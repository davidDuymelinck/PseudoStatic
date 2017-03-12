<?php

namespace PseudoStatic\Middleware;


use PseudoStatic\YamlHelper;

class AllGetRoutes
{
    private $projectRoot;

    private $container;

    private $adminActions = [];

    public function __construct($container, $projectRoot) {
        $this->projectRoot = $projectRoot;
        $this->container = $container;
    }

    /**
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $url = $request->getAttribute('route')->getArgument('url');
        $fileContent = 'html';

        if(preg_match('/\.([a-z]+)$/', $url, $matches) && !empty($matches) && $matches[1] != $fileContent) {
            $fileContent = $matches[1];
        }

        if(strlen($url) > 0) {
            $url = str_replace('.'.$fileContent, '', $url);
        }

        $template = empty($url) ? 'landing/html.twig' : $url . '/'.$fileContent.'.twig';

        if($this->container->has('adminActions')) {
            $this->adminActions = $this->container->get('adminActions');
        }

        if (strlen($url) > 0 && file_exists($this->projectRoot . '/site/' . $template) === FALSE) {
            $request = $request->withAttribute('template', 'error/not-found/html.twig');
            $request = $request->withAttribute('data', []);
        } else {
            if(strlen($url) > 0 && strpos($url, 'admin') !== FALSE) {
                $request = $this->executeAdmin($url, $request);
            }

            $request = $request->withAttribute('template', $template);

            $data = $request->getAttribute('data', []);
            $data = array_merge($data, $this->getYamlData($url));
            $request = $request->withAttribute('data', $data);
        }

        $response = $next($request, $response);

        return $response;
    }

    private function getYamlData($url) {
        $yamlData = [];
        $dataPath = empty($url) ? $this->projectRoot.'/site/landing/' : $this->projectRoot.'/site/'.$url.'/';
        return (new YamlHelper($this->projectRoot))->getArrayFromDir($dataPath);
    }

    private function executeAdmin($url, $request) {
        $actionTrigger = str_replace('admin/', '', $url);
        $return = $request;

        if(isset($this->adminActions[$actionTrigger])) {
            $return = $this->adminActions[$actionTrigger]($request);
        }

        return $return;
    }
}