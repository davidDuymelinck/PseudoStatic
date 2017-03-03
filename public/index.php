<?php

$projectRoot = dirname(dirname(__FILE__));

require $projectRoot.'/vendor/autoload.php';

$app = new \Slim\App();

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

$app->get('/', function (\Slim\Http\Request $request, $response, $args) {
    return $this->view->render($response, 'landing/page.html.twig', []);
});

$app->run();