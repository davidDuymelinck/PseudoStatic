<?php

namespace PseudoStatic\Command;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PseudoStatic\YamlHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Twig_Environment;
use Twig_Loader_Filesystem;

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
        $loader = new Twig_Loader_Filesystem($siteRoot);
        $loader->addPath($this->projectRoot.'/layout', 'layout');
        $twig = new Twig_Environment($loader);

        foreach ($siteFiles as $file) {
            // there are no admin functions or error catching on a static site
            if(strpos($file->getPathname(), 'admin') !== FALSE || strpos($file->getPathname(), 'error') !== FALSE ||
                strpos($file->getPathname(), '.yaml') !== FALSE || in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

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