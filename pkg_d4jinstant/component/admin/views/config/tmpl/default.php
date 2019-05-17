<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
JHtml::_('formbehavior.chosen', 'select');
?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10 ">
	<form class="form-horizontal" name="adminForm" id="adminForm" method="post">
		<div class="span6">
			<?php echo $this->form->renderField('content_category') ?>
			
			<?php if($this->k2): ?>
			<?php echo $this->form->renderField('k2_category');	?>
			<?php endif; ?>
			
			<?php if($this->zoo): ?>
			<?php echo $this->form->renderField('zooblogcategory');	?>
			<?php endif; ?>
			
			<?php echo $this->form->renderFieldset('method_config') ?>
		</div>
		<div class="span6">
			<?php echo $this->form->renderFieldset('config') ?>
		</div>
		<input type="hidden" name="task" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

