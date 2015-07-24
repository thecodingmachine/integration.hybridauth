<?php
namespace Mouf\Integration\HybridAuth\Services;

use Mouf\Security\UserService\AuthenticationListenerInterface;
use Mouf\Security\UserService\UserServiceInterface;
use Mouf\Integration\HybridAuth\HybridAuthFactory;

class HybridAuthUserServiceListener implements AuthenticationListenerInterface {

	/**
	 * 
	 * @var HybridAuthFactory
	 */
	private $hybridAuthFactory;
	
	public function __construct(HybridAuthFactory $hybridAuthFactory) {
		$this->hybridAuthFactory = $hybridAuthFactory;
	}
	
	/**
	 * This method is called just after a log-in occurs.
	 *
	 * @param UserServiceInterface $userService The service that performed the log-in
	 */
	public function afterLogIn(UserServiceInterface $userService) {
		
	}
	
	/**
	 * This method is called just before the current user logs out.
	 *
	 * @param UserServiceInterface $userService The service that performed the log-out
	*/
	public function beforeLogOut(UserServiceInterface $userService) {
		$hybridAuth = $this->hybridAuthFactory->getHybridAuth();
		$hybridAuth->logoutAllProviders();
	}
}