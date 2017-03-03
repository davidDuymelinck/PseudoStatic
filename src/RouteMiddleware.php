<?php

namespace PseudoStatic;


use Symfony\Component\Yaml\Yaml;

class RouteMiddleware
{
    protected $projectRoot;

    protected $url;

    public function __construct($projectRoot, $url) {
        $this->projectRoot = $projectRoot;
        $this->url = $url;
    }

    public function getYamlData() {
        $yamlData = [];
        $dataPath = empty($this->url) ? $this->projectRoot.'/site/landing/' : $this->projectRoot.'/site/'.$this->url.'/';
        $dataFile = $dataPath.'/data.yaml';

        if(file_exists($dataFile)) {
            $yamlData = Yaml::parse(file_get_contents($dataFile));

            if(isset($yamlData['imports'])) {
                foreach($yamlData['imports'] as $file) {
                    $importFile = $dataPath.$file;

                    if(strpos($file, '!site') !== FALSE) {
                        $importFile = str_replace('!site', $this->projectRoot.'/site', $file);
                    }

                    $yamlData += Yaml::parse(file_get_contents($importFile));
                }

                unset($yamlData['imports']);
            }
        }

        return $yamlData;
    }
}