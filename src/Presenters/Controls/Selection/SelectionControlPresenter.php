<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection;

require_once __DIR__ . "/../ControlPresenter.class.php";

use Rhubarb\Crown\Context;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\Enum;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

/**
 * A base class for all controls implementing a range of options to pick from.
 *
 * Provides support for manual items, items from an enum column type, from a model collection
 * and callbacks.
 */
class SelectionControlPresenter extends ControlPresenter
{
	protected function initialiseModel()
	{
		parent::initialiseModel();

		if ( !isset( $this->model->SelectedItems ) )
		{
			$this->model->SelectedItems = [];
		}
	}

	protected $_selectionItems = [];

	public function GetSelectionItems()
	{
		return $this->_selectionItems;
	}

	public function SetSelectionItems( array $items )
	{
		$this->_selectionItems = $items;

		return $this;
	}

	protected function SupportsMultipleSelection()
	{
		return false;
	}

	/**
	 * Override this function to get a label for a selected item.
	 *
	 * This is normally only called for the initial render of the page as during searching the labels are already
	 * available. Also there is no sensible default implementation for this function as the meaning of $item
	 * is known only to the overriding class.
	 *
	 * @param $item
	 * @return string
	 */
	protected function GetLabelForItem( $item )
	{
		return "";
	}

	protected function getPublicModelPropertyList()
	{
		$properties = parent::getPublicModelPropertyList();
		$properties[] = "SelectedItems";

		return $properties;
	}

	protected function applyModelToView()
	{
		parent::applyModelToView();

		$this->view->SetAvailableItems( $this->GetCurrentlyAvailableSelectionItems() );
		$this->view->SetSelectedItems( $this->model->SelectedItems );
		$this->view->SetSupportsMultiple( $this->SupportsMultipleSelection() );
	}

	protected function configureView()
	{
		parent::configureView();

		$this->view->attachEventHandler( "UpdateAvailableSelectionItems", function()
		{
			$args = func_get_args();

			call_user_func_array( array( $this, "UpdateAvailableSelectionItems" ), $args );

			return $this->GetCurrentlyAvailableSelectionItems();
		});
	}

	protected function UpdateAvailableSelectionItems( $itemId )
	{

	}

	protected function parseRequestForCommand()
	{
		$request = Context::CurrentRequest();
		$values = $request->Post( $this->getIndexedPresenterPath() );

		if ( $values !== null )
		{
			if ( !is_array( $values ) )
			{
				$values = explode( ",", $values );
			}

			$this->SetSelectedItems( $values );
			$this->SetBoundData();
		}
	}

	/**
	 * Override this function to get the data associated with a selected model item.
	 *
	 * By default this will use all the public data however for efficiency you can return a specific
	 * array of values.
	 *
	 * @param $item
	 * @return Array
	 */
	protected function GetDataForItem( $item )
	{
		if( $item instanceof Model )
		{
			return $item->ExportPublicData();
		}

		return [];
	}

	protected function IsValueSelectable( $value )
	{
		if ( $value === null )
		{
			return false;
		}

		return true;
	}

	/**
	 * If your selection control presenter works with models, this function should return
	 * the appropriate model for a selected value.
	 *
	 * @param $value
	 */
	protected function ConvertValueToModel( $value )
	{
		return $value;
	}

	public function SetSelectedItems( $rawItems )
	{
		if ( is_object( $rawItems ) )
		{
			if ( $rawItems instanceof Model )
			{
				$rawItems = [ $rawItems->UniqueIdentifier ];
			}
		}
		else
		{
			if ( is_int( $rawItems ) || is_bool( $rawItems ) )
			{
				$rawItems = [ $rawItems ];
			}
			elseif ( !is_array( $rawItems ) )
			{
				$rawItems = explode( ",", $rawItems );
			}
		}

		$selectedItems = [];

		foreach( $rawItems as $value )
		{
			if ( !$this->IsValueSelectable( $value ) )
			{
				continue;
			}

			if ( $value === 0 || $value === "0" )
			{
				$item = $this->MakeItem( $value, "", [] );
			}
			else
			{
				if ( !$value instanceof Model )
				{
					$value = $this->ConvertValueToModel( $value );
				}

				$optionValue = ( $value instanceof Model ) ? $value->UniqueIdentifier : $value;

				$item = $this->MakeItem( $optionValue, $this->GetLabelForItem( $value ), $this->GetDataForItem( $value ) );
			}

			$selectedItems[] = $item;
		}

		$this->model->SelectedItems = $selectedItems;
	}

	protected function ApplyBoundData( $data )
	{
		$this->SetSelectedItems( $data );
	}

	private function BuildDataArrayFromSelectedList( $list )
	{
		$data = [];

		foreach( $list as $key => $value )
		{
			if ( is_object( $value ) )
			{
				$value = $value->value;
			}
			else
			{
				$value = $value[ "value" ];
			}

			$data[ $key ] = $value;
		}

		return $data;
	}

	protected function ExtractBoundData()
	{
		// We have to decide how to return the list of selected items.
		// If there is only one selected item we will just return that item, however if there
		// are many items we will return the full array. We'll assume that which ever presenter
		// is processing the bound value will handle detection of the two scenarios.
		// Returning a single value is important to simplify occasions where a selection control is
		// directly bound to a single value column.

		$data = $this->BuildDataArrayFromSelectedList( $this->model->SelectedItems );

		if ($this->SupportsMultipleSelection() )
		{
			return $data;
		}

		if( sizeof( $data ) > 0 )
		{
			return current( $data );
		}
		else
		{
			return "";
		}
	}

	/**
	 * Makes a stdClass to represent an item.
	 *
	 * This will make a standard object with the following properties:
	 *
	 * Value: The value of the item
	 * Label: A text display value for the item
	 * Data: Any other associated item data
	 *
	 * Note that these properties are UpperCamelCase as these objects are often converted directly into
	 * Javascript objects and that best matches our current javascript styles.
	 *
	 * @param $value
	 * @param $label
	 * @param $data
	 * @return \stdClass
	 */
	protected final function MakeItem( $value, $label, $data = [] )
	{
		$item = new \stdClass();
		$item->value = $value;
		$item->label = $label;
		$item->data = $data;

		return $item;
	}

	/**
	 * Returns an array of all the items that should be available for selection.
	 *
	 * @return array
	 */
	protected function GetCurrentlyAvailableSelectionItems()
	{
		$totalItems = [];
		$selectionItems = $this->GetSelectionItems();

		foreach( $selectionItems as $group => $item )
		{
			$items = [];

			if ( $item instanceof Collection )
			{
				foreach( $item as $key => $model )
				{
					$items[] = $this->MakeItem( $key, $model->GetLabel(), $this->GetDataForItem( $model ) );
				}
			}
			elseif ( $item instanceof Enum )
			{
				$enumValues = $item->enumValues;

				foreach( $enumValues as $enumValue )
				{
					$items[] = $this->MakeItem( $enumValue, $enumValue );
				}
			}
			elseif ( is_array( $item ) )
			{
				$value = $item[0];
				$label = ( sizeof( $item ) == 1 ) ? $item[0] : $item[1];

				$data = ( sizeof( $item ) > 2 ) ? $item[2] : [];

				$items[] = $this->MakeItem( $value, $label, $data );
			}
			else
			{
				$items[] = $this->MakeItem( $item, $item );
			}

			if ( is_numeric( $group ) )
			{
				$totalItems = array_merge( $totalItems, $items );
			}
			else
			{
				$groupItem = $this->MakeItem( "", $group );
				$groupItem->Children = $items;

				$totalItems[] = $groupItem;
			}
		}

		return $totalItems;
	}
}