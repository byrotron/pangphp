<style type="text/css">
	.error_email {
		margin-top: 15px;
	}
	table {
		border: solid 1px grey
	}
	table td {
		border: solid 1px grey;
		padding: 3px
	}
</style>

<p>Hi, <?php echo $this->_data['name']?></p>
<p>There where exceptions logged on <strong><?php echo $this->_data['url'] ?></strong></p>
<p>Please review and take corrective measures.</p>

<?php if(is_array($this->_data['errors'])): ?>
	<h3>Table Summary of Exceptions </h3>
	<table rules="all" class="error_email">
	<tr>
		<th>Instance</th>
		<th>Code</th>
		<th>Occurrence</th>
	</tr>
	<?php foreach($this->_data['errors'] as $error): ?>
	<tr>
		<td><?php echo $error->getMessage(); ?></td>
		<td><?php echo $error->getFile(); ?></td>
		<td><?php echo $error->getLine(); ?></td>
		<td><?php echo $error->getLoggedAt()->format("Y-m-d h:i"); ?></td>
	</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>