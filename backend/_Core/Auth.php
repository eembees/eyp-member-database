<?php

namespace Core;

class Auth extends \Slim\Middleware {

	public function call() {
		// Get the token from the query
		$authToken = $this->app->request->headers->get('AuthToken');

		if(\Core\User::authenticate($authToken) ||
		   $this->app->request->getResourceUri() === '/login') {
			$this->next->call();
		} else {
			$this->app->response()->status(401);
		}
	}
}