<?php
return function (FastRoute\RouteCollector $r) {
    $r->get('/dp/api/show', 'IndexController@show');
};
