<?php

namespace PseudoStatic;


use Symfony\Component\Yaml\Yaml;

class YamlHelper
{
    protected $projectRoot;

    public function __construct($projectRoot) {
        $this->projectRoot = $projectRoot;
    }

    public function getArrayFromDir($dir) {
        $yamlData = [];
        $file = $dir.'data.yaml';

        if(file_exists($file)) {
            $yamlData = Yaml::parse(file_get_contents($file));

            if(isset($yamlData['imports'])) {
                foreach($yamlData['imports'] as $importFile) {
                    $importFile = $dir.$file;

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