<?php  /* @var $this Mouf\Integration\HybridAuth\Controllers\HybridAuthInstallController */ ?>
<h1>Setting up HybridAuth</h1>

<form action="generate" method="post" class="form-horizontal">
	<input type="hidden" id="selfedit" name="selfedit"
		value="<?php echo plainstring_to_htmlprotected($this->selfedit) ?>" />

	<div class="control-group">
		<div class="controls">
			<label class="checkbox"> <input type="checkbox" name="facebook" /> Configure Facebook Connect</label>
		</div>
	</div>

	<div id="facebook_group">
		<div class="control-group">
			<label class="control-label">Facebook id:</label>
			<div class="controls">
				<input type="text" name="facebook_id"
					value="<?php //echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
				<span class="help-block">The facebook ID of your application. <a target="_blank" href="https://developers.facebook.com/apps">Don't have a Facebook app yet?</a>
				</span>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Facebook secret:</label>
			<div class="controls">
				<input type="text" name="facebook_secret"
					value="<?php //echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
				<span class="help-block">The facebook secret of your application.
				</span>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Scope (optional):</label>
			<div class="controls">
				<input type="text" name="facebook_scope"
					value="<?php //echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
				<span class="help-block">The scope requested by your application, separated by commas.
				For instance: "email, user_about_me, user_birthday, user_hometown"
				</span>
			</div>
		</div>	
	</div>
	
	
	
	
	<div class="control-group">
		<div class="controls">
			<label class="checkbox"> <input type="checkbox" name="twitter" /> Configure Twitter provider</label>
		</div>
	</div>

	<div id="twitter_group">
		<div class="control-group">
			<label class="control-label">Twitter Key:</label>
			<div class="controls">
				<input type="text" name="twitter_key"
					value="<?php //echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
				<span class="help-block">The Twitter key of your application. <a target="_blank" href="https://dev.twitter.com/apps">Don't have a Twitter app yet?</a>
				</span>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Twitter secret:</label>
			<div class="controls">
				<input type="text" name="twitter_secret"
					value="<?php //echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
				<span class="help-block">The Twitter secret of your application.
				</span>
			</div>
		</div>
		
	</div>
	
	
	<div class="control-group">
		<div class="controls">
			<label class="checkbox"> <input type="checkbox" name="google" /> Configure Google provider</label>
		</div>
	</div>

	<div id="google_group">
		<div class="control-group">
			<label class="control-label">Google ID:</label>
			<div class="controls">
				<input type="text" name="google_id"
					value="<?php //echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
				<span class="help-block">The Google ID of your application. <a target="_blank" href="https://code.google.com/apis/console/">Don't have a Google app yet?</a>
				</span>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Google secret:</label>
			<div class="controls">
				<input type="text" name="google_secret"
					value="<?php //echo plainstring_to_htmlprotected($this->sourceDirectory) ?>"></input>
				<span class="help-block">The Google secret of your application.
				</span>
			</div>
		</div>
		
	</div>
	
	<div class="control-group">
		<div class="controls">
			<button name="action" value="generate" type="submit"
				class="btn btn-danger">Install HybridAuth</button>
		</div>
	</div>
</form>

<script type="text/javascript">
function updateFacebook() {
	if ($("input[name=facebook]").prop('checked')) {
		$("#facebook_group").show();
	} else {
		$("#facebook_group").hide();
	}
}
$("input[name=facebook]").change(updateFacebook);
updateFacebook();

function updateGoogle() {
	if ($("input[name=google]").prop('checked')) {
		$("#google_group").show();
	} else {
		$("#google_group").hide();
	}
}
$("input[name=google]").change(updateGoogle);
updateGoogle();


function updateTwitter() {
	if ($("input[name=twitter]").prop('checked')) {
		$("#twitter_group").show();
	} else {
		$("#twitter_group").hide();
	}
}
$("input[name=twitter]").change(updateTwitter);
updateTwitter();

</script>