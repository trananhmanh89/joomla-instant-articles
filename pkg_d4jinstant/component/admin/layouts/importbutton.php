<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
?>

<button class="btn btn-small">
	<span class="import-icon icon-spinner11" ></span> 
	<span class="import-text">Importing Instant Article <span class="import-loading">...</span></span>
</button>

<script>
	jQuery(document).ready(function ($) {
		var ajaxdata = {};
		ajaxdata = d4jajax.data;
		ajaxdata.do = 'd4jimport';
		setTimeout(function () {
			$.ajax({
				url: d4jajax.url,
				method: 'post',
				data: ajaxdata,
				dataType: 'json',
				success: function (json) {
					Joomla.renderMessages(json);
					var icon = $('.import-icon'), text = $('.import-text');
					icon.removeClass('icon-spinner11');
					if (json.notice) {
						icon.addClass('icon-save');
						text.text('Import Successfully');
					} else if (json.warning) {
						icon.addClass('icon-pending');
						text.text('Instant Article not live yet');
					} else if (json.error) {
						icon.addClass('icon-notification');
						text.text('Import Failed');
					}
				}
			});
		}, 2000);
	});
</script>
