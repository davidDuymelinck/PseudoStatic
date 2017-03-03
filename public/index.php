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

$app->get('/{url:.*}', function (\Slim\Http\Request $request, $response, $args) {
    return $this->view->render($response, $request->getAttribute('template'), $request->getAttribute('data'));
})->add(function (\Slim\Http\Request $request, $response, $next) use ($projectRoot) {
    $url = $request->getAttribute('route')->getArgument('url');

    $template = empty($url) ? 'landing/page.html.twig' : $url . '/page.html.twig';

    if (strlen($url) > 0 && file_exists($projectRoot . '/site/' . $template) === FALSE) {
        $request = $request->withAttribute('template', 'error/not-found/page.html.twig');
        $request = $request->withAttribute('data', []);
    } else {
        $request = $request->withAttribute('template', $template);

        $request = $request->withAttribute('data', []);
    }

    $response = $next($request, $response);

    return $response;
});

$app->run();