<?php

namespace Rhubarb\Crown\Mvp;

require_once __DIR__."/../Core/Module.class.php";

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
use Rhubarb\Stem\ModellingModule;
use Rhubarb\Crown\Module;
use Rhubarb\Crown\StaticResource\UrlHandlers\StaticResourceUrlHandler;

class MvpModule extends \Rhubarb\Crown\Module
{
	public function __construct()
	{
		$this->namespace = __NAMESPACE__;

		parent::__construct();
	}

	protected function RegisterDependantModules()
	{
		include_once( __DIR__."/../Modelling/ModellingModule.class.php" );
		include_once( __DIR__."/../ClientSide/ClientSideModule.class.php" );
		include_once( __DIR__."/../Integration/IntegrationModule.class.php" );

		Module::RegisterModule( new ModellingModule() );
		Module::RegisterModule( new ClientSideModule() );
		Module::RegisterModule( new IntegrationModule() );
	}

	protected function Initialise()
	{
		parent::Initialise();

		$this->AddUrlHandlers( "/mvp/", new StaticResourceUrlHandler( __DIR__."/ClientSide/Resources/" ) );
	}
}
