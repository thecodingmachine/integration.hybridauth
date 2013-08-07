<?php 
<?php /* @var $this Mouf\Integration\HybridAuth\Controllers\HybridAuthInstallController */ ?>
<h1>Setting up HybridAuth</h1>

<form action="generate" method="post" class="form-horizontal">
<input type="hidden" id="selfedit" name="selfedit" value="<?php echo plainstring_to_htmlprotected($this->selfedit) ?>" />

<div class="control-group">
	<label class="control-label">Source directory:</label>
	<div class="controls">
		<input type="text" name="sourcedirectory" value="<?php echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
		<span class="help-block">This is the directory containing your source code (it should be configured in your the "autoload" section of your <em>composer.json</em> file.</span>
	</div>
</div>

<div class="control-group">
	<div class="controls">
		<button name="action" value="generate" type="submit" class="btn btn-danger">Install HybridAuth</button>
	</div>
</div>
</form>