<?php

namespace Rhubarb\Leaf\Presenters\Controls\DateTime;

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;

/**
 * Class TimeView
 * @package Rhubarb\Leaf\Presenters\Controls\DateTime
 *
 */
class TimeView extends ControlView
{
	private $_minuteInterval = 1;
	private $_hourStart;
	private $_hourEnd;

	function __construct( $minuteInterval = 1, $hourStart = 0, $hourEnd = 23 )
	{
		$this->_minuteInterval = $minuteInterval;
		$this->_hourStart = $hourStart;
		$this->_hourEnd = $hourEnd;
	}

	public function createPresenters()
	{
		$this->addPresenters(
			$hours = new DropDown( "Hours" ),
			$minutes = new DropDown( "Minutes" )
		);

		$hourRange = range( $this->_hourStart, $this->_hourEnd);
		$minuteRange = range( 0, 59, $this->_minuteInterval );

		$pad = function( &$value )
		{
			if( $value < 10 )
			{
				$value = "0".$value;
			}
		};

		array_walk( $hourRange, $pad );
		array_walk( $minuteRange, $pad );

		$hours->SetSelectionItems( $hourRange );
		$minutes->SetSelectionItems( $minuteRange );
	}

	public function printViewContent()
	{
		print $this->presenters[ "Hours" ]." ".$this->presenters[ "Minutes" ];
	}

    protected function getClientSideViewBridgeName()
    {
        return "TimeViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/TimeViewBridge.js";

        return $package;
    }
}