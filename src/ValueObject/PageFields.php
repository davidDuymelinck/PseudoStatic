<?php

namespace PseudoStatic\ValueObject;

class PageFields extends \ArrayObject
{
    public function __construct(array $fields) {
        if(count(array_diff(['url', 'title', 'body'], array_keys($fields))) > 0) {
            throw new \Exception('One of the required fields is missing: url, title or body');
        }

        parent::__construct($fields);
    }

    public function get($key, $default = '') {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $default ;
    }

    public function getUrl($default = '') {
        return $this->get('url', $default);
    }

    public function getTitle($default = '') {
        return $this->get('title', $default);
    }

    public function getBody($default = '') {
        return $this->get('body', $default);
    }

    public function requiredFieldsNotEmpty() {
        return strlen($this->getUrl()) > 0 && strlen($this->getTitle()) > 0 && strlen($this->getBody()) > 0;
    }
}