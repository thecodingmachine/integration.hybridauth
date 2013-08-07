<?php
namespace Mouf\Integration\HybridAuth\Actions;

use Mouf\Utils\Action\ActionInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\Variable;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Integration\HybridAuth\HybridAuthFactory;
use Mouf\Database\DBConnection\ConnectionInterface;
use SQLParser\Query\Select;
use Mouf\Security\UserService\UserServiceInterface;
use Mouf\Security\UserService\UserDaoInterface;

/**
 * This action is typically triggered in the onSuccess callback of the SocialAuthentication class.
 * It logs users in or create a user account and log them in.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class PerformSocialLoginAction implements ActionInterface {

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
	 * The request that returns the ID of the user if it exists.
	 * 
	 * @var Select
	 */
	private $findSocialUser;
	
	/**
	 * The connection to the database
	 *
	 * @var ConnectionInterface
	 */
	private $dbConnection;

	/**
	 * The user service. It will be used to log the user.
	 *
	 * @var UserDaoInterface
	 */
	private $userDao;
	
	/**
	 * The user service. It will be used to log the user.
	 * 
	 * @var UserServiceInterface
	 */
	private $userService;
	
	/**
	 * When a user logs in via Facebook or another social network for the first time,
	 * if the user has already an account on the site, should we try to merge the 2 accounts
	 * based on the email address?
	 * 
	 * @var bool
	 */
	private $bindOnEmail;
	
	/**
	 * A request that finds a user ID based on its mail address.
	 *  
	 * @var Select
	 */
	private $findUserIdFromMail;
	
	/**
	 * 
	 * @param Variable $socialProfile
	 * @param Select $findSocialUser
	 * @param ConnectionInterface $dbConnection
	 * @param bool $bindOnEmail
	 * @param Select $findUserIdFromMail
	 */
	public function __construct(Variable $socialProviderName, Variable $socialProfile, Select $findSocialUser,
			ConnectionInterface $dbConnection, $bindOnEmail = true, Select $findUserIdFromMail = null) {
		$this->socialProviderName = $socialProviderName;
		$this->socialProfile = $socialProfile;
		$this->findSocialUser = $findSocialUser;
		$this->dbConnection = $dbConnection;
		$this->bindOnEmail = $bindOnEmail;
		$this->findUserIdFromMail =$findUserIdFromMail;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Utils\Action\ActionInterface::run()
	 */
	public function run() {
		$providerName = ValueUtils::val($this->socialProviderName);
		$socialProfile = ValueUtils::val($this->socialProfile);
		$providerUid = $socialProfile->identifier;
		
		$sql = $this->findSocialUser->toSql(array("provider"=>$providerName,
											"provider_uid"=>$providerUid), $this->dbConnection);
		
		$userId = $this->dbConnection->getOne($sql);
		
		// 1 - check if user already have authenticated using this provider before
		if ($userId) {
			$userBean = $this->userDao->getUserById($userId);
			$this->userService->loginWithoutPassword($userBean->getLogin());
			return;
		}
		
		// 2- if user never authenticated, here lets check if the user email we got from the provider already exists in our database
		// if authentication does not exist, but the email address returned  by the provider does exist in database,
		// then we tell the user that the email  is already in use
		// but, its up to you if you want to associate the authentication with the user having the adresse email in the database
		if($user_profile->email && $this->bindOnEmail){
			$sql = $this->findUserIdFromMail->toSql(array("email"=>$user_profile->email));
			$userId = $this->dbConnection->getOne($sql);
			
			if ($userId) {
				$userBean = $this->userDao->getUserById($userId);
				$this->userService->loginWithoutPassword($userBean->getLogin());
				return;
			}
		}
		
		// 3- the user does not exist in database, we must create it.
		
	}
}