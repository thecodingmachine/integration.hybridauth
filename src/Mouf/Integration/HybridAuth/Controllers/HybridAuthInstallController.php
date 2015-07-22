<?php
namespace Mouf\Integration\HybridAuth\Controllers;

use Mouf\MoufManager;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Actions\InstallUtils;
use Mouf\Html\Renderer\RendererUtils;
use Mouf\Database\Patcher\DatabasePatchInstaller;

/**
 * This class is displaying the HybridAuth install controller. 
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class HybridAuthInstallController extends Controller {
	
	/**
	 *
	 * @var HtmlBlock
	 */
	public $content;
	
	public $selfedit;
	
	/**
	 * The active MoufManager to be edited/viewed
	 *
	 * @var MoufManager
	 */
	public $moufManager;
	
	/**
	 * The template used by the main page for mouf.
	 *
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 * Displays the first install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function defaultAction($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
				
		$this->content->addFile(dirname(__FILE__)."/../../../../views/installStep1.php", $this);
		$this->template->toHtml();
	}

	/**
	 * Skips the install process.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only)
	 */
	public function skip($selfedit = "false") {
		InstallUtils::continueInstall($selfedit == "true");
	}

	
	/**
	 * Displays the second install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function configure($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		// Let's start by performing basic checks about the instances we assume to exist.
		if (!$this->moufManager->instanceExists("dbalConnection")) {
			$this->displayErrorMsg("The TDBM install process assumes your database connection instance is already created, and that the name of this instance is 'dbalConnection'. Could not find the 'dbalConnection' instance.");
			return;
		}
								
		$this->content->addFile(dirname(__FILE__)."/../../../../views/installStep2.php", $this);
		$this->template->toHtml();
	}
	
	/**
	 * This action generates the TDBM instance, then the DAOs and Beans. 
	 * 
	 * @Action
	 * @param string $name
	 * @param bool $selfedit
	 */
	public function generate($facebook = "", $facebook_id = "", $facebook_secret = "", $facebook_scope = "", 
			$google = "", $google_id = "", $google_secret = "",
			$twitter = "", $twitter_id = "", $twitter_secret = "",
			$redirect_login = "", $redirect_create = "", $redirect_failure = "",
			$selfedit="false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		
		$moufManager = $this->moufManager;
		
		$configManager = $moufManager->getConfigManager();
		$constants = $configManager->getMergedConstants();
		
		$configPhpConstants = $configManager->getDefinedConstants();
		
		$providers = array();
		
		if ($facebook) {
			if ($configManager->getConstantDefinition('FACEBOOK_ID') === null) {
				$configManager->registerConstant('FACEBOOK_ID', 'string', '', 'The Facebook ID used by Facebook API. You can create an app here: https://developers.facebook.com/apps');
			}
			if ($configManager->getConstantDefinition('FACEBOOK_SECRET') === null) {
				$configManager->registerConstant('FACEBOOK_SECRET', 'string', '', 'The Facebook secret used by Facebook API. You can create an app here: https://developers.facebook.com/apps');
			}
			if ($configManager->getConstantDefinition('FACEBOOK_SCOPE') === null) {
				$configManager->registerConstant('FACEBOOK_SCOPE', 'string', '', 'The Facebook scope requested by the application.
	For instance: "email, user_about_me, user_birthday, user_hometown"');
			}

			$configPhpConstants['FACEBOOK_ID'] = $facebook_id;
			$configPhpConstants['FACEBOOK_SECRET'] = $facebook_secret;
			$configPhpConstants['FACEBOOK_SCOPE'] = $facebook_scope;
				
			
			$facebookProvider = InstallUtils::getOrCreateInstance('facebookProvider', 'Mouf\\Integration\\HybridAuth\\GenericProvider', $moufManager);
			
			if (!$facebookProvider->getSetterProperty('setProviderName')->isValueSet()) {
				$facebookProvider->getSetterProperty('setProviderName')->setValue('Facebook');
			}
			if (!$facebookProvider->getSetterProperty('setEnabled')->isValueSet()) {
				$facebookProvider->getSetterProperty('setEnabled')->setValue(true);
			}
			if (!$facebookProvider->getSetterProperty('setId')->isValueSet()) {
				$facebookProvider->getSetterProperty('setId')->setValue('FACEBOOK_ID');
				$facebookProvider->getSetterProperty('setId')->setOrigin("config");
			}
			if (!$facebookProvider->getSetterProperty('setSecret')->isValueSet()) {
				$facebookProvider->getSetterProperty('setSecret')->setValue('FACEBOOK_SECRET');
				$facebookProvider->getSetterProperty('setSecret')->setOrigin("config");
			}
			if (!$facebookProvider->getSetterProperty('setScope')->isValueSet()) {
				$facebookProvider->getSetterProperty('setScope')->setValue('FACEBOOK_SCOPE');
				$facebookProvider->getSetterProperty('setScope')->setOrigin("config");
			}
			$providers[] = $facebookProvider;
		}
		
		if ($google) {
			if (!isset($constants['GOOGLE_ID'])) {
				$configManager->registerConstant('GOOGLE_ID', 'string', '', 'The Google ID used to by Google API for connection. You can create an app here: https://code.google.com/apis/console/');
			}
			if (!isset($constants['GOOGLE_SECRET'])) {
				$configManager->registerConstant('GOOGLE_SECRET', 'string', '', 'The Google secret used by Google API.');
			}
			

			$configPhpConstants['GOOGLE_ID'] = $google_id;
			$configPhpConstants['GOOGLE_SECRET'] = $google_secret;
			
			$googleProvider = InstallUtils::getOrCreateInstance('googleProvider', 'Mouf\\Integration\\HybridAuth\\GenericProvider', $moufManager);
			
			if (!$googleProvider->getSetterProperty('setProviderName')->isValueSet()) {
				$googleProvider->getSetterProperty('setProviderName')->setValue('Google');
			}
			if (!$googleProvider->getSetterProperty('setEnabled')->isValueSet()) {
				$googleProvider->getSetterProperty('setEnabled')->setValue(true);
			}
			if (!$googleProvider->getSetterProperty('setId')->isValueSet()) {
				$googleProvider->getSetterProperty('setId')->setValue('GOOGLE_ID');
				$googleProvider->getSetterProperty('setId')->setOrigin("config");
			}
			if (!$googleProvider->getSetterProperty('setSecret')->isValueSet()) {
				$googleProvider->getSetterProperty('setSecret')->setValue('GOOGLE_SECRET');
				$googleProvider->getSetterProperty('setSecret')->setOrigin("config");
			}
			
			$providers[] = $googleProvider;
		}
		
		if ($twitter) {
			if (!isset($constants['TWITTER_KEY'])) {
				$configManager->registerConstant('TWITTER_KEY', 'string', '', 'The Twitter key used by Twitter API. You can create an app here: https://dev.twitter.com/apps');
			}
			if (!isset($constants['TWITTER_SECRET'])) {
				$configManager->registerConstant('TWITTER_SECRET', 'string', '', 'The Twitter secret used to by Twitter API');
			}
			
			$configPhpConstants['TWITTER_ID'] = $twitter_id;
			$configPhpConstants['TWITTER_SECRET'] = $twitter_secret;
			
			$twitterProvider = InstallUtils::getOrCreateInstance('twitterProvider', 'Mouf\\Integration\\HybridAuth\\GenericProvider', $moufManager);
			
			if (!$twitterProvider->getSetterProperty('setProviderName')->isValueSet()) {
				$twitterProvider->getSetterProperty('setProviderName')->setValue('Twitter');
			}
			if (!$twitterProvider->getSetterProperty('setEnabled')->isValueSet()) {
				$twitterProvider->getSetterProperty('setEnabled')->setValue(true);
			}
			if (!$twitterProvider->getSetterProperty('setKey')->isValueSet()) {
				$twitterProvider->getSetterProperty('setKey')->setValue('TWITTER_KEY');
				$twitterProvider->getSetterProperty('setKey')->setOrigin("config");
			}
			if (!$twitterProvider->getSetterProperty('setSecret')->isValueSet()) {
				$twitterProvider->getSetterProperty('setSecret')->setValue('TWITTER_SECRET');
				$twitterProvider->getSetterProperty('setSecret')->setOrigin("config");
			}
			
			$providers[] = $twitterProvider;
		}
		
		$configManager->setDefinedConstants($configPhpConstants);
		
		
		// These instances are expected to exist when the installer is run.
		$dbalConnection = $moufManager->getInstanceDescriptor('dbalConnection');
		$userService = $moufManager->getInstanceDescriptor('userService');
		$userMessageService = $moufManager->getInstanceDescriptor('userMessageService');
		
		// Let's create the instances.
		$socialAuthenticateUrl = InstallUtils::getOrCreateInstance('socialAuthenticateUrl', 'Mouf\\Mvc\\Splash\\UrlEntryPoint', $moufManager);
		$socialProviderName = InstallUtils::getOrCreateInstance('socialProviderName', 'Mouf\\Utils\\Value\\Variable', $moufManager);
		$socialProfile = InstallUtils::getOrCreateInstance('socialProfile', 'Mouf\\Utils\\Value\\Variable', $moufManager);
		$hybridAuthFactory = InstallUtils::getOrCreateInstance('hybridAuthFactory', 'Mouf\\Integration\\HybridAuth\\HybridAuthFactory', $moufManager);
		$socialLoginErrorMessage = InstallUtils::getOrCreateInstance('socialLoginErrorMessage', 'Mouf\\Utils\\Value\\Variable', $moufManager);
		$socialLoginException = InstallUtils::getOrCreateInstance('socialLoginException', 'Mouf\\Utils\\Value\\Variable', $moufManager);
		$anonymousAssign = $moufManager->createInstance('Mouf\\Utils\\Action\\Assign');
		$anonymousRequestParam = $moufManager->createInstance('Mouf\\Utils\\Value\\RequestParam');
		$anonymousSocialAuthenticateAction = $moufManager->createInstance('Mouf\\Integration\\HybridAuth\\Actions\\SocialAuthenticateAction');
		$anonymousPerformSocialLoginAction = $moufManager->createInstance('Mouf\\Integration\\HybridAuth\\Actions\\PerformSocialLoginAction');
		$anonymousSelect = $moufManager->createInstance('SQLParser\\Query\\Select');
		$anonymousColRef = $moufManager->createInstance('SQLParser\\Node\\ColRef');
		$anonymousTable = $moufManager->createInstance('SQLParser\\Node\\Table');
		$anonymousAndOp = $moufManager->createInstance('SQLParser\\Node\\AndOp');
		$anonymousEqual = $moufManager->createInstance('SQLParser\\Node\\Equal');
		$anonymousColRef2 = $moufManager->createInstance('SQLParser\\Node\\ColRef');
		$anonymousParameter = $moufManager->createInstance('SQLParser\\Node\\Parameter');
		$anonymousParamAvailableCondition = $moufManager->createInstance('Mouf\\Database\\QueryWriter\\Condition\\ParamAvailableCondition');
		$anonymousEqual2 = $moufManager->createInstance('SQLParser\\Node\\Equal');
		$anonymousColRef3 = $moufManager->createInstance('SQLParser\\Node\\ColRef');
		$anonymousParameter2 = $moufManager->createInstance('SQLParser\\Node\\Parameter');
		$anonymousParamAvailableCondition2 = $moufManager->createInstance('Mouf\\Database\\QueryWriter\\Condition\\ParamAvailableCondition');
		$anonymousSelect2 = $moufManager->createInstance('SQLParser\\Query\\Select');
		$anonymousColRef4 = $moufManager->createInstance('SQLParser\\Node\\ColRef');
		$anonymousTable2 = $moufManager->createInstance('SQLParser\\Node\\Table');
		$anonymousEqual3 = $moufManager->createInstance('SQLParser\\Node\\Equal');
		$anonymousColRef5 = $moufManager->createInstance('SQLParser\\Node\\ColRef');
		$anonymousParameter3 = $moufManager->createInstance('SQLParser\\Node\\Parameter');
		$anonymousParamAvailableCondition3 = $moufManager->createInstance('Mouf\\Database\\QueryWriter\\Condition\\ParamAvailableCondition');
		$anonymousRedirect = $moufManager->createInstance('Mouf\\Utils\\Action\\Redirect');
		$anonymousRedirect2 = $moufManager->createInstance('Mouf\\Utils\\Action\\Redirect');
		$anonymousDisplayMessageAction = $moufManager->createInstance('Mouf\\Html\\Widgets\\MessageService\\Service\\Actions\\DisplayMessageAction');
		$anonymousUserMessage = $moufManager->createInstance('Mouf\\Html\\Widgets\\MessageService\\Service\\UserMessage');
		$anonymousRedirect3 = $moufManager->createInstance('Mouf\\Utils\\Action\\Redirect');

		// Let's bind instances together.
		if (!$socialAuthenticateUrl->getConstructorArgumentProperty('url')->isValueSet()) {
			$socialAuthenticateUrl->getConstructorArgumentProperty('url')->setValue('authenticate');
		}
		if (!$socialAuthenticateUrl->getConstructorArgumentProperty('actions')->isValueSet()) {
			$socialAuthenticateUrl->getConstructorArgumentProperty('actions')->setValue(array(0 => $anonymousAssign, 1 => $anonymousSocialAuthenticateAction, ));
		}
		if (!$hybridAuthFactory->getConstructorArgumentProperty('providers')->isValueSet()) {
			$hybridAuthFactory->getConstructorArgumentProperty('providers')->setValue($providers);
		}
		if (!$hybridAuthFactory->getSetterProperty('setDebugMode')->isValueSet()) {
			$hybridAuthFactory->getSetterProperty('setDebugMode')->setValue(false);
		}
		if (!$hybridAuthFactory->getSetterProperty('setDebugFile')->isValueSet()) {
			$hybridAuthFactory->getSetterProperty('setDebugFile')->setValue('');
		}
		
		
		$anonymousAssign->getConstructorArgumentProperty('variable')->setValue($socialProviderName);
		$anonymousAssign->getConstructorArgumentProperty('value')->setValue($anonymousRequestParam);
		$anonymousRequestParam->getConstructorArgumentProperty('paramName')->setValue('provider');
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('socialProviderName')->setValue($socialProviderName);
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('socialProfile')->setValue($socialProfile);
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('hybridAuthFactory')->setValue($hybridAuthFactory);
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('errorMessage')->setValue($socialLoginErrorMessage);
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('errorException')->setValue($socialLoginException);
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('onSuccess')->setValue(array(0 => $anonymousPerformSocialLoginAction, ));
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('onFailure')->setValue(array(0 => $anonymousDisplayMessageAction, 1 => $anonymousRedirect3, ));
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('socialProviderName')->setValue($socialProviderName);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('socialProfile')->setValue($socialProfile);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('findSocialUser')->setValue($anonymousSelect);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('dbalConnection')->setValue($dbalConnection);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('userService')->setValue($userService);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('bindOnEmail')->setValue(true);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('findUserIdFromMail')->setValue($anonymousSelect2);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('onUserLogged')->setValue(array(0 => $anonymousRedirect, ));
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('onUserCreated')->setValue(array(0 => $anonymousRedirect2, ));
		$anonymousSelect->getSetterProperty('setColumns')->setValue(array(0 => $anonymousColRef, ));
		$anonymousSelect->getSetterProperty('setFrom')->setValue(array(0 => $anonymousTable, ));
		$anonymousSelect->getSetterProperty('setWhere')->setValue($anonymousAndOp);
		$anonymousColRef->getSetterProperty('setColumn')->setValue('user_id');
		$anonymousTable->getSetterProperty('setTable')->setValue('authentications');
		$anonymousTable->getSetterProperty('setJoinType')->setValue('JOIN');
		$anonymousAndOp->getSetterProperty('setOperands')->setValue(array(0 => $anonymousEqual, 1 => $anonymousEqual2, ));
		$anonymousEqual->getSetterProperty('setLeftOperand')->setValue($anonymousColRef2);
		$anonymousEqual->getSetterProperty('setRightOperand')->setValue($anonymousParameter);
		$anonymousEqual->getSetterProperty('setCondition')->setValue($anonymousParamAvailableCondition);
		$anonymousColRef2->getSetterProperty('setColumn')->setValue('provider');
		$anonymousParameter->getSetterProperty('setName')->setValue('provider');
		$anonymousParamAvailableCondition->getConstructorArgumentProperty('parameterName')->setValue('provider');
		$anonymousEqual2->getSetterProperty('setLeftOperand')->setValue($anonymousColRef3);
		$anonymousEqual2->getSetterProperty('setRightOperand')->setValue($anonymousParameter2);
		$anonymousEqual2->getSetterProperty('setCondition')->setValue($anonymousParamAvailableCondition2);
		$anonymousColRef3->getSetterProperty('setColumn')->setValue('provider_uid');
		$anonymousParameter2->getSetterProperty('setName')->setValue('provider_uid');
		$anonymousParamAvailableCondition2->getConstructorArgumentProperty('parameterName')->setValue('provider_uid');
		$anonymousSelect2->getSetterProperty('setColumns')->setValue(array(0 => $anonymousColRef4, ));
		$anonymousSelect2->getSetterProperty('setFrom')->setValue(array(0 => $anonymousTable2, ));
		$anonymousSelect2->getSetterProperty('setWhere')->setValue($anonymousEqual3);
		$anonymousColRef4->getSetterProperty('setColumn')->setValue('id');
		$anonymousTable2->getSetterProperty('setTable')->setValue('users');
		$anonymousTable2->getSetterProperty('setJoinType')->setValue('JOIN');
		$anonymousEqual3->getSetterProperty('setLeftOperand')->setValue($anonymousColRef5);
		$anonymousEqual3->getSetterProperty('setRightOperand')->setValue($anonymousParameter3);
		$anonymousEqual3->getSetterProperty('setCondition')->setValue($anonymousParamAvailableCondition3);
		$anonymousColRef5->getSetterProperty('setColumn')->setValue('email');
		$anonymousParameter3->getSetterProperty('setName')->setValue('email');
		$anonymousParamAvailableCondition3->getConstructorArgumentProperty('parameterName')->setValue('email');
		$anonymousDisplayMessageAction->getConstructorArgumentProperty('message')->setValue($anonymousUserMessage);
		$anonymousDisplayMessageAction->getConstructorArgumentProperty('messageService')->setValue($userMessageService);
		$anonymousUserMessage->getConstructorArgumentProperty('message')->setValue($socialLoginErrorMessage);
		$anonymousUserMessage->getConstructorArgumentProperty('type')->setValue('error');
		
		$anonymousRedirect->getSetterProperty('setUrl')->setValue($redirect_login);
		$anonymousRedirect2->getSetterProperty('setUrl')->setValue($redirect_create);
		$anonymousRedirect3->getSetterProperty('setUrl')->setValue($redirect_failure);
		
		$socialProfilePicture = InstallUtils::getOrCreateInstance('socialProfilePicture', 'Mouf\\Integration\\HybridAuth\\Html\\SocialProfilePicture', $moufManager);
		if (!$socialProfilePicture->getConstructorArgumentProperty('hybridAuth')->isValueSet()) {
			$socialProfilePicture->getConstructorArgumentProperty('hybridAuth')->setValue($hybridAuthFactory);
		}
		
		if ($facebook) {
			// Let's create the instance.
			$facebookConnectButton = InstallUtils::getOrCreateInstance('facebookConnectButton', 'Mouf\\Integration\\HybridAuth\\Html\\SocialConnectButton', $moufManager);
			
			// Let's bind instances together.
			if (!$facebookConnectButton->getConstructorArgumentProperty('hybridAuth')->isValueSet()) {
				$facebookConnectButton->getConstructorArgumentProperty('hybridAuth')->setValue($hybridAuthFactory);
			}
			if (!$facebookConnectButton->getConstructorArgumentProperty('provider')->isValueSet()) {
				$facebookConnectButton->getConstructorArgumentProperty('provider')->setValue($facebookProvider);
			}
			if (!$facebookConnectButton->getConstructorArgumentProperty('url')->isValueSet()) {
				$facebookConnectButton->getConstructorArgumentProperty('url')->setValue('authenticate?provider=Facebook');
			}
			if (!$facebookConnectButton->getConstructorArgumentProperty('imageUrl')->isValueSet()) {
				$facebookConnectButton->getConstructorArgumentProperty('imageUrl')->setValue('vendor/mouf/integration.hybridauth/images/Facebook.png');
			}
		}
		
		if ($google) {
			// Let's create the instance.
			$googleConnectButton = InstallUtils::getOrCreateInstance('googleConnectButton', 'Mouf\\Integration\\HybridAuth\\Html\\SocialConnectButton', $moufManager);
				
			// Let's bind instances together.
			if (!$googleConnectButton->getConstructorArgumentProperty('hybridAuth')->isValueSet()) {
				$googleConnectButton->getConstructorArgumentProperty('hybridAuth')->setValue($hybridAuthFactory);
			}
			if (!$googleConnectButton->getConstructorArgumentProperty('provider')->isValueSet()) {
				$googleConnectButton->getConstructorArgumentProperty('provider')->setValue($googleProvider);
			}
			if (!$googleConnectButton->getConstructorArgumentProperty('url')->isValueSet()) {
				$googleConnectButton->getConstructorArgumentProperty('url')->setValue('authenticate?provider=Google');
			}
			if (!$googleConnectButton->getConstructorArgumentProperty('imageUrl')->isValueSet()) {
				$googleConnectButton->getConstructorArgumentProperty('imageUrl')->setValue('vendor/mouf/integration.hybridauth/images/Google.png');
			}
		}
		
		if ($twitter) {
			// Let's create the instance.
			$twitterConnectButton = InstallUtils::getOrCreateInstance('twitterConnectButton', 'Mouf\\Integration\\HybridAuth\\Html\\SocialConnectButton', $moufManager);
		
			// Let's bind instances together.
			if (!$twitterConnectButton->getConstructorArgumentProperty('hybridAuth')->isValueSet()) {
				$twitterConnectButton->getConstructorArgumentProperty('hybridAuth')->setValue($hybridAuthFactory);
			}
			if (!$twitterConnectButton->getConstructorArgumentProperty('provider')->isValueSet()) {
				$twitterConnectButton->getConstructorArgumentProperty('provider')->setValue($twitterProvider);
			}
			if (!$twitterConnectButton->getConstructorArgumentProperty('url')->isValueSet()) {
				$twitterConnectButton->getConstructorArgumentProperty('url')->setValue('authenticate?provider=Twitter');
			}
			if (!$twitterConnectButton->getConstructorArgumentProperty('imageUrl')->isValueSet()) {
				$twitterConnectButton->getConstructorArgumentProperty('imageUrl')->setValue('vendor/mouf/integration.hybridauth/images/Twitter.png');
			}
		}
		
		// In case there is a userDao, let's bind it.
		if ($moufManager->has('userDao')) {
			$userDao = $moufManager->getInstanceDescriptor('userDao');
			$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('userDao')->setValue($userDao);
		}
		
		// Let's now declare the database patch that will create the "authorisations" table:
		DatabasePatchInstaller::registerPatch($moufManager,
			"create_authentications_table",
			"Creates the 'authentications' table that will store authentication related data from social networks.",
			"vendor/mouf/integration.hybridauth/database/up/create_authentications.sql"
			);
		
		
		// Finally, let's declare a renderer
		RendererUtils::createPackageRenderer($moufManager, "mouf/integration.hybridauth");
		
		$this->moufManager->rewriteMouf();
		
		InstallUtils::continueInstall($selfedit == "true");
	}
	

	protected $errorMsg;
	
	private function displayErrorMsg($msg) {
		$this->errorMsg = $msg;
		$this->content->addFile(dirname(__FILE__)."/../../../../views/installError.php", $this);
		$this->template->toHtml();
	}
		
}
?>
