<?php
namespace Mouf\Integration\HybridAuth\Actions;

use Mouf\Utils\Action\ActionInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\Variable;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Integration\HybridAuth\HybridAuthFactory;
use Doctrine\DBAL\Connection;
use SQLParser\Query\Select;
use Mouf\Security\UserService\UserServiceInterface;
use Mouf\Security\UserService\UserDaoInterface;
use Mouf\Integration\HybridAuth\UserManagerServiceInterface;
use Mouf\Integration\HybridAuth\SocialUserBean;
use Mouf\Validator\MoufValidatorInterface;

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
	 * @var Connection
	 */
	private $dbalConnection;

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
	 * The user manager service (used to create the new user)
	 *
	 * @var UserManagerServiceInterface
	 */
	private $userManagerService;
	
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
	 * List of actions to be performed if the user already existed in database.
	 * You will usually redirect the user to some place in your application.
	 * @var ActionInterface[]
	 */
	private $onUserLogged;
	
	/**
	 * List of actions to be performed if the user did not exist in database and has been just created.
	 * You will usually redirect the user to some place in your application.
	 * @var ActionInterface[]
	 */
	private $onUserCreated;

	/**
	 * 
	 * @param Variable $socialProviderName
	 * @param Variable $socialProfile
	 * @param Select $findSocialUser
	 * @param Connection $dbalConnection
	 * @param UserManagerServiceInterface $userManagerService
	 * @param bool $bindOnEmail When a user logs in via Facebook or another social network for the first time, if the user has already an account on the site, should we try to merge the 2 accounts based on the email address?
	 * @param Select $findUserIdFromMail A request that finds a user ID based on its mail address.
	 * @param ActionInterface[] $onUserLogged List of actions to be performed if the user already existed in database. You will usually redirect the user to some place in your application.
	 * @param ActionInterface[] $onUserCreated List of actions to be performed if the user did not exist in database and has been just created. You will usually redirect the user to some place in your application.
	 */
	public function __construct(Variable $socialProviderName, Variable $socialProfile, Select $findSocialUser,
			Connection $dbalConnection, UserDaoInterface $userDao, UserServiceInterface $userService, UserManagerServiceInterface $userManagerService, $bindOnEmail = true, Select $findUserIdFromMail = null,
			array $onUserLogged = array(), array $onUserCreated = array()) {
		$this->socialProviderName = $socialProviderName;
		$this->socialProfile = $socialProfile;
		$this->findSocialUser = $findSocialUser;
		$this->dbalConnection = $dbalConnection;
		$this->userDao = $userDao;
		$this->userService = $userService;
		$this->userManagerService = $userManagerService;
		$this->bindOnEmail = $bindOnEmail;
		$this->findUserIdFromMail =$findUserIdFromMail;
		$this->onUserLogged = $onUserLogged;
		$this->onUserCreated = $onUserCreated;
	}
	
	/**
	 * This function will check in database if the user (in the socialProviderProfile) does exist or not.
	 * If it does not exist, it will be created.
	 * If it does not exist but a user exists with the same email adress, it can be bound.
	 * 
	 * (non-PHPdoc)
	 * @see \Mouf\Utils\Action\ActionInterface::run()
	 */
	public function run() {
		$providerName = ValueUtils::val($this->socialProviderName);
		/* @var $user_profile \Hybrid_User_Profile */
		$user_profile = ValueUtils::val($this->socialProfile);
		$providerUid = $user_profile->identifier;
		
		$sql = $this->findSocialUser->toSql(array("provider"=>$providerName,
											"provider_uid"=>$providerUid), $this->dbalConnection);
		
		$userId = $this->dbalConnection->fetchColumn($sql);
		
		// 1 - check if user already have authenticated using this provider before
		if ($userId) {
			$userBean = $this->userDao->getUserById($userId);
			$this->userService->loginWithoutPassword($userBean->getLogin());
			foreach ($this->onUserLogged as $action) {
				$action->run();
			}
			return;
		}
		
		// 2- if user never authenticated, here lets check if the user email we got from the provider already exists in our database
		// if authentication does not exist, but the email address returned  by the provider does exist in database,
		if($user_profile->email && $this->bindOnEmail){
			$sql = $this->findUserIdFromMail->toSql(array("email"=>$user_profile->email));
			$userId = $this->dbalConnection->fetchColumn($sql);
			
			if ($userId) {
				$this->insertIntoAuthentications($userId, $providerName, $user_profile);
				
				$userBean = $this->userDao->getUserById($userId);
				$this->userService->loginWithoutPassword($userBean->getLogin());
				foreach ($this->onUserLogged as $action) {
					$action->run();
				}
				return;
			}
		}
		
		// 3- the user does not exist in database, we must create it.
		$userId = $this->userManagerService->saveUser($user_profile);
		
		$this->insertIntoAuthentications($userId, $providerName, $user_profile);
		
		$userBean = $this->userDao->getUserById($userId);
		$this->userService->loginWithoutPassword($userBean->getLogin());
		
		foreach ($this->onUserCreated as $action) {
			$action->run();
		}
	}
	
	private function insertIntoAuthentications($userId, $providerName, \Hybrid_User_Profile $user_profile) {
		$this->dbalConnection->insert('authentications', ['user_id' => $userId,
                                                            'provider' => $providerName,
                                                            'provider_uid' => $user_profile->identifier,
                                                            'profile_url' => $user_profile->profileURL,
                                                            'website_url' => $user_profile->webSiteURL,
                                                            'photo_url' => $user_profile->photoURL,
                                                            'display_name' => $user_profile->displayName,
                                                            'description' => $user_profile->description,
                                                            'first_name' => $user_profile->firstName,
                                                            'last_name' => $user_profile->lastName,
                                                            'gender' => $user_profile->gender,
                                                            'language' => $user_profile->language,
                                                            'age' => $user_profile->age,
                                                            'birth_day' => $user_profile->birthDay,
                                                            'birth_month' => $user_profile->birthMonth,
                                                            'birth_year' => $user_profile->birthYear,
                                                            'email' => $user_profile->email,
                                                            'email_verified' => $user_profile->emailVerified,
                                                            'phone' => $user_profile->phone,
                                                            'address' => $user_profile->address,
                                                            'country' => $user_profile->country,
                                                            'region' => $user_profile->region,
                                                            'city' => $user_profile->city,
                                                            'zip' => $user_profile->zip,
															'created_at' => date('Y-m-d H:i:s')
        ]);
	}
	
	/**
	 * Generates a login name from the information we have.
	 * 
	 * @param object $user_profile
	 */
	protected function generateLogin($user_profile) {
		if ($user_profile->email) {
			$login = $user_profile->email;
		} else {
			$login = $user_profile->firstName.".".$user_profile->lastName;
		}
		if ($this->userDao->getUserByLogin($login) == null) {
			return $login;
		}
		$count = 2;
		while (true) {
			if ($this->userDao->getUserByLogin($login.'_'.$count) == null) {
				return $login.'_'.$count;
			}
			$count++;
		}
	}
	
}