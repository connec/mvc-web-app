<html>
	<head>
		<title><?php echo $page_title ?></title>
		<style type="text/css">
		
		body {
			margin-left: 100px;
			font-family: 'Lucida Sans Unicode', 'Lucida Sans', Arial, sans-serif;
		}
		
		.debug, .debug td, .debug th {
			font-size: 0.7em;
		}
		
		.red {
			color: #900;
		}
		
		.green {
			color: #090;
		}
		
		</style>
	</head>
	<body>
		<h1><?php echo $page_heading ?></h1>
		<?php if($h('session')->check('flash')): ?>
			<div class="flash<?php echo $h('session')->read('flash.type') ?: '' ?>">
				<?php echo $h('session')->read('flash.message') ?>
			</div>
		<?php endif; ?>
		<?php echo $action_output ?>
		<?php if(DEBUG): ?>
			:debug:
		<?php endif; ?>
	</body>
</html>