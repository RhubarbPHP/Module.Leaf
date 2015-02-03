<?php

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__."/../PresenterViewBase.class.php";
require_once __DIR__."/../../Core/IGeneratesResponse.class.php";

/**
 * The base class for presenters
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\Context;
use Rhubarb\Crown\Exceptions\ImplementationException;
use Rhubarb\Crown\IGeneratesResponse;
use Rhubarb\Stem\Exceptions\ModelConsistencyValidationException;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Models\Validation\Validator;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;
use Rhubarb\Leaf\PresenterViewBase;
use Rhubarb\Leaf\Views\View;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\Scaffolds\AuthenticationWithRoles\PermissionException;

abstract class Presenter extends PresenterViewBase implements IGeneratesResponse
{
	/**
	 * True if this presenter is the target of the invocation, false if it is a sub presenter.
	 *
	 * Note this is only set in the GenerateResponse() method and so will always be false in the constructor of the
	 * presenter.
	 *
	 * @var bool
	 */
	private $_isExecutionTarget;

	/**
	 * The model for this presenter
	 *
	 * Note that the model is public to allow for unit tests to determine if the
	 * presenter and view are working correctly.
	 *
	 * @var \Rhubarb\Stem\ModelState
	 */
	public $model = null;

	/**
	 * The view for this presenter
	 *
	 * This should only set to an interface so we can replace it for unit testing.
	 *
	 * @var View;
	 */
	protected $view;

	/**
	 * If set this will cause the view to be indexed with the relevant string
	 *
	 * e.g. Forename[2]
	 *
	 * @var string
	 */
	protected $_viewIndex = "";

	/**
	 * True if the view has been configured.
	 *
	 * @var bool
	 */
	private $initialised = false;

	/**
	 * True if events have already been processed.
	 *
	 * @var bool
	 */
	private $eventsProcessed = false;

	/**
	 * A collection of events to run after all other events have ran.
	 *
	 * This is normally used by controls on a view that need to run after all other
	 * updates to the model have taken place.
	 *
	 * Events are queued by calling DelayEvent() and executed by ProcessDelayedEvents();
	 *
	 * @see Presenter::RaiseDelayedEvent()
	 * @see Presenter::ProcessDelayedEvents()
	 *
	 * @var array
	 */
	private $delayedEvents = array();

	/**
	 * A count of the number of presenters hosted on our view.
	 *
	 * We use this to generate unique presenter names for each hosted presenter and that has to
	 * be the responsibility of the presenter, not the view.
	 *
	 * @var int
	 */
	private $hostedPresenterCount = 0;

	/**
	 * Set to true if this presenter is hosted on another presenter.
	 *
	 * @var bool
	 */
	protected $hosted = false;

	/**
	 * Set to true if the presenter does not require the context of it's host in order to process RPC events.
	 *
	 * @var bool
	 */
	protected $atomic = false;

	/**
	 * True if when processing through AJAX this presenter should push it's view back to the client.
	 *
	 * This should only be set to true by calling RePresent()
	 *
	 * @see Presenter::rePresent()
	 * @var bool
	 */
	private $rePresent = false;

	/**
	 * An associative array of any validators that have err'd.
	 *
	 * @var array
	 */
	private $validationErrors = [];

	/**
	 * A collection of sub presenter names used on the view
	 *
	 * Used to ensure presenter names are unique - but only if there is more than
	 * 1 on the view.
	 *
	 * @var array
	 */
	private $subPresenterNamesUsed = [];

	/**
	 * A collection of events to be called on the view bridge on an AJAX response.
	 *
	 * @var array
	 */
	protected static $_viewBridgeEvents = [];

	/**
	 * If we need the presenter to be available but not actually printed
	 * @var bool
	 */
	public $suppressContent = false;

	public function __construct( $name = "" )
	{
		$this->model =  new PresenterModel();
		$this->model->PresenterName = $name;
		$this->model->PresenterPath = $name;
	}


	/**
	 * Returns the unique path to identify this presenter amongst the hierarchy of sub presenters forming the complete view.
	 *
	 * Presenter paths become import where state storage and AJAX post backs are involved.
	 *
	 * @return string
	 */
	public function getPresenterPath()
	{
		return $this->model->PresenterPath;
	}

	/**
	 * Returns true to indicate this presenter is flagged for representing
	 *
	 * Used for unit testing.
	 *
	 * @return bool
	 */
	public function NeedsRePresented()
	{
		return $this->rePresent;
	}

	/**
	 * Sets the presenter path.
	 *
	 * This method is private as only a hosting presenter should be able to set this.
	 *
	 * @param $path
	 */
	protected function SetPresenterPath( $path )
	{
		$this->model->PresenterPath = $path;
	}

	/**
	 * Gets the name, if any, of this presenter.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->model->PresenterName;
	}

	/**
	 * Allows the presenter name to be changed.
	 *
	 * Only used internally to make sure presenter names are unique.
	 *
	 * @see SetSubPresenterPath()
	 * @param $presenterName
	 */
	protected function SetName( $presenterName )
	{
		$this->model->PresenterName = $presenterName;
	}

	public function GetValidationErrorsByName( $name )
	{
		$returnErrors = [];

		if ( isset( $this->validationErrors[ $name ] ) )
		{
			$returnErrors[] = $this->validationErrors[ $name ];
		}

		return $returnErrors;
	}

	/**
	 * Performs the validation supplied and if it errors, stores the resultant error in the $validationErrors array.
	 *
	 * @param \Rhubarb\Stem\Models\Validation\Validator $validator
	 * @return bool True if the validation succeeded. False if it didn't
	 */
	public function Validate( Validator $validator )
	{
		try
		{
			$validator->Validate( $this->model );

			return true;
		}
		catch( ModelConsistencyValidationException $er )
		{
			$this->validationErrors[] = $er->GetErrors();
		}

		return false;
	}

	/**
	 * Delays an event until after other events have processed.
	 *
	 * This is normally used by controls on a view that need to run after all other
	 * updates to the model have taken place.
	 *
	 * Events are queued by calling DelayEvent() and executed by ProcessDelayedEvents();
	 *
	 * @see Presenter::ProcessDelayedEvents()
	 * @param string $event The event code
	 * @return void
	 */
	protected function RaiseDelayedEvent( $event )
	{
		$args = func_get_args();

		$this->delayedEvents[] = $args;
	}

	/**
	 * Executes events delayed with DelayEvent
	 *
	 * @see Presenter::RaiseDelayedEvent()
	 */
	public final function ProcessDelayedEvents()
	{
		foreach( $this->delayedEvents as $event )
		{
			call_user_func_array( array( $this, "RaiseEvent" ), $event );
		}

		$this->delayedEvents = array();

		$this->view->ProcessDelayedEvents();
	}

	protected function CreateDefaultValidator()
	{
		// Empty validator that will always pass.
		return new Validator();
	}

	/**
	 * Creates and sets a sub presenters path.
	 *
	 * @param Presenter $subPresenter
	 */
	private function SetSubPresenterPath( Presenter $subPresenter )
	{
		$this->hostedPresenterCount++;

		$subPresenterName = $subPresenter->getName();

		if ( $subPresenterName == "" || in_array( $subPresenterName, $this->subPresenterNamesUsed ) )
		{
			$subPresenterName .= $this->hostedPresenterCount;
			$subPresenter->SetName( $subPresenterName );
		}

		$path = $this->getPresenterPath()."_".$subPresenterName;
		$subPresenter->SetPresenterPath( $path );

		$this->subPresenterNamesUsed[] = $subPresenterName;
	}

	protected $subPresenters = [];

	public function AddSubPresenter( Presenter $presenter )
	{
		$this->SetSubPresenterPath( $presenter );

		$presenter->attachEventHandler( "GetIndexedPresenterPath", function()
		{
			return $this->getIndexedPresenterPath();
		});

		$presenter->attachEventHandler( "GetBoundData", function( $dataKey, $viewIndex = false )
		{
			return $this->getDataForPresenter( $dataKey, $viewIndex );
		} );

		$presenter->attachEventHandler( "GetData", function ( $dataKey, $viewIndex = false )
		{
			return $this->getData( $dataKey, $viewIndex );
		} );

		$presenter->attachEventHandler( "GetModel", function ()
		{
			return $this->GetModel();
		} );

		$presenter->attachEventHandler( "SetData", function( $dataKey, $data, $viewIndex = false )
		{
			$this->SetData( $dataKey, $data, $viewIndex );
		} );

		$presenter->attachEventHandler( "SetBoundData", function( $dataKey, $data, $viewIndex = false )
		{
			$this->SetDataFromPresenter( $dataKey, $data, $viewIndex );
		} );

		$presenter->Initialise();
		$presenter->hosted = true;
		$presenter->OnHosted();

		$this->OnPresenterAdded( $presenter );

		$this->subPresenters[ $presenter->getName() ] = $presenter;

		return $presenter;
	}

	/**
	 * Provides an opportunity for presenters to create sub presenters for their view based on a string name
	 *
	 * @param $presenterName
	 * @return null
	 */
	protected function CreatePresenterByName( $presenterName )
	{
		return null;
	}

	public final function getIndexedPresenterPath()
	{
		$path = $this->RaiseEvent( "GetIndexedPresenterPath" );

		if ( $path !== null )
		{
			$path .= "_".$this->PresenterName;
		}
		else
		{
			$path = $this->PresenterName;
		}

		if ( ( $this->_viewIndex !== null ) && ( $this->_viewIndex !== "" ) )
		{
			$path .= "(".$this->_viewIndex.")";
		}

		return $path;
	}

	/**
	 * Attaches the view to the presenter.
	 *
	 * @param View $view
	 */
	protected final function RegisterView( View $view )
	{
		$this->view = $view;

		$this->view->SetName( $this->model->PresenterName );
		$this->view->setPath( $this->model->PresenterPath );

		$view->attachEventHandler( "CreatePresenterByName", function( $presenterName )
		{
			return $this->CreatePresenterByName( $presenterName );
		} );

		$view->attachEventHandler( "GetData", function ( $dataKey, $viewIndex = false )
			{
				return $this->getData( $dataKey, $viewIndex );
			}
		);
		$view->attachEventHandler( "GetModel", function ()
			{
				return $this->GetModel();
			}
		);

		$view->attachEventHandler( "GetIndexedPresenterPath", function()
		{
			return $this->getIndexedPresenterPath();
		});

		$view->attachEventHandler( "OnPresenterAdded", function( Presenter $presenter )
		{
			return $this->AddSubPresenter( $presenter );
		} );

		$view->attachEventHandler( "GetValidationErrors", function( $validationName )
		{
			return $this->GetValidationErrorsByName( $validationName );
		} );

		$view->attachEventHandler( "IsRootPresenter", function()
		{
			return ( $this->_isExecutionTarget && !$this->hosted );
		});

		$view->attachEventHandler( "GetEventHostClassName", function()
		{
			return $this->GetEventHostClassName();
		});

		$view->attachEventHandler( "GetModelState", function()
		{
			return $this->GetModelState();
		});

		$this->OnViewRegistered();
	}

	/**
	 * Triggers an event on the client side for this presenter.
	 *
	 * Note that this approach should be seldom used as it violates a core principle of MVP; that the presenter
	 * should not know any specifics about the view. If you're attempting to raise an event on the view bridge you
	 * are coupling this presenter with a specific view and so there are usually better approaches to the problem.
	 *
	 * @param $presenterPath
	 * @param $eventName
	 */
	public static function raiseEventOnViewBridge( $presenterPath, $eventName )
	{
		self::$_viewBridgeEvents[] = func_get_args();
	}

	/**
	 * Returns an array of state data which the view bridge can use
	 *
	 * @return array
	 */
	protected function GetModelState()
	{
		return $this->GetPublicModelData();
	}

	protected function OnViewRegistered()
	{

	}

	/**
	 * Called when a presenter has been added to this presenters view.
	 *
	 * @param Presenter $presenter
	 */
	protected function OnPresenterAdded( Presenter $presenter )
	{

	}

	/**
	 * Called once the presenter has been successfully added to a hosting presenter's view.
	 *
	 * Can be used to initiate activity that requires event hookups to be in place.
	 */
	protected function OnHosted()
	{
	}

	/**
	 * Override to initialise the presenter with it's model, and any other relevant settings.
	 *
	 * The view should not be instantiated or configured here however - do this in ApplyModelToView
	 */
	protected function initialiseModel()
	{

	}

	/**
	 * Where relevant a presenter may realise another present is better doing the
	 * work for the given request.
	 */
	protected function GetSubPresenter()
	{

	}

	/**
	 * Pass Display Identifier from View
	 *
	 * @return string
	 */
	public function GetDisplayIdentifier()
	{
		return $this->view->GetDisplayIdentifier();
	}

	/**
	 * Override this to configure how your model is applied to your view.
	 *
	 * The view should be created first through CreateView()
	 *
	 * @see Presenter::createView()
	 */
	protected function applyModelToView()
	{
		$this->view->SetIndex( $this->_viewIndex );
	}

	public final function ApplyModelsToViews()
	{
		$this->applyModelToView();
		$this->view->ApplyModelsToViews();
		$this->OnModelAppliedToView();
	}

	protected function OnModelAppliedToView()
	{

	}

	/**
	 * Returns the list of properties that should appear in the model.
	 *
	 * This does seem like duplicated effort as ModelState has a similar convention however the burden of creating
	 * a separate model object for every presenter just to set this data is overkill
	 *
	 * @return array
	 */
	protected function getPublicModelPropertyList()
	{
		return [ "PresenterName", "PresenterPath" ];
	}

	/**
	 * Returns an array of model data permitted for sending to a client.
	 *
	 * @see getPublicModelPropertyList()
	 * @return array
	 */
	protected final function GetPublicModelData()
	{
		$publicProperties = $this->getPublicModelPropertyList();
		$data = $this->model->ExportRawData();

		$publicData = [];

		foreach( $publicProperties as $property )
		{
			if ( isset( $data[ $property ] ) )
			{
				$publicData[ $property ] = $data[ $property ];
			}
		}

		return $publicData;
	}

	/**
	 * Returns true if the presenter has been configured such that it can't be re-instantiated perfectly
	 * from the public model.
	 *
	 * @return bool
	 */
	public function IsConfigured()
	{
		$properties = $this->getPublicModelPropertyList();

		$data = $this->model->ExportRawData();

		$keys = array_keys( $data );

		$result = array_diff( $keys, $properties );

		if ( sizeof( $result ) > 0 )
		{
			return true;
		}

		return $this->HasExternallyAttachedEventHandlers();
	}

	/**
	 * Replaces the view with a version suitable for unit testing.
	 *
	 * @param View $mockView
	 */
	public final function AttachMockView( View $mockView )
	{
		$this->RegisterView( $mockView );
		$this->Initialise();
	}

	/**
	 * Called to create and register the view.
	 *
	 * The view should be created and registered using RegisterView()
	 * Note that this will not be called if a previous view has been registered.
	 *
	 * @see Presenter::RegisterView()
	 */
	protected function createView()
	{

	}

	/**
	 * Called to initialise the view.
	 *
	 * This method should be used to attach any event handlers. The view must first be created
	 * using CreateView
	 *
	 * Do not apply any settings that might be overriden with default values in ApplyModelToView() or that
	 * need to use the model. The reason for this is that after the view is initialised events are
	 * processed that might change the model or view directly. Just before presenting the view we
	 * call ApplyModelToView() to apply any remaining settings to the view (usually model data). If we apply the
	 * model data too early it will be re-applied just before presentation with results that can sometimes
	 * be hard to predict.
	 *
	 * @see Presenter::createView()
	 * @see Presenter::UpdateView()
	 */
	protected function configureView()
	{
		$this->view->suppressContent = $this->suppressContent;
	}

	public function setSuppressContent($suppress)
	{
		$this->view->suppressContent = $suppress;
		$this->suppressContent = $suppress;
	}

	/**
	 * Call to make sure this presenter pushes it's view back to the client.
	 */
	public function rePresent()
	{
		$this->rePresent = true;
	}

	protected function GetEventHostClassName()
	{
		if ( $this->atomic )
		{
			return get_class( $this );
		}

		return "";
	}

	public static $rePresenting = false;

	/**
	 * Your implementation should create and configure a view class
	 * and call it's PrintContent() method
	 */
	protected function Present()
	{
		if ( $this->view == null )
		{
			throw new ImplementationException( "Your presenter has no view." );
		}

		$this->FetchBoundData();
		$this->beforeRenderView();
		$this->applyModelToView();
		$this->OnModelAppliedToView();

		print $this->view->RenderView();
	}

	/**
	 * Called just before the view is rendered.
	 *
	 * Guaranteed to only be called once during a normal page execution.
	 */
	protected function beforeRenderView()
	{

	}

	/**
	 * Dispatches a command to a function that can deal with it.
	 *
	 * Functions to handle commands should be of the form Command{CommandName}
	 * e.g CommandDeleteCustomer.
	 *
	 * This convention means all presenter commands are alphabetically grouped in the IDE
	 * inspectors and guarantees the
	 *
	 * All arguments apart from the first are passed to the function.
	 *
	 * @param $command
	 */
	public final function DispatchCommand( $command )
	{
		if ( !$this->initialised )
		{
			// Make sure the presenter is initialised.
			$this->Initialise();
		}

		$functionName = "Command".$command;

		$args = func_get_args();
		$args = array_slice( $args, 1 );

		if ( method_exists( $this, $functionName ) )
		{
			call_user_func_array( array( $this, $functionName ), $args );
		}
	}

	/**
	 * Parses the request for any command actions.
	 */
	protected function parseRequestForCommand()
	{

	}

	/**
	 * Determines if any events are due for processing.
	 *
	 * Also asks the view to do the same.
	 */
	public final function ProcessUserInterfaceEvents()
	{
		if ( !$this->initialised )
		{
			// The presenter must be initialised before we can process events.
			$this->Initialise();
		}

		$path = $this->getIndexedPresenterPath();

		$request = Context::CurrentRequest();

		$postData = is_array( $request->PostData ) ? $request->PostData : [];
		$filesData = is_array( $request->FilesData ) ? $request->FilesData : [];

		$postData = array_merge( $postData, $filesData );

		$indexes = [];

		foreach( array_keys( $postData ) as $key )
		{
			// Look for a pattern like Presenter_Path(3)
			if ( preg_match( '/^'.$path.'\(([^)]+)\)/', $key, $match ) )
			{
				$matchingIndex = $match[1];

				if ( !in_array( $matchingIndex, $indexes ) )
				{
					$indexes[] = $matchingIndex;
				}
			}
		}

		if ( sizeof( $indexes ) > 0 )
		{
			foreach( $indexes as $index )
			{
				$this->_viewIndex = $index;

				$this->parseRequestForCommand();
				$this->view->ProcessUserInterfaceEvents();
				$this->ParseRequestForEvent();
			}

			$this->_viewIndex = "";
		}
		else
		{
			$this->parseRequestForCommand();
			$this->view->ProcessUserInterfaceEvents();
			$this->ParseRequestForEvent();
		}
	}

	/**
	 * Looks for an event that should be raised on this presenter within the HTTP request data.
	 *
	 * An event is recognised if the _mvpEventTarget matches this presenter's path. If it does
	 * the event name should be stored in _mvpEventName
	 */
	private function ParseRequestForEvent()
	{
		if ( !isset( $_REQUEST[ "_mvpEventTarget" ] ) )
		{
			return;
		}

		$targetWithoutIndexes = preg_replace( "/\([^)]+\)/", "", $_REQUEST[ "_mvpEventTarget" ] );

		if ( stripos( $targetWithoutIndexes, $this->model->PresenterPath ) !== false )
		{
			$requestTargetParts = explode( "_", $_REQUEST[ "_mvpEventTarget" ] );
			$pathParts = explode( "_", $this->model->PresenterPath );

			if ( preg_match( "/\(([^)]+)\)/", $requestTargetParts[ count( $pathParts ) - 1 ], $match ) )
			{
				$this->_viewIndex = $match[1];
			}
		}

		if ( $targetWithoutIndexes == $this->model->PresenterPath )
		{
			$eventName = $_REQUEST[ "_mvpEventName" ];
			$eventTarget = $_REQUEST[ "_mvpEventTarget" ];
			$eventArguments = [ $eventName ];

			if ( isset( $_REQUEST[ "_mvpEventArguments" ] ) )
			{
				foreach( $_REQUEST[ "_mvpEventArguments" ] as $argument )
				{
					$eventArguments[] = json_decode( $argument );
				}
			}

			// Provide a callback for the event processing.
			$eventArguments[] = function( $response ) use ($eventName, $eventTarget)
			{
				if ( $response === null )
				{
					return;
				}

				$type = "";

				if ( is_object( $response ) || is_array( $response ) )
				{
					$response = json_encode( $response );
					$type = " type=\"json\"";
				}

				print "<eventresponse event=\"".$eventName."\" sender=\"".$eventTarget."\"".$type.">
<![CDATA[".$response."]]>
</eventresponse>";
			};

			// First raise the event on the presenter itself
			call_user_func_array( [ $this, "RaiseDelayedEvent" ], $eventArguments );

			$this->view->SetIndex( $this->_viewIndex );

			// Now raise the event on the view
			call_user_func_array( [ $this->view, "ReceivedEventPassThrough" ], $eventArguments );
		}
	}

	/**
	 * Manipulates the presenter name and path to allow multiple instances of the presenter to live on
	 * the same page
	 *
	 * @param $index
	 */
	public final function displayWithIndex( $index )
	{
		$this->_viewIndex = $index;

		print ( string ) $this;
	}

	/**
	 * Manipulates the presenter name and path to allow multiple instances of the presenter to live on
	 * the same page and returns the HTML for use in a host page.
	 *
	 * @param $index
	 */
	public final function GetHtmlForIndex( $index )
	{
		$this->_viewIndex = $index;

		return ( string ) $this;
	}

	public final function __toString()
	{
		try
		{
			$response = $this->GenerateResponse();

			if ( !is_string( $response ) )
			{
				return $response->GetContent();
			}

			return $response;
		}
		catch( \Exception $er )
		{
			return $er->getMessage();
		}
	}

	public final function GetChangedPresenterModels()
	{
		$models = [];

		if ( $this->model->HasChanged() )
		{
			$models[ $this->getPresenterPath() ] = $this->GetPublicModelData();
		}

		$models = array_merge( $models, $this->view->GetChangedPresenterModels() );

		return $models;
	}

	public final function RecursiveRePresent()
	{
		if ( $this->rePresent )
		{
			$context = new Context();

			// Note we're bypassing the magic feature for performance.
			if ( !$context->GetIsAjaxRequest() )
			{
				// If we're an ajax request and this presenter hasn't been asked to
				// re-present itself, we do nothing as that makes no sense.
				return;
			}

			ob_start();

			self::$rePresenting = true;
			$this->Present();
			self::$rePresenting = false;

			$html = ob_get_clean();

			$html = "<htmlupdate id=\"".$this->model->PresenterPath."\">
<![CDATA[".$html."]]>
</htmlupdate>";

			print $html;
		}
		else
		{
			// Note that we don't need to call RecursiveRePresent if we are RePresenting ourselves
			// as that will naturally re present all sub presenters.

			$this->view->RecursiveRePresent();
		}
	}

	/**
	 * Initiates simplified processing to assist in unit testing.
	 *
	 * Execute this method if you have a presenter as a SUT that you need to invoke to perform your tests.
	 *
	 * @return string The view content printed as a consequence of the test.
	 */
	public function Test()
	{
		$this->Initialise();

		try
		{
			$this->ProcessEvents();
		}
		catch( RequiresViewReconfigurationException $er )
		{
			$this->InitialiseView();
		}

		ob_start();

		$this->Present();

		return ob_get_clean();
	}

	/**
	 * Override this method to check user authorisation - return false if they are not permitted to view this presenter
	 *
	 * @return bool
	 */
	protected function IsPermitted()
	{
		return true;
	}

	/**
	 * Returns the response for this Presenter
	 *
	 * Normally HTML.
	 *
	 * @param null $request
	 *
	 * @throws \Rhubarb\Crown\Scaffolds\AuthenticationWithRoles\PermissionException
	 * @return string
	 */
	public final function GenerateResponse( $request = null )
	{
		$context = new Context();

		$isAjax = $context->GetIsAjaxRequest();

		// Make sure any event processing is deferred to the correct class if the class is specified.
		// This is used when the presenter is marked as atomic and so does not require the context of it's
		// parent to be able to handle the event.
		//
		// Should events be slower than necessary the first thing to consider is whether the presenter involved can
		// be flagged as atomic or redesigned so that it can be flagged as atomic.
		if ( $isAjax && $request && ( $className = $request->Post( "_mvpEventClass" ) ) && ( $className != get_class( $this ) ) )
		{
			if( !$this->IsPermitted() )
			{
				throw new PermissionException();
			}

			$correctPresenter = new $className();
			$correctPresenter->SetPresenterPath( $request->Post( "_mvpEventPresenterPath" ) );

			return $correctPresenter->GenerateResponse( $request );
		}


		/** @var array $modelChanges Keeps track of changes in any model or sub model. */
		$modelChanges = [];

		// If $request is passed in we are being targeted by the request itself.
		$this->_isExecutionTarget = ( $request != null );

		if( $this->_isExecutionTarget && !$this->IsPermitted() )
		{
			throw new PermissionException();
		}

		$this->Initialise();

		ob_start();

		if ( $this->_isExecutionTarget )
		{
			if ( $isAjax )
			{
				print "<?xml version=\"1.0\"?><mvp>\r\n";
			}

			try
			{
				$this->ProcessEvents();
			}
			catch( RequiresViewReconfigurationException $er )
			{
				$this->InitialiseView();
			}
		}

		if ( $this->_isExecutionTarget && $isAjax )
		{
			$this->RecursiveRePresent();
			$modelChanges = $this->GetChangedPresenterModels();
		}
		else
		{
			$this->Present();
		}

		$html = ob_get_clean();

		$response = $this->OnResponseGenerated( $html );

		if ( $response !== null && $response !== false )
		{
			$html = $response;
		}

		if ( $isAjax )
		{
			$response = new HtmlResponse( $this );

			if ( $this->_isExecutionTarget )
			{
				foreach( $modelChanges as $path => $modelChange )
				{
					$html .= "<model id=\"".$path."\"><![CDATA[".json_encode( $modelChange )."]]></model>";
				}

				foreach( self::$_viewBridgeEvents as $eventParams )
				{
					$html .= '<event name="'.htmlentities( $eventParams[1] ).'" target="'.htmlentities( $eventParams[0] ).'">';

					for( $i = 2; $i < sizeof( $eventParams ); $i++ )
					{
						$html .= '<param><![CDATA['.$eventParams[ $i ].']]></param>';
					}

					$html .= '</event>';
				}

				$scripts = ResourceLoader::GetResourceInjectionHtml();

				$html .= $scripts;

				$html .= "</mvp>";
			}

			$response->SetContent( $html );
			$response->SetHeader( "Content-Type", "text/xml" );

			return $response;
		}

		return $html;
	}

	/**
	 * Override this method to execute code after the response has been generated.
	 *
	 * It will have the opportunity to modify the return HTML by returning the adapted
	 * HTML string.
	 */
	protected function OnResponseGenerated( $html )
	{
		return false;
	}

	/**
	 * Process events and then updates the view.
	 */
	private function ProcessEvents()
	{
		$this->ApplyModelsToViews();
		$this->ProcessUserInterfaceEvents();
		$this->ProcessDelayedEvents();
	}

	/**
	 * Initialises the presenter's model, view and any hosted presenters
	 */
	public final function Initialise()
	{
		if ( !$this->initialised )
		{
			$this->initialised = true;
			$this->initialiseModel();
			$this->InitialiseView();
			$this->RestoreModel();

			// Snapshot the model so we can track if it changes during execution.
			$this->model->TakeChangeSnapshot();
		}
	}

	/**
	 * First creates and then configures the view.
	 *
	 * @see Presenter::createView()
	 * @see Presenter::configureView()
	 * @throws \Rhubarb\Leaf\Exceptions\NoViewException
	 */
	protected function InitialiseView()
	{
		if (!$this->view)
		{
			$response = $this->createView();

			if ( $response instanceof View )
			{
				$this->RegisterView( $response );
			}
		}

		if (!$this->view)
		{
			throw new \Rhubarb\Leaf\Exceptions\NoViewException();
		}

		$this->hostedPresenterCount = 0;

		$this->configureView();

		$this->view->createPresenters();
	}

	/**
	 * Provides an opportunity to restore model data before being used by the presenter.
	 *
	 * This allows traits to change the behaviour of the model setup without touching the
	 * function hierarchy.
	 *
	 */
	protected function RestoreModel()
	{
		$restoredModelData = $this->view->GetRestoredModel();

		$this->model->MergeRawData( $restoredModelData );
	}

	/**
	 * Takes the data received from the hosting presenter and applies it to the model.
	 *
	 * @param $data
	 */
	protected function ApplyBoundData( $data )
	{
	}

	/**
	 * Extracts data from the model and presents it to the hosting presenter for application to it's own model
	 *
	 * @return string
	 */
	protected function ExtractBoundData()
	{
		return "";
	}

	/**
	 * Get's the raw bound data from the hosting presenter.
	 *
	 * @return mixed|null
	 */
	public final function FetchBoundData()
	{
		$data = $this->RaiseEvent( "GetBoundData", $this->model->PresenterName, $this->_viewIndex );

		if ( $data !== null )
		{
			$this->ApplyBoundData( $data );
		}

		return $data;
	}

	/**
	 * Sends the bound data back to the hosting presenter.
	 */
	public final function SetBoundData()
	{
		$data = $this->ExtractBoundData();

		$this->RaiseEvent( "SetBoundData", $this->model->PresenterName, $data, $this->_viewIndex );
	}

	/**
	 * Sets model data for a sub presenter.
	 *
	 * This implementation simply bubbles the event to this presenters host. Normal
	 * practice is not to override this but instead use the ModelProvider trait which
	 * does.
	 *
	 * @param string $dataKey
	 * @param mixed $data
	 * @internal param \Rhubarb\Leaf\Presenters\Presenter $presenter
	 */
	protected function SetDataFromPresenter( $dataKey, $data, $viewIndex = false )
	{
		$this->RaiseEvent( "SetBoundData", $dataKey, $data, $viewIndex );
	}

	protected function SetData( $dataKey, $data, $viewIndex = false )
	{
		return $this->RaiseEvent( "SetData", $dataKey, $data, $viewIndex );
	}

	/**
	 * Gets model data for a sub presenter.
	 *
	 * This implementation simply bubbles the event to this presenters host. Normal
	 * practice is not to override this but instead use the ModelProvider trait which
	 * does.
	 *
	 * @param string $dataKey
	 * @param bool $viewIndex
	 * @return mixed|null
	 */
	protected function getDataForPresenter( $dataKey, $viewIndex = false )
	{
		return $this->RaiseEvent( "GetBoundData", $dataKey, $viewIndex );
	}

	/**
	 * Gets model data
	 * @param      $dataKey
	 * @param bool $viewIndex
	 *
	 * @return mixed|null
	 */
	protected function getData( $dataKey, $viewIndex = false )
	{
		return $this->RaiseEvent( "GetData", $dataKey, $viewIndex );
	}

	/**
	 * Gets model
	 *
	 * @return null|Model
	 */
	protected function GetModel()
	{
		return $this->RaiseEvent( "GetModel" );
	}

	/**
	 * Provides access to the presenter's model
	 *
	 * @param $name
	 */
	public function __get( $name )
	{
		return $this->model[ $name ];
	}

	/**
	 * Provides access to the presenter's model
	 *
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value)
	{
		$this->model[ $name ] = $value;
	}

	/**
	 * Override this to attach events to another presenter
	 *
	 * @param Presenter $presenter
	 */
	protected function bindEvents( Presenter $presenter )
	{

	}

	/**
	 * Provides an easy way for a presenter to bind events to another presenter.
	 *
	 * This method provides both parties with a chance to register event handlers.
	 *
	 * @param Presenter $presenter
	 */
	public final function BindEventsWith( Presenter $presenter )
	{
		$this->bindEvents( $presenter );
		$presenter->bindEvents( $this );
	}
}
