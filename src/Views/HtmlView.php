<?php

namespace Rhubarb\Leaf\Views;

require_once __DIR__."/View.class.php";

/**
 * A view specifically designed to return HTML
 *
 * @author acuthbert
 * @copyright GCD Technologies 2013
 */
use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\Context;
use Rhubarb\Leaf\LayoutProviders\LayoutProvider;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\PresenterDeploymentPackage;
use Rhubarb\Leaf\Views\Validation\Placeholder;

class HtmlView extends View
{
	/**
	 * True if the view requires the presenter state to be displayed as hidden inputs after the content.
	 *
	 * This can be set to false if:
	 *
	 * 1) You don't need to manipulate state (e.g. just displaying some data)
	 * 2) The state is represented in some other form (e.g. a TextBox holds it's state in the textbox input)
	 *
	 * @var bool
	 */
	protected $_requiresStateInputs = true;

	/**
	 * True if the view should output a container <DIV> tag surrounding the view.
	 *
	 * This is often required by client side view bridges however is sometimes unnecessary or unwanted.
	 *
	 * @var bool
	 */
	protected $_requiresContainer = true;

	protected function CreateFieldsetLegend( $legend )
	{
		return '<legend class="form-legend">'.$legend.'</legend>';
	}

	public function getDeploymentPackage()
	{
		$package = new PresenterDeploymentPackage();
		$package->resourcesToDeploy = [ __DIR__."/HtmlViewBridge.js" ];

		return $package;
	}

	/**
	 * Returns the name of the client side presenter bridge to attach to this presenter.
	 *
	 */
	protected function getClientSideViewBridgeName()
	{
		return "HtmlViewBridge";
	}

	/**
	 * Prints a collection of Inputs within a fieldset and <dl>
	 *
	 * This method takes a legend string and then any number of string or array parameters
	 *
	 * The string are outputted as HTML directly into the fieldset while the arrays contain
	 * column information to rendered as a form.
	 *
	 * You can supply a simple array of column names:
	 *
	 * array( "Title", "Forename", "Surname" );
	 *
	 * Or you can return an associative array with the key being the label and the
	 * value being either the string name of the field, or an actual input object.
	 *
	 * $title = new PgFormInputSelect( "Title" );
	 * array( "Title" => $title, "Email Marketing" => "NoMail" );
	 *
	 * Or you can do both:
	 *
	 * array( "Title" => $title, "Forename", "Surname" );
	 *
	 * Additionally, if the key or value in the array cannot be mapped to a field input
	 * the value will be treated as HTML and outputted directly. e.g.
	 *
	 * array( "<i>Wow HTML in a dd!</i>", "My Label" => "<b>My HTML</b>" );
	 *
	 * Note that while this base class provides SaveRecord and CancelRecord methods
	 * for you to target with submitFunctions, you need to create and display the
	 * save and cancel buttons yourself.
	 *
	 * The best practice is to create a standard base class for your project to provide
	 * these and any other framing.
	 *
	 * @param mixed $legend The title of the field set. If blank, no legend will be outputted.
	 */
	public function PrintFieldset( $legend = "" )
	{
		$args = func_get_args();

		$layout = $this->GetBoundLayoutProvider();

		call_user_func_array( [ $layout, "printItemsWithContainer"], $args );
	}

	/**
	 * Prints a group of controls
	 *
	 * @see PrintFieldset()
	 */
	public function PrintControlGroup( $inputs = array() )
	{
		$args = func_get_args();

		$layout = $this->GetBoundLayoutProvider();

		call_user_func_array( [ $layout, "printItems"], $args );
	}

	/**
	 * Gets a LayoutProvider that is bound to this view.
	 *
	 * @return LayoutProvider
	 */
	protected function GetBoundLayoutProvider()
	{
		$layoutProvider = LayoutProvider::getDefaultLayoutProvider();
		$layoutProvider->setValueGenerationCallBack( function( $name )
		{
			return $this->GetControlByName( $name );
		});

		$layoutProvider->setValidationPlaceholderGenerationCallBack( function( $placeholderName )
		{
			return new Placeholder( $placeholderName, $this );
		});

		return $layoutProvider;
	}

	/**
	 * Generates a control presenter object or parses a template for controls
	 * .
	 * @param string $name A string representing a field name, control name or template string.
	 * @return bool|\Rhubarb\Leaf\Presenters\Controls\ControlPresenter
	 */
	protected final function GetControlByName( $name )
	{
		if ( isset( $this->presenters[ $name ] ) )
		{
			return $this->presenters[ $name ];
		}

		return false;
	}

	protected function GetWrappers()
	{
		$wrappers = $this->_wrappers;

		if ( $this->_requiresStateInputs )
		{
			$wrappers[] = function( $content )
			{
				$id = $this->GetIndexedPresenterPath();

				$request = Context::CurrentRequest();
				$ajaxUrl = $request->UrlPath;

				$viewIndexSuffix = ( $this->_index ) ? "[_".$this->_index."]" : "";

				$url = ( $ajaxUrl ) ? '
<input type="hidden" name="'.$id.'Url" id="'.$id.'Url" value="'.$ajaxUrl.'" />' : '';

				$html = $content . '
<input type="hidden" name="'.$id.'State" id="'.$id.'State" value="'.htmlentities( json_encode( $this->GetState() ) ).'" />'.$url;

				$hostClassName = $this->RaiseEvent( "GetEventHostClassName" );

				if ( $hostClassName != "" )
				{
					$html .= '
	<input type="hidden" name="'.$id.'EventHost" id="'.$id.'EventHost" value="'.htmlentities( $hostClassName ).'" />';
				}

				return $html;
			};
		}

		if ( $this->_requiresContainer )
		{
			$wrappers[] = function( $content )
			{
				$path = $this->GetIndexedPresenterPath();
				$name = $this->presenterName;

				$classes = [ basename( str_replace( "\\", "/", get_class( $this ) ) ) ];

				if ( $this->RaiseEvent( "IsRootPresenter" ) )
				{
					$classes[] = "host";
				}

				$class = "";

				if ( sizeof( $classes ) )
				{
					$class = " class=\"".implode( " ", $classes )."\"";
				}

				$nameAttribute = ( $name ) ? " presenter-name=\"".htmlentities( $name )."\"" : "";

				$html = '<div id="'.$path.'"'.$class.$nameAttribute.'>
'.$content.'
</div>';

				return $html;
			};
		}

		if ( $this->RaiseEvent( "IsRootPresenter" ) )
		{
			$wrappers[] = function( $content )
			{
				$html = '<form method="post" enctype="multipart/form-data">
'.$content.'
</form>';

				return $html;
			};
		}

		$viewBridge = $this->getClientSideViewBridgeName();

		if ( $viewBridge != "" && !Presenter::$rePresenting )
		{
			$indexedPath = $this->GetIndexedPresenterPath();

			// Top level HTML Presenters sometimes don't have a path as they are the root item. In this case they can't
			// support a view bridge anyway so we can ignore this. This is actually quite rare as normally the top
			// level presenter will extend the Form presenter which does have a name and path.
			if ( $indexedPath != "" )
			{
				$wrappers[] = function()
				{
					$deploymentPackage = $this->getDeploymentPackage();
					$urls = $deploymentPackage->GetDeployedUrls();
					$urls = array_merge( $urls, $this->getAdditionalResourceUrls() );

					$jsAndCssUrls = [];

					foreach( $urls as $url )
					{
						if ( preg_match( "/\.js$/", $url ) || preg_match( "/\.css$/", $url ) )
						{
							$jsAndCssUrls[] = $url;
						}
					}

					ResourceLoader::AddScriptCode(
						"new window.gcd.core.mvp.viewBridgeClasses.".$this->getClientSideViewBridgeName()."( '".$this->GetIndexedPresenterPath()."' );",
						$jsAndCssUrls
					);
				};
			}
		}

		return $wrappers;
	}

	/**
	 * Prints a group of controls.
	 *
	 * @see PrintFieldset()
	 * @param array $controls
	 */
	public function PrintControls( $controls = array() )
	{

	}

	protected final function RaiseEventOnViewBridge( $eventName )
	{
		$args = func_get_args();
		array_unshift( $args, $this->presenterPath );
		call_user_func_array( array( '\Rhubarb\Leaf\Presenters\Presenter', "raiseEventOnViewBridge"), $args );
	}

	public function GetRestoredModel()
	{
		$id = $this->presenterPath;

		$request = Context::CurrentRequest();
		$state = $request->Post( $id."State" );

		if ( $state != null )
		{
			if ( is_string( $state ) )
			{
				return json_decode( $state, true );
			}
		}

		return [];
	}
}