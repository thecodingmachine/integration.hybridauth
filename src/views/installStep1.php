<?php /* @var $this Mouf\Integration\HybridAuth\Controllers\HybridAuthInstallController */ ?>
<h1>Setting up HybridAuth</h1>

<p>HybridAuth let you configure social connect on a number of social networks (Facebook, Google+, Twitter,
and many others...). The install process will let you configure a Facebook, Google+ or Twitter account,
but you can add many other accounts later.</p>
<p>HybridAuth will set up a number of instances. After setup, you will need to configure the 'userManagerService' 
property of the <strong>PerformSocialLoginAction</strong> class. This instance is used to create or update
users.</p>
<p>Then, you can use the /authenticate?provider=XXX URL to log into
your application.</p>

<form action="configure">
	<input type="hidden" name="selfedit" value="<?php echo $this->selfedit ?>" />
	<button class="btn btn-danger">Configure HybridAuth</button>
</form>
<form action="skip">
	<input type="hidden" name="selfedit" value="<?php echo $this->selfedit ?>" />
	<button class="btn">Skip</button>
</form>