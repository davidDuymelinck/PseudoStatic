<?php

use PseudoStatic\AdminAction\RefreshSite;
use PseudoStatic\RouteMiddleware;

$projectRoot = dirname(dirname(__FILE__));

require $projectRoot.'/vendor/autoload.php';

$config = [
    'adminActions' => [
        'refresh-site' => new RefreshSite($projectRoot),
    ]
];

$app = new \Slim\App($config);

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) use($projectRoot) {
    $view = new \Slim\Views\Twig($projectRoot.'/site', [
        'cache' => $projectRoot.'/cache'
    ]);

    // Instantiate and add Slim specific extension
    $urlBasePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $urlBasePath));

    return $view;
};

$app->get('/{url:.*}', function (\Slim\Http\Request $request, $response, $args) {
    return $this->view->render($response, $request->getAttribute('template'), $request->getAttribute('data'));
})->add(function (\Slim\Http\Request $request, $response, $next) use ($projectRoot, $container) {
    $url = $request->getAttribute('route')->getArgument('url');
    $fileContent = '.html';

    if(preg_match('/\.[a-z]+$/', $url, $matches) && !empty($matches) && $matches[0] != $fileContent) {
        $fileContent = $matches[0];
    }

    $template = empty($url) ? 'landing/page.html.twig' : $url . '/page'.$fileContent.'.twig';
    $routeMiddleware = new RouteMiddleware($projectRoot, $url);
    $routeMiddleware->addAdminActions($container->get('adminActions'));

    if (strlen($url) > 0 && file_exists($projectRoot . '/site/' . $template) === FALSE) {
        $request = $request->withAttribute('template', 'error/not-found/page.html.twig');
        $request = $request->withAttribute('data', []);
    } else {
        if(strlen($url) > 0 && strpos($url, 'admin') !== FALSE) {
            $routeMiddleware->executeAdmin();
        }

        $request = $request->withAttribute('template', $template);

        $data = $routeMiddleware->getYamlData();
        $request = $request->withAttribute('data', $data);
    }

    $response = $next($request, $response);

    return $response;
});

$app->run();