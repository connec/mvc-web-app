<?php use MVCWebComponents\Debug ?>
<div class="debug-table watched-table">
	<?php if(empty($watched)): ?>
		<p>No variables are watched.</p>
	<?php else: ?>
		<table>
			<tr class="header">
				<th>Key</th>
				<th>Value</th>
				<th>Bookmarks</th>
			</tr>
			<?php foreach($watched as $key => $details): ?>
				<tr>
					<td><?php echo $key ?></td>
					<td><?php echo Debug::var_dump($details['ref'], true, false) ?></td>
					<td class="bookmarks">
						<?php foreach($details['bookmarks'] as $bookmark => $value): ?>
							<span class="bookmark"><?php echo $bookmark ?></span> - 
							<?php echo Debug::var_dump($details['bookmarks'][$bookmark]) ?>
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>