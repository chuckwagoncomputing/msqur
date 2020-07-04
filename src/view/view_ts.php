<?php

global $rusefi;
global $id;
global $dialogId;

include_once("view/view_ts_dialog.php");

if (!empty($dialogId)) {
	// security check
	if (preg_match("/[A-Za-z0-9]+/", $dialogId)) {
		$dlgTitle = printDialog($msqMap, $dialogId, FALSE);
		// pass the title back to JS
		echo "<script>dlgTitle = '".$dlgTitle."';</script>\r\n";
	}
	die;
}

ob_start();
?>

<!-- generated by https://loading.io -->
<div id="loading"><img src="view/img/loading.gif" /></div>

<div id="ts-menu-container">
<div id="ts-menu">
<ul id="menu">
<?php 
	$mi = 1;
	$menuItems = array();
	if (isset($msqMap["menu"]))
	foreach ($msqMap["menu"] as $mn=>$menu) {
		$mn = printTsItem($mn);
		if ($mn == "Help") continue;
?>
<li class="tsMenuItem"><img class="tsMenuItemImg" src="view/img/ts-icons/menu<?=$mi;?>.png"><span class="tsMenuItemText"><?=$mn;?></span>
<ul>
<?php
		foreach ($menu["subMenu"] as $sm=>$sub) {
			if ($sub == "std_separator") {
				echo "<li class='tsMenuSeparator' type='separator'></li>\r\n";
			} else {
				$menuItems[] = $sub[0];
				$sm = printTsItem($sub[1]);
				$isDisabled = false;
				//if (isset($sub[3]))
				if (isset($sub[3])) {
					try
					{
						// see INI::parseExpression()
						$isDisabled = !eval($sub[3]);
					} catch (Throwable $t) {
						// todo: should we react somehow?
					}
				}
?>
	<li <?=$isDisabled ? "class=\"ui-state-disabled\"":"";?>><a class="tsMenuItemText tsSubMenuItemText" href="#<?=$sub[0];?>" id="<?=$sub[0];?>"><?=$sm;?></a></li>
<?php
			}
		}
?>
</ul>
</li>
<?php
	$mi++;
	}
?>
</ul>
</div>
</div>

<div id="ts-dialogs">

<?php
//!!!!!!!!
$menuItems = array(/*"engineChars", "injectionSettings"*/);

foreach ($menuItems as $mi) {
	if (isset($msqMap["dialog"][$mi])) {
		$dlg = $msqMap["dialog"][$mi];
		$dlgName = $dlg["dialog"][0][0];
		$dlgTitle = getDialogTitle($msqMap, $dlg);
?>
<div class="tsDialog" id="dlg<?=$dlgName;?>" title="<?=$dlgTitle;?>">
<?php
		printDialog($msqMap, $mi, FALSE);
?>
</div>
<?php
	}
}
?>

</div>

<script>
	var dlgTitle = "";
	////////////////////////////////////////////////////////////////////////////
	// menu
	$("#menu").menu({
		position: {at: "left bottom"},
		icons: { submenu: 'ui-icon-blank' }
	});
	$("#ts-menu").show();
	$(window).resize(function() {
		var avgWidth = (($(document).width() - 40) / $(".tsMenuItem").length) - 10;
		$(".tsMenuItem").each(function () {
			$(this).css("min-width", avgWidth + 'px');
		});
	});

	$('#ts-menu li a').click(function (e) {
		e.preventDefault();
		var dlgId = $(this).attr("id");
		if ($("#dlg" + dlgId).hasClass('ui-dialog-content') === false) {
			$("#loading").show();
			$('#loading').position({my: "center", at: "center", of: window});
			var dlgDiv = $("<div>", {
				id: "dlg" + dlgId,
				title: ""
			});
			dlg = addDialog(dlgDiv, "left top", $("#ts-dialogs"));
			$("#ts-dialogs").prepend(dlg.parent());
			dlg.load('view.php?msq=<?=$id;?>&view=ts&dialog=' + dlgId, function() { 
				dlg.dialog({
					title: dlgTitle
				}).dialog('open');
				fixDialogGroups();
				fixDialogPositions();
				$("#loading").hide();
				findDialog(dlg);
			});
		}
		else {
			findDialog($("#dlg" + dlgId));
		}
	});

	var tsMenuTop = $("#ts-menu").offset().top;
	// float the menu when scrolling
	$(window).scroll(function fix_element() {
    	$('#ts-menu').css(
			$(window).scrollTop() > tsMenuTop
	        	? { 'position': 'fixed', 'top': '2px' }
				: { 'position': 'relative', 'top': 'auto' }
	    );
	    return fix_element;
	}());

	////////////////////////////////////////////////////////////////////////////
	// dialogs

	function findDialog(dlg) {
		var top = dlg.offset().top - 100;
		// the dialog is already opened somewhere, let's find it
		$([document.documentElement, document.body]).animate({
			scrollTop: top
		}, 2000);
	}

	function addDialog(div, at, prevDlg) {
		var dialog = div.dialog({
			modal: false,
			draggable: false,
			resizable: true,//false,
			autoOpen: false,
			dialogClass: 'tsDialogClass',
			prependTo: $("#ts-dialogs"),
			position: { my: "left top", at: at, of: prevDlg, collision: "none" },
			width: 'auto',
			resize: function(event, ui) {
				fixDialogPositions();
			},
			open: function( event, ui ) {
				fixDialogPositions();
			},
			close: function(event, ui) {
				$(this).empty().dialog('destroy');
				fixDialogPositions();
			},
		});
		return dialog;
	}

	function fixDialogGroups() {
		$(".ts-controlgroup-vertical").each(function () {
			$(this).controlgroup({
				"direction": "vertical"
			});
		});
		$(".ts-controlgroup-horizontal").each(function () {
			$(this).controlgroup({
				"direction": "horizontal"
			});
		});
	}

	function fixDialogPositions() {
		var maxWidth = 0;
		var totalHeight = 0;
		var prevDlg = $("#ts-dialogs");
		var at = "left top";
		$("#ts-dialogs>div").each(function () {
			//.top(totalHeight);
			var dlgContent = $(this).find('.ui-dialog-content');
			var dlg = dlgContent.data('ui-dialog');
			/*$(this).resizable({
				handles: "e, s, se",
				resize: function(event, ui) {
					fixDialogPositions();
				},
				alsoResize: dlgContent
			});*/
			dlg.option("resizable", false);
			dlg.option("position", { my: "left top", at: at, of: prevDlg, collision: "none" });
			totalHeight += $(this).height() + 10;
			maxWidth = Math.max(maxWidth, $(this).width());
			prevDlg = $(this);
			at = "left bottom+10";
		});

		$("#ts-dialogs>div").each(function () {
			//$(this).show();
		});

		$("#ts-dialogs").width(maxWidth);
		$("#ts-dialogs").height(totalHeight + 50);
	}

	$("#ts-dialogs").show();
	var prevDlg = $("#ts-dialogs");
	var at = "left top";
	$("#ts-dialogs>div").each(function () {
		var dialog = addDialog($(this), at, prevDlg);
		prevDlg = dialog.parent();
		at = "left bottom+10";
	});

	//alert(totalHeight);

	$(document).ready(function() {

		fixDialogGroups();

		fixDialogPositions();

		//alert($('#ts-dialogs').height());
		//$('#footer').css('top', $('html').height() +'px');
	});
	
	

	$(window).trigger('resize');


</script>



<?php

$html["ts"] = ob_get_contents();
ob_end_clean();

?>