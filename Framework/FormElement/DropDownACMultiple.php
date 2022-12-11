<?php

class DropDownACMultiple extends DropDownAC {

	public function getHtml() {
		$id = $this->getId();
		File::includeJs('jquery.js', File::LIB_DIR);
		File::includeJs('jquery-ui.js', File::LIB_DIR);
		File::includeCss('jquery-ui/css/smoothness/jquery-ui.custom.css', File::LIB_DIR);
		File::includeCss('css/ddacm.css', File::LIB_DIR);

		HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
$().ready(function() {
	var ac = $("input#' . $id . '-ac");

	ac.autocomplete({ 
		source: "' . $this->sourceUrl . '",
		mustMatch: true,
		minChars: 2,
		width: ' . $this->size . ',
		select: function( event, ui ) { 
			if (ui.item.id)	{
				$("ul#' . $id . '-values").append(\'<li>\'+ui.item.value+\'<input type="hidden" name="' . $this->getName() . '[]" value="\'+ui.item.id+\'" /> <span class="' . $id . '-remove remove" title="'.Lang::get('delete').'">&nbsp;</span></li>\');
				ac.val("");
				return false;
			}
		},
		change: function( event, ui ) { 
		}
	});

	$("span.' . $id . '-remove").live("click", function() { 
		$(this).parent("li").fadeOut(300, function() { $(this).remove(); } ); 
	});
	$("a#' . $id . '-remove-all").click(function() {
		$("ul#' . $id . '-values li").each(function() {
			$(this).remove();
		});
	});

	$("ul#' . $id . '-values").sortable();

	$("ul#' . $id . '-values li").each(function() {
		var e = $(this);
		$.post("' . $this->sourceUrl . '", {id: e.children("input").val()}, function(data) {
			e.prepend(data[0].value);
		}, "json");
	});
});
</script>');
		
		$ret = '<div class="ddacm">';
		$ret .= '<input type="text" id="' . $id . '-ac" value=""';
		$cssClass = $this->getCssClass();
		$cssClass = ($cssClass) ? $cssClass . ' line' : 'line';
		$ret .= ' class="' . $cssClass . '"';
		if ($this->getReadOnly()) {
			$ret .= ' readonly="readonly"';
		}
		if (isset($this->maxlength)) {
			$ret .= ' maxlength="' . $this->maxlength . '"';
		}
		if ($this->size) {
			$ret .= ' size="' . $this->size . '"';
		}
		$ret .= ' autocomplete="off" />';

		$ret .= '
			<ul id="' . $id . '-values">';
		$values = $this->getValue();	
		if (is_array($values)) {
			foreach ($values as $v) {
				$ret .= '
					<li><input type="hidden" name="' . $this->getName() . '[]" value="' . $v . '" /> <span class="' . $id . '-remove remove" title="' . Lang::get('delete') . '">&nbsp;</span></li>';
			}
		}
		$ret .= '
			</ul>
			<div style="padding:2px"><a href="#" id="' . $id . '-remove-all">' . Lang::get('Delete all') . '</a></div>
			</div>';

		return $ret;
	}
}
