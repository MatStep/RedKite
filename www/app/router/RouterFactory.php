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
		$router[] = new Route('[<locale=sk sk|cs|en>/]<presenter>/<action>[/<id>]', 'Homepage:default');
		$adminRouter[] = new Route('[<locale=sk sk|cs|en>/]admin/<presenter>/<action>', 'Admin:default');
		return $router;
	}

}
