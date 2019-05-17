<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
?>
<div style="width: 240px;margin: 0 auto;">
<form method="post">
	<label>App ID</label>
	<input type="text" name="appid"/>
    <label>Secret</label>
	<input type="text" name="secret"/>
	<input type="hidden" name="task" value="setup.saveApp" />
	<?php echo JHtml::_('form.token'); ?>
	<br>
	<button type="submit" class="btn btn-success">Save App</button>
</form>
</div>