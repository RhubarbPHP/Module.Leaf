<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\Presenters\Application\Table\FooterProviders;

require_once __DIR__ . '/FooterProvider.php';

class FooterColumnsFooterProvider extends FooterProvider
{
    private $columns = [];

    public function __construct($columns = [])
    {
        $this->columns = $columns;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    private function inflateColumns()
    {
        $inflatedColumns = [];

        foreach ($this->columns as $column) {
            // $column is either a string, a string with a width or a FooterColumn object
            if (!is_object($column)) {
                $parts = explode(":", $column, 2);

                $text = $parts[0];
                $span = 1;

                if (count($parts) > 1) {
                    $span = (int)$parts[1];
                }

                $column = new LabelFooterColumn($text, $span);
            }

            $inflatedColumns[] = $column;
        }

        return $inflatedColumns;
    }

    public function printFooter()
    {
        print "<tr>";

        $columns = $this->inflateColumns();

        foreach ($columns as $column) {
            print "<th";

            $span = $column->getSpan();

            if ($span > 1) {
                print " colspan=\"" . $span . "\"";
            }

            $classes = $column->getCssClasses();

            if (count($classes) > 0) {
                print " class=\"" . implode(" ", $classes) . "\"";
            }

            print ">" . $column->getCellValue($this->table) . "</th>";

        }

        print "</tr>";
    }
}