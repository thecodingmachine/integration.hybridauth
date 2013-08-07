<?php
namespace Mouf\Integration\HybridAuth;

use Mouf\Security\UserService\AdvancedUserInterface;

/**
 * A bean describing a user from a social network.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class SocialUserBean implements AdvancedUserInterface {
	private $id;
	private $login;
	private $email;
	private $lastName;
	private $firstName;

	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	public function getLogin() {
		return $this->login;
	}
	
	public function setLogin($login) {
		$this->login = $login;
		return $this;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}
	
	public function getLastName() {
		return $this->lastName;
	}
	
	public function setLastName($lastName) {
		$this->lastName = $lastName;
		return $this;
	}
	
	public function getFirstName() {
		return $this->firstName;
	}
	
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
		return $this;
	}
}