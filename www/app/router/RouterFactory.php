<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = $adminRouter = new RouteList('Admin');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		$adminRouter[] = new Route('admin/<presenter>/<action>', 'Admin:default');
		return $router;
	}

}
