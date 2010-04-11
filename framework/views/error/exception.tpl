<pre class="exception">
<h1>Unhandled Exception <i><?php echo $type ?></i></h1>
<p class="exception-message">
<?php echo $exception->getMessage() ?> - Code: <?php echo $exception->getCode()?> <br/>
<span class="exception-context">
Triggered on line <?php echo $exception->getLine() ?> in <?php echo $file ?>.
</span>
</p>
<?php echo $trace ?>
</pre>