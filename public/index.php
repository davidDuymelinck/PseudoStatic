<?php

use PseudoStatic\AdminAction\AddPage;
use PseudoStatic\AdminAction\RefreshSite;
use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\Extension\MarkdownExtension;
use PseudoStatic\Middleware\AllGetRoutes;
use PseudoStatic\RouteHandler\CreatePage;
use PseudoStatic\RouteHandler\Get;
use Slim\Middleware\HttpBasicAuthentication;

$projectRoot = dirname(dirname(__FILE__));

require $projectRoot.'/vendor/autoload.php';

(new Dotenv\Dotenv($projectRoot))->load();

$config = [
    'adminActions' => [
        'refresh-site' => new RefreshSite($projectRoot),
        'create-page' => new AddPage(),
    ]
];

if(getenv('DEVELOPING') === 'YES') {
    $config['settings'] = [
        'displayErrorDetails' => true,
    ];
}

$app = new \Slim\App($config);

// Get container
$container = $app->getContainer();

$container['view'] = function ($container) use($projectRoot) {

    $view = new \Slim\Views\Twig($projectRoot.'/site', [
        'cache' => getenv('DEVELOPING') === 'YES' ? false : $projectRoot.'/cache'
    ]);

    $view->getLoader()->addPath($projectRoot.'/templates/layout', 'layout');

    // Instantiate and add Slim specific extension
    $urlBasePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $urlBasePath));

    $engine = new MarkdownEngine\MichelfMarkdownEngine();
    $view->addExtension(new MarkdownExtension($engine));

    return $view;
};

$app->add(new HttpBasicAuthentication([
    "path" => "/admin",
    "secure" => true,
    "relaxed" => ["localhost", "pseudostatic.local"],
    "users" => [
        getenv('ADMIN_USER') => getenv('ADMIN_PASS'),
    ]
]));

$app->get('/{url:.*}', new Get($container))->add(new AllGetRoutes($container, $projectRoot));

$app->post('/admin/create-page', new CreatePage($container, $projectRoot));

$app->run();