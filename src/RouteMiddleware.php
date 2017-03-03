<?php

namespace PseudoStatic;


use Symfony\Component\Yaml\Yaml;

class RouteMiddleware
{
    protected $projectRoot;

    protected $url;

    protected $adminActions = [];

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

    public function addAdminAction($urlParam, $action) {
        $this->adminActions[$urlParam] = $action;
    }

    public function addAdminActions(array $actionCollection) {
        foreach ($actionCollection as $trigger => $action) {
            $this->addAdminAction($trigger, $action);
        }
    }

    public function executeAdmin() {
        $actionTrigger = str_replace('admin/', '', $this->url);

        if(isset($this->adminActions[$actionTrigger])) {
            $this->adminActions[$actionTrigger]();
        }
    }
}