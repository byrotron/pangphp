<style type="text/css">
	.error_email {
		margin-top: 15px;
	}
</style>

<p>Hi, <?php echo $this->_data['name']?></p>
<p>There where exceptions logged on <strong><?php echo $this->_data['url']?></strong></p>
<p>Please review and take corrective measures.</p>

<?php if(is_array($this->_data['summary_data'])): ?>
	<h3>Table Summary of Exceptions </h3>
	<table class="error_email">
	<tr>
		<th>Instance</th>
		<th>Code</th>
		<th>Occurrence</th>
	</tr>
	<?php foreach($this->_data['summary_data'] as $data): ?>
	<tr>
		<td>><?php echo $data['instance_name']; ?> </td>
		<td>><?php echo $data['code']; ?> </td>
		<td>><?php echo $data['result']; ?> </td>
	</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

<?php if(is_array($this->_data['short_list'])): ?>
	<h3>Table Summary of Exceptions Short List</h3>
	<table class="error_email">
		<tr>
			<th>Id</th>
			<th>Code</th>
			<th>Message</th>
			<th>Logged</th>
		</tr>
		<?php foreach($this->_data['short_list'] as $list): ?>
			<tr>
				<td>><?php echo $data['instance_name']; ?> </td>
				<td>><?php echo $data['code']; ?> </td>
				<td>><?php echo $data['message']; ?> </td>
				<td>><?php echo $data['logged_at']->format('Y-m-d'); ?> </td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>

<p>Have a great day further!</p>