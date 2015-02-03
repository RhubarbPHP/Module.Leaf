<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\TextArea;

require_once __DIR__."/../TextBox/TextBoxView.class.php";

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;

class TextAreaView extends TextBoxView
{
	public $rows = 5;
	public $cols = 40;

	public function printViewContent()
	{
		?>
		<textarea rows="<?=$this->rows;?>" cols="<?=$this->cols;?>" name="<?= \htmlentities( $this->presenterPath ); ?>" id="<?= \htmlentities( $this->presenterPath ); ?>" presenter-name="<?=\htmlentities( $this->presenterName );?>"<?= $this->GetHtmlAttributeTags().$this->GetClassTag() ?>><?=$this->text;?></textarea>
		<?php
	}
}