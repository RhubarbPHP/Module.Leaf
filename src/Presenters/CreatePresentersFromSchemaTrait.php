<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Stem\Repositories\MySql\Schema\Columns\Decimal;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\Enum;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\MediumText;
use Rhubarb\Stem\Schema\Columns\Boolean;
use Rhubarb\Stem\Schema\Columns\Date;
use Rhubarb\Stem\Schema\Columns\DateTime;
use Rhubarb\Stem\Schema\Columns\Integer;
use Rhubarb\Stem\Schema\Columns\Money;
use Rhubarb\Stem\Schema\Columns\String;
use Rhubarb\Stem\Schema\Columns\Time;
use Rhubarb\Stem\Schema\SolutionSchema;
use Rhubarb\Leaf\Presenters\Controls\CheckBoxes\CheckBox;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;
use Rhubarb\Leaf\Presenters\Controls\Text\NumericTextBox\NumericTextBox;
use Rhubarb\Leaf\Presenters\Controls\Text\Password\Password;
use Rhubarb\Leaf\Presenters\Controls\Text\TextArea\TextArea;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Crown\String\StringTools;

trait CreatePresentersFromSchemaTrait
{
	protected function CreatePresenterByName( $presenterName )
	{
		$restModel = $this->GetRestModel();

		if ( $restModel )
		{
			$class = $restModel->GetModelName();
			$schema = $restModel->GetSchema();
		}
		else
		{
			$restCollection = $this->GetRestCollection();

			$class = $restCollection->GetModelClassName();
			$schema = $restCollection->GetModelSchema();
		}

		// See if the model has a relationship with this name.
		$relationships = SolutionSchema::GetAllOneToOneRelationshipsForModelBySourceColumnName( $class );

		$columnRelationships = false;

		if ( isset( $relationships[ $presenterName ] ) )
		{
			$columnRelationships = $relationships[ $presenterName ];
		}
		else
		{
			if ( $presenterName == $schema->uniqueIdentifierColumnName )
			{
				if ( isset( $relationships[ "" ] ) )
				{
					$columnRelationships = $relationships[ "" ];
				}
			}
		}

		if ( $columnRelationships )
		{
			$relationship = $relationships[ $presenterName ];

			$collection = $relationship->GetCollection();

			$dropDown = new DropDown( $presenterName, "" );
			$dropDown->SetSelectionItems(
				[ [ "", "Please Select" ],
					$collection
				]
			);

			$dropDown->SetLabel( StringTools::WordifyStringByUpperCase( $relationship->GetNavigationPropertyName() ) );

			return $dropDown;
		}

		$columns = $schema->GetColumns();

		if ( !isset( $columns[ $presenterName ] ) )
		{
			return null;
		}

		$column = $columns[ $presenterName ];

		// Checkbox
		if ( $column instanceof Boolean )
		{
			return new CheckBox( $presenterName );
		}

		// Date
		if ( $column instanceof Date || $column instanceof DateTime )
		{
			return new \Rhubarb\Leaf\Presenters\Controls\DateTime\Date( $presenterName );
		}

		// Time
		if ( $column instanceof Time )
		{
			$textBox = new \Rhubarb\Leaf\Presenters\Controls\DateTime\Time( $presenterName );
			return $textBox;
		}

		// Drop Downs
		if ( $column instanceof Enum )
		{
			$dropDown = new DropDown( $presenterName, $column->defaultValue );
			$dropDown->SetSelectionItems(
				[ [ "", "Please Select" ],
					$column
				]
			);

			return $dropDown;
		}

		// TextArea
		if ( $column instanceof MediumText )
		{
			$textArea = new TextArea( $presenterName, 5, 40 );

			return $textArea;
		}

		// TextBoxes
		if ( $column instanceof String )
		{
			if ( stripos( $presenterName, "password" ) !== false )
			{
				return new Password( $presenterName );
			}

			$textBox = new TextBox( $presenterName );
			$textBox->SetMaxLength( $column->stringLength );

			return $textBox;
		}

		// Decimal
		if( $column instanceof Decimal || $column instanceof Money )
		{
			$textBox = new NumericTextBox( $presenterName, 5 );

			return $textBox;
		}

		// Int
		if( $column instanceof Integer )
		{
			$textBox = new TextBox( $presenterName );
			$textBox->SetSize( 5 );

			return $textBox;
		}

		return parent::CreatePresenterByName( $presenterName );
	}
} 