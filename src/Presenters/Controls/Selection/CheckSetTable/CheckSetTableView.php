<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\CheckSetTable;

use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlView;

/**
 * Horizontally oriented checkboxes
 */
class CheckSetTableView extends SelectionControlView
{
    public function __construct()
    {
        $this->requiresContainer = false;
        $this->requiresStateInputs = false;
    }

    protected function printViewContent()
    {
        $name = $this->getIndexedPresenterPath();
        $name = \htmlentities($name) . '[]';

        $presenterPath = \htmlentities($this->getIndexedPresenterPath());
        $presenterName = \htmlentities($this->presenterName);
        $attributes = $this->getHtmlAttributeTags();

        $checkRow = $headerRow = '';

        foreach ($this->availableItems as $item) {
            if (isset($item->Children)) {
                $itemList = $item->Children;
                $text = \htmlentities($item->label);
                $headerRow .= '<th rowspan="2">' . $text . '</th>';
            } else {
                $itemList = [$item];
            }

            if (count($itemList)) {
                foreach ($itemList as $subItem) {
                    $value = \htmlentities($subItem->value);
                    $text = \htmlentities($subItem->label);
                    $checked = $this->isValueSelected($subItem->value) ? ' checked="checked"' : '';
                    $data = \htmlentities(json_encode($subItem));
                    $headerRow .= '<th>' . $text . '</th>';
                    $checkRow .= <<<HTML
                    <td>
                        <input type="checkbox" name="$name" value="$value"$checked data-item="$data" />
                    </td>
HTML;
                }
            }
        }

        print <<<HTML
		<table id="$presenterPath" presenter-name="$presenterName"$attributes>
			<thead><tr>$headerRow</tr></thead>
			<tbody><tr>$checkRow</tr></tbody>
		</table>
HTML;
    }
}
