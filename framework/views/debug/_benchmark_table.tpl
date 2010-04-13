<div class="debug-table benchmark-table">
	<?php if(empty($benchmarks)): ?>
		<p>No benchmarks.</p>
	<?php else: ?>
		<table>
			<tr class="header">
				<th>Key</th>
				<th>Time</th>
			</tr>
			<?php foreach($benchmarks as $key => $time): ?>
				<tr>
					<td><?php echo $key ?></td>
					<td><?php echo number_format($time, 3) ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>