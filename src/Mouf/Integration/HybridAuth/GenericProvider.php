<?php
namespace Mouf\Integration\HybridAuth;

/**
 * A default provider for HybridAuth (can be used by any provider: Facebook, Google, ...)
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class GenericProvider implements  ProvidersInterface {
	
	private $providerName;
	private $enabled = true;
	private $id;
	private $key;
	private $secret;
	private $scope;
	private $wrapper;
	
	/**
	 * The provider name (the key in the config array passed to HybridAuth constructor)
	 */
	public function getProviderName() {
		return $this->providerName;
	}

	/**
	 * The provider name (the key in the config array passed to HybridAuth constructor)
	 * 
	 * @param string $providerName
	 */
	public function setProviderName($providerName) {
		$this->providerName = $providerName;
		return $this;
	}
	
	/**
	 * Whether the provider is enabled or not (defaults to true)
	 * @return bool
	 */
	public function getEnabled() {
		return $this->enabled;
	}
	
	/**
	 * Whether the provider is enabled or not (defaults to true)
	 * @param bool $enabled
	 */
	public function setEnabled($enabled) {
		$this->enabled = $enabled;
		return $this;
	}
	
	/**
	 * Id is your (facebook or live) application id 
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Id is your (facebook or live) application id 
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * key is your (twitter, myspace, linkedin, etc.) application consumer key 
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}
	
	/**
	 * key is your (twitter, myspace, linkedin, etc.) application consumer key 
	 * @param string $key
	 */
	public function setKey($key) {
		$this->key = $key;
		return $this;
	}
	
	/**
	 * secret is your application consumer secret 
	 * @return string
	 */
	public function getSecret() {
		return $this->secret;
	}
	
	/**
	 * secret is your application consumer secret 
	 * @param string $secret
	 */
	public function setSecret($secret) {
		$this->secret = $secret;
		return $this;
	}
	
	/**
	 * (Optional) used for facebook in case you want to provider the permissions requested by default
	 * @return string
	 */
	public function getScope() {
		return $this->scope;
	}
	
	/**
	 * (Optional) used for facebook in case you want to provider the permissions requested by default
	 * @param string $scope
	 */
	public function setScope($scope) {
		$this->scope = $scope;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getWrapper()
	{
		return $this->wrapper;
	}

	/**
	 * If you want tu use additional provider you add to set this wrapper wich is an array. Just use Php code like this one:
	 * return array('class'=>'Hybrid_Providers_Steam',
	 * 'path' => ROOT_PATH.'vendor/hybridauth/hybridauth/additional-providers/hybridauth-steam/Providers/Steam.php');
	 * @param array $wrapper
	 */
	public function setWrapper($wrapper)
	{
		$this->wrapper = $wrapper;
	}

	/**
	 * The configuration array passed to HybridAuth constructor
	 */
	public function getConfigArray() {
		$ret = array("enabled"=>$this->enabled);
		if ($this->key) {
			$ret['keys']['key'] = $this->key;
		}
		if ($this->id) {
			$ret['keys']['id'] = $this->id;
		}
		if ($this->secret) {
			$ret['keys']['secret'] = $this->secret;
		}
		if ($this->scope) {
			$ret['scope'] = $this->scope;
		}
		if ($this->wrapper) {
			$ret['wrapper'] = $this->wrapper;
		}
		return $ret;
	}
}