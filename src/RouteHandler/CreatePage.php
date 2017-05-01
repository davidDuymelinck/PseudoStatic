<?php

namespace PseudoStatic\RouteHandler;


use Interop\Container\ContainerInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PseudoStatic\ValueObject\PageFields;

final class CreatePage
{
    private $container;

    private $projectRoot;

    private $fileSystem;

    public function __construct(ContainerInterface $container, $projectRoot) {
        $this->container = $container;
        $this->projectRoot = $projectRoot;

        $adapter = new Local($this->projectRoot);
        $this->filesystem = new Filesystem($adapter);
    }

    public function __invoke($request, $response, $args) {
        $formData = new PageFields($request->getParsedBody());
        $message = [];

        if($formData->requiredFieldsNotEmpty() === FALSE) {
            $message['error'] = 'All fields are required.';
        } else {
            $urlPath = '/site/'.$formData->getUrl().'/';
            $twigPath = $urlPath.'html.twig';

            if($this->fileExists($twigPath)) {
                $message['error'] = 'The url exists, please pick another.';
            } else {
                $message['created'] = 'yes';

                $templateContent = $this->filesystem->read('/templates/blueprints/create-page.html.twig');
                $templateContent = str_replace(['§title§', '§body§'], [$formData->getTitle(), $formData->getBody()], $templateContent);

                $this->filesystem->write($twigPath, $templateContent);

                if(strlen($formData->get('data')) > 0) {
                    $this->filesystem->write($urlPath.'data.yaml', $formData->get('data'));
                }

                if(strlen($formData->get('yaml_name')) > 0 && strlen($formData->get('yaml_data')) > 0) {
                    $this->filesystem->write($urlPath.$formData->get('yaml_name').'.yaml', $formData->get('yaml_data'));
                }
            }
        }

        $redirectUrl = '/admin/create-page';

        if(count($message) > 0) {
            $redirectUrl .= '?'.array_keys($message)[0].'='.current($message);
        }

        return $response->withStatus(302)->withHeader('Location', $redirectUrl);
    }

    public function fileExists($path) {
        return $this->filesystem->has($path);
    }
}