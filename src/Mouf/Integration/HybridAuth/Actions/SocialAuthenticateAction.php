<?php
namespace Mouf\Integration\HybridAuth\Actions;

use Mouf\Utils\Action\ActionInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\Variable;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Integration\HybridAuth\HybridAuthFactory;

/**
 * This action will trigger authentication on a social network.
 * If does actually call the social network login page (if not logged), and then call success or failure
 * actions depending on the result. This class does not log the user in, it just fills the "socialProfile"
 * variable with information related to the social network.
 * You can use the PerformSocialLoginAction class to log people in (if authentication is successful).
 * 
 * If the user is not logged, it can directly redirect the user on the social network and just
 * quit. When the authentication is done, the authentication URL will be called again.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class SocialAuthenticateAction implements ActionInterface {

	/**
	 * The name of the social provider.
	 * 
	 * @var ValueInterface|string
	 */
	private $socialProviderName;
	
	/**
	 * The social profile after authentication will be stored here (as a PHP object)
	 *
	 * @var Variable
	 */
	private $socialProfile;
	
	/**
	 * Error message (if any)
	 *
	 * @var Variable
	 */
	private $errorMessage;
	
	/**
	 * The triggered exception (if any)
	 *
	 * @var Variable
	 */
	private $errorException;
	
	/**
	 * The HybridAuth object used to perform the authentication
	 * 
	 * @var HybridAuthFactory
	 */
	private $hybridAuthFactory;
	
	/**
	 * List of actions to be performed if the authentication is successful.
	 * Note: these actions can access the profile in the $socialProfile variable
	 * @var ActionInterface[]
	 */
	private $onSuccess;

	/**
	 * List of actions to be performed if the authentication fails
	 * Note: these actions can access the error message in the $errorMessage variable
	 * @var ActionInterface[]
	 */
	private $onFailure;
	
	/**
	 * 
	 * @param ValueInterface|string $socialProviderName The name of the social provider.
	 * @param Variable $socialProfile The social profile after authentication will be stored here (as a PHP object)
	 * @param HybridAuthFactory $hybridAuthFactory The HybridAuth object used to perform the authentication
	 * @param Variable $errorMessage Error message (if any)
	 * @param Variable $errorException The triggered exception (if any)
	 * @param ActionInterface[] $onSuccess List of actions to be performed if the authentication is successful. Note: these actions can access the profile in the $socialProfile variable.
	 * @param ActionInterface[] $onFailure List of actions to be performed if the authentication fails. Note: these actions can access the error message in the $errorMessage variable and exception in the $errorException variable
	 */
	public function __construct($socialProviderName, Variable $socialProfile, HybridAuthFactory $hybridAuthFactory,
			Variable $errorMessage, Variable $errorException,
			array $onSuccess = array (), array $onFailure = array()) {
		$this->socialProviderName = $socialProviderName;
		$this->socialProfile = $socialProfile;
		$this->hybridAuthFactory = $hybridAuthFactory;
		$this->onSuccess = $onSuccess;
		$this->onFailure = $onFailure;
		$this->errorMessage = $errorMessage;
		$this->errorException = $errorException;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Utils\Action\ActionInterface::run()
	 */
	public function run() {
		$providerName = ValueUtils::val($this->socialProviderName);
		
		try{
			$hybridAuth = $this->hybridAuthFactory->getHybridAuth();
			$adapter = $hybridAuth->authenticate( $providerName );
			$user_profile = $adapter->getUserProfile();
		} catch (\Exception $e) {
			switch( $e->getCode() ){
				case 0 : $error = "Unspecified error."; break;
				case 1 : $error = "Hybriauth configuration error."; break;
				case 2 : $error = "Provider not properly configured."; break;
				case 3 : $error = "Unknown or disabled provider."; break;
				case 4 : $error = "Missing provider application credentials."; break;
				case 5 : $error = "Authentication failed. The user has canceled the authentication or the provider refused the connection."; break;
				case 6 : $error = "User profile request failed. Most likely the user is not connected to the provider and he should try to authenticate again.";
				$adapter->logout();
				break;
				case 7 : $error = "User not connected to the provider.";
				$adapter->logout();
				break;
			}
			$this->errorMessage->setValue($error.($e->getMessage()?' '.$e->getMessage():''));
			$this->errorException->setValue($e);
			foreach ($this->onFailure as $action) {
				$action->run();
			}
			return;
		}
		
		$this->socialProfile->setValue($user_profile);
		foreach ($this->onSuccess as $action) {
			$action->run();
		}
	}
	
}