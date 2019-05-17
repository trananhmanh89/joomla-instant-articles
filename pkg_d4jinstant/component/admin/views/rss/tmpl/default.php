<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10 ">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>
					<a href="javascript:">Type</a>
				</th>
				<th>
					<a href="javascript:">Url</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($this->types as $value): ?>
			<tr>
				<td><?php echo ucfirst($value) ?></td>
					<?php 
					$url = JUri::root() . 'index.php?option=com_d4jinstant&view=rss&&type=' . $value;
					?>
				<td>
					<a target="_blank" href="<?php echo $url ?>">
						<?php echo $url ?>
					</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>