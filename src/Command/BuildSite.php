<?php

namespace PseudoStatic\Command;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PseudoStatic\YamlHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\Extension\MarkdownExtension;

class BuildSite extends Command
{
    protected $projectRoot;

    public function __construct($projectRoot) {
        parent::__construct(null);

        $this->projectRoot = $projectRoot;
    }

    protected function configure()
    {
        $this
            ->setName('build:site')
            ->setDescription('Creates the site for a server without php.')
            ->setHelp('Creates the site for a server without php.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = new Local($this->projectRoot);
        $filesystem = new Filesystem($adapter, ['disable_asserts' => TRUE]);
        // remove old static files
        $filesystem->deleteDir('distr');

        $publicRoot = $this->projectRoot.'/public';
        $distrRoot = $this->projectRoot.'/distr';
        $assetFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($publicRoot));

        foreach($assetFiles as $file) {
            if(!$file->isDir() && !in_array($file->getFilename(), ['index.php', '.htaccess', '.', '..'])) {
                $oldPath = str_replace($publicRoot, '/public/', $file->getPathname());
                $newPath = str_replace($publicRoot, '/distr/', $file->getPathname());
                $filesystem->copy($oldPath, $newPath);
            }
        };

        $siteRoot = $this->projectRoot.'/site';
        $siteFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($siteRoot));
        $templateFiles = new \CallbackFilterIterator($siteFiles, function ($current, $key, $file) {
            $pathName = $file->getPathname();

            return strpos($pathName, 'admin') === FALSE &&
                strpos($pathName, 'error') === FALSE &&
                strpos($pathName, '.yaml') === FALSE &&
                !$file->isDot();
        });
        $loader = new Twig_Loader_Filesystem($siteRoot);
        $loader->addPath($this->projectRoot.'/layout', 'layout');
        $twig = new Twig_Environment($loader);
        $engine = new MarkdownEngine\MichelfMarkdownEngine();
        $twig->addExtension(new MarkdownExtension($engine));

        foreach ($templateFiles as $file) {
            $template = str_replace($siteRoot.DIRECTORY_SEPARATOR, '', $file->getPathName());
            $filePath = str_replace($file->getFilename(), '', $file->getPathname());
            $data = (new YamlHelper($this->projectRoot))->getArrayFromDir($filePath);
            $content = $twig->load($template)->render($data);

            if(strpos($file->getPathname(), 'html') !== FALSE) {
                $outputFile = strpos($file->getPathname(), 'landing') !== FALSE ? 'index.html' : str_replace($file->getFilename(), '', $template).'index.html';
            } else {
                $outputFile = substr(str_replace($file->getFilename(), '', $template),0, -1).'.'.str_replace('.twig', '', $file->getFilename());
            }

            $filesystem->write('/distr/'.$outputFile, $content);
        }
    }
}