<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Event\EventHandler;
use Cradle\Http\Router;
use Cradle\Package\Role\Exception;

return function ($request, $response) {
    $permissions = $request->getSession('role', 'role_permissions');

    //make sure permissions is an array
    if (!is_array($permissions)) {
        $permissions = [];
    }

    // path
    $home = $this->package('global')->config('settings', 'home');

    //at least allow the home page
    if (!trim($request->getPath('string'))
        || $request->getPath('string') === $home
    ) {
        return true;
    }

    // initialize router
    $router = new Router;

    // iterate on each permissions
    foreach($permissions as $permission) {
        // validate route
        $router->route(
            $permission['method'],
            $permission['path'],
            function($request, $response) {
                //if good, let's end checking
                return false;
            }
        );
    }

    // process router
    $router->process($request, $response, 1);

    //let's interpret the results
    if($router->getEventHandler()->getMeta() ===  EventHandler::STATUS_INCOMPLETE) {
        //the role passes
        return true;
    }

    $this->response->setFlash(Exception::ERROR_NOT_PERMITTED, 'error');

    throw Exception::forNotPermitted();
};