<?php

namespace PseudoStatic;


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
        return (new YamlHelper($this->projectRoot))->getArrayFromDir($dataPath);
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