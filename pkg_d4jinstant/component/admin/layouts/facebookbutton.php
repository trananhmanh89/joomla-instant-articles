<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
?>

<button id="facebook-btn"data-toggle="modal" class="btn btn-small btn-primary">
	<span class="icon-facebook2" title=""></span> Post to Facebook
</button>

<script type="text/x-template" id="modal-template">
<div id="facebookModal" class="modal hide fade" style="max-width: 500px;margin-left: -20%; top: 25%;">
	<div class="modal-header">
		<button type="button" class="close novalidate" data-dismiss="modal">×</button>
		<h3>Message</h3>
	</div>
	<div class="modal-body">
		<div class="row-fluid fbmodal-body">
			<textarea placeholder="Type your message here" style="width:92%; height: 200px;margin-left: 10px;" class="textarea-message"></textarea>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-small loading-btn hide">
			<span class="loading-icon" ></span> 
			<span class="loading-text"></span>
		</button>
		<button class="btn btn-small btn-success post-btn" id="post-to-facebook">
			Post
		</button>
	</div>
</div>
</script>

<script>
	jQuery(document).ready(function ($) {
		$('body').append($('#modal-template').html());
		$('#facebook-btn').on('click', function () {
			$('#facebookModal').modal('show');
			$('.textarea-message').removeAttr('disabled').val('').show().focus();
			$('.post-btn').removeAttr('disabled');
			$('.loading-btn').hide();
			$('.created-note').remove();
		});

		$('#post-to-facebook').on('click', function () {
			var ajaxdata = {};
			var icon = $('.loading-icon'), text = $('.loading-text');
			var loading_btn = $('.loading-btn');
			var post_btn = $('.post-btn');
			var textarea_message = $('.textarea-message');
			var url;
			var fb_id = [];
			post_btn.attr('disabled', 'disabled');
			textarea_message.attr('disabled', 'disabled');
			loading_btn.show();
			icon.removeClass('icon-save').addClass('spinner icon-spinner11');
			text.text('');
			ajaxdata = d4jajax.data;
			ajaxdata.do = 'd4jpost';
			ajaxdata.msg = textarea_message.val();
			$.ajax({
				url: d4jajax.url,
				method: 'post',
				data: ajaxdata,
				dataType: 'json',
				success: function (json) {
					icon.removeClass('spinner icon-spinner11');
					if (json.notice) {
						icon.addClass('icon-save');
						text.text('Successfully');
						fb_id = json.postid.id.split('_');
						url = '<p class="created-note" style="margin-left: 10px;font-size: 18px">Your post is created. ';
						url += '<a target="_blank" href="https://www.facebook.com/permalink.php?story_fbid=' + fb_id[1] + '&id=' + fb_id[0] + '">';
						url += 'Click here';
						url += '</a></p>';
						$('.fbmodal-body').prepend(url);
						textarea_message.hide();
					} else if (json.error) {
						icon.addClass('icon-cancel');
						text.text('Failed');
						alert(json.error[0]);
						post_btn.removeAttr('disabled');
						textarea_message.removeAttr('disabled');
					}
				}
			});

		});
	});
</script>