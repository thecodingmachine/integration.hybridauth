<?php
namespace Mouf\Integration\HybridAuth\Controllers;

use Mouf\MoufManager;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;

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
		if (!$this->moufManager->instanceExists("dbConnection")) {
			$this->displayErrorMsg("The TDBM install process assumes your database connection instance is already created, and that the name of this instance is 'dbConnection'. Could not find the 'dbConnection' instance.");
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
			$twitter = "", $twitter_key = "", $twitter_secret = "",
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
			if (!isset($constants['FACEBOOK_ID'])) {
				$configManager->registerConstant('FACEBOOK_ID', 'string', '', 'The Facebook ID used by Facebook API. You can create an app here: https://developers.facebook.com/apps');
			}
			if (!isset($constants['FACEBOOK_SECRET'])) {
				$configManager->registerConstant('FACEBOOK_SECRET', 'string', '', 'The Facebook secret used by Facebook API. You can create an app here: https://developers.facebook.com/apps');
			}
			if (!isset($constants['FACEBOOK_SCOPE'])) {
				$configManager->registerConstant('FACEBOOK_SCOPE', 'string', '', 'The Facebook scope requested by the application.
	For instance: "email, user_about_me, user_birthday, user_hometown"');
			}

			$configPhpConstants['FACEBOOK_ID'] = $facebook_id;
			$configPhpConstants['FACEBOOK_SECRET'] = $facebook_secret;
			$configPhpConstants['FACEBOOK_SCOPE'] = $facebook_scope;
				
			
			$googleProvider = InstallUtils::getOrCreateInstance('facebookProvider', 'Mouf\\Integration\\HybridAuth\\GenericProvider', $moufManager);
			
			if (!$facebookProvider->getConstructorArgumentProperty('setProviderName')->isValueSet()) {
				$facebookProvider->getSetterProperty('setProviderName')->setValue('Facebook');
			}
			if (!$facebookProvider->getConstructorArgumentProperty('setEnabled')->isValueSet()) {
				$facebookProvider->getSetterProperty('setEnabled')->setValue(true);
			}
			if (!$facebookProvider->getConstructorArgumentProperty('setId')->isValueSet()) {
				$facebookProvider->getSetterProperty('setId')->setValue('FACEBOOK_ID');
				$facebookProvider->getSetterProperty('setId')->setOrigin("config");
			}
			if (!$facebookProvider->getConstructorArgumentProperty('setSecret')->isValueSet()) {
				$facebookProvider->getSetterProperty('setSecret')->setValue('FACEBOOK_SECRET');
				$facebookProvider->getSetterProperty('setSecret')->setOrigin("config");
			}
			if (!$facebookProvider->getConstructorArgumentProperty('setScope')->isValueSet()) {
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
			
			if (!$googleProvider->getConstructorArgumentProperty('setProviderName')->isValueSet()) {
				$googleProvider->getSetterProperty('setProviderName')->setValue('Google');
			}
			if (!$googleProvider->getConstructorArgumentProperty('setEnabled')->isValueSet()) {
				$googleProvider->getSetterProperty('setEnabled')->setValue(true);
			}
			if (!$googleProvider->getConstructorArgumentProperty('setId')->isValueSet()) {
				$googleProvider->getSetterProperty('setId')->setValue('GOOGLE_ID');
				$googleProvider->getSetterProperty('setId')->setOrigin("config");
			}
			if (!$googleProvider->getConstructorArgumentProperty('setSecret')->isValueSet()) {
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
			
			if (!$twitterProvider->getConstructorArgumentProperty('setProviderName')->isValueSet()) {
				$twitterProvider->getSetterProperty('setProviderName')->setValue('Twitter');
			}
			if (!$twitterProvider->getConstructorArgumentProperty('setEnabled')->isValueSet()) {
				$twitterProvider->getSetterProperty('setEnabled')->setValue(true);
			}
			if (!$twitterProvider->getConstructorArgumentProperty('setKey')->isValueSet()) {
				$twitterProvider->getSetterProperty('setKey')->setValue('TWITTER_KEY');
				$twitterProvider->getSetterProperty('setKey')->setOrigin("config");
			}
			if (!$twitterProvider->getConstructorArgumentProperty('setSecret')->isValueSet()) {
				$twitterProvider->getSetterProperty('setSecret')->setValue('TWITTER_SECRET');
				$twitterProvider->getSetterProperty('setSecret')->setOrigin("config");
			}
			
			$providers[] = $twitterProvider;
		}
		
		$configManager->setDefinedConstants($configPhpConstants);
		
		
		// These instances are expected to exist when the installer is run.
		$dbConnection = $moufManager->getInstance('dbConnection');
		$userService = $moufManager->getInstance('userService');
		$userMessageService = $moufManager->getInstance('userMessageService');
		
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
		$anonymousDisplayMessageAction = $moufManager->createInstance('Mouf\\Html\\Widgets\\MessageService\\Service\\Actions\\DisplayMessageAction');
		$anonymousUserMessage = $moufManager->createInstance('Mouf\\Html\\Widgets\\MessageService\\Service\\UserMessage');
		
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
		if (!$hybridAuthFactory->getConstructorArgumentProperty('setDebugMode')->isValueSet()) {
			$hybridAuthFactory->getSetterProperty('setDebugMode')->setValue(true);
		}
		if (!$hybridAuthFactory->getConstructorArgumentProperty('setDebugFile')->isValueSet()) {
			$hybridAuthFactory->getSetterProperty('setDebugFile')->setValue('hybridAuth.log');
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
		$anonymousSocialAuthenticateAction->getConstructorArgumentProperty('onFailure')->setValue(array(0 => $anonymousDisplayMessageAction, ));
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('socialProviderName')->setValue($socialProviderName);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('socialProfile')->setValue($socialProfile);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('findSocialUser')->setValue($anonymousSelect);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('dbConnection')->setValue($dbConnection);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('userService')->setValue($userService);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('bindOnEmail')->setValue(true);
		$anonymousPerformSocialLoginAction->getConstructorArgumentProperty('findUserIdFromMail')->setValue($anonymousSelect2);
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
