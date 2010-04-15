<?php $delegate('page_heading', 'MVC Web App') ?>
<p>
	Overwrite this page by creating your own home page in 
	<span class="green">
		<?php echo $h('url')->native('[root]/app/views/pages/home.tpl') ?>
	</span>
</p>
<p>
	You can overwrite the layout by creating your own in
	<span class="green">
		<?php echo $h('url')->native('[root]/app/views/layouts/default.tpl') ?>
	</span>
</p>
<p>
	Database status: 
	<?php if($h('register')->check('database')): ?>
		<span class="green">OK</span>
	<?php else: ?>
		<span class="red">
			not configured!  Create 
			<span class="green">
				<?php echo $h('url')->native('[root]/app/configs/database.php') ?>
			</span> 
			with your database configuration in $database.
		</span>
	<?php endif; ?>
</p>