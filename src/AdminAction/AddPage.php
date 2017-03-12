<?php

namespace PseudoStatic\AdminAction;


class AddPage
{
    function __invoke($request)
    {
        $query = $request->getQueryParams();

        if(is_array($query) && count($query) > 0) {
                return $request->withAttribute('data', $query);
        }

        return $request;
    }
}