<?php

namespace PseudoStatic\RouteHandler;


use Interop\Container\ContainerInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

final class CreatePage
{
    private $container;

    private $projectRoot;

    public function __construct(ContainerInterface $container, $projectRoot) {
        $this->container = $container;
        $this->projectRoot = $projectRoot;
    }

    public function __invoke($request, $response, $args) {
        $formData = $request->getParsedBody();
        $message = [];

        if(empty($formData['url']) || empty($formData['title']) || empty($formData['body'])) {
            $message['error'] = 'All fields are required.';
        } else {
            $adapter = new Local($this->projectRoot);
            $filesystem = new Filesystem($adapter);
            $newPath = '/site/'.$formData['url'].'/html.twig';

            if($filesystem->has($newPath)) {
                $message['error'] = 'The url exists, please pick another.';
            } else {
                $message['created'] = 'yes';

                $templateContent = $filesystem->read('/layout/create-page.html.twig');
                $templateContent = str_replace(['§title§', '§body§'], [$formData['title'], $formData['body']], $templateContent);

                $filesystem->write($newPath, $templateContent);
            }
        }

        $redirectUrl = '/admin/create-page';

        if(count($message) > 0) {
            $redirectUrl .= '?'.array_keys($message)[0].'='.current($message);
        }

        return $response->withStatus(302)->withHeader('Location', $redirectUrl);
    }
}