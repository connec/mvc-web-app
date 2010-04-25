<div class="debug-table query-table">
	<?php if(empty($queries)): ?>
		<p>No queries.</p>
	<?php else: ?>
		<table>
			<tr class="header">
				<th>#</th>
				<th>SQL</th>
				<th>Time</th>
				<th>Count</th>
				<th>Error</th>
			</tr>
			<?php foreach($queries as $i => $query): ?>
				<tr>
					<td><?php echo $i ?></td>
					<td><?php echo $query['sql'] ?></td>
					<td><?php echo number_format($query['time'], 4) ?></td>
					<td><?php echo $query['num_result_rows'] ?: ($query['num_affected_rows'] ?: 0) ?></td>
					<td><?php echo $query['error'] ?: 'none' ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>