<?php
namespace Mouf\Integration\HybridAuth;

use Hybrid_Auth;

/**
 * This class is a simple wrapper around HybridAuth.
 * It is designed to be easily usable from Mouf, using DI.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class HybridAuthFactory {
	
	/**
	 * @var ProvidersInterface[]
	 */
	private $providers;
	
	/**
	 * Set to true to debug. Debug output will go in the debug file (below)
	 * @var bool
	 */
	private $debugMode;
	
	/**
	 * The name of the debug file where debug information will be written.
	 * If it does not start with /, it will be relative to the ROOT_PATH
	 * @var string
	 */
	private $debugFile;
	
	/**
	 * 
	 * @var Hybrid_Auth
	 */
	private $hybridAuth;
	
	/**
	 * 
	 * @param ProvidersInterface[] $providers The list of providers supported by HybridAuth.
	 */
	public function __construct(array $providers = array()) {
		$this->providers = $providers;
	}
	
	/**
	 * @return Hybrid_Auth
	 */
	public function getHybridAuth() {
		if (!$this->hybridAuth) {
			// Note: base_url has been set to index.php explicitly (unlike stated in the docs) in case index.php is not the default file loaded.
			$url = 'http://';
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') {
				$url = 'https://';
			}
			$config = array("base_url" => $url.$_SERVER['HTTP_HOST'].ROOT_URL.'vendor/hybridauth/hybridauth/hybridauth/index.php');
			foreach ($this->providers as $provider) {
				$config['providers'][$provider->getProviderName()] = $provider->getConfigArray();
			}
			
			if ($this->debugFile != '' && $this->debugMode) {
				$config['debug_mode'] = true;
				if (strpos($this->debugFile, '/') !== 0) {
					$config['debug_file'] = ROOT_PATH.$this->debugFile;
				} else {
					$config['debug_file'] = $this->debugFile;
				}
			}
			
			$this->hybridAuth = new Hybrid_Auth($config);
		}
		
		return $this->hybridAuth;
	}

	/**
	 * Set to true to debug. Debug output will go in the debug file (below)
	 * @param bool $debugMode
	 * @return \Mouf\Integration\HybridAuth\HybridAuthFactory
	 */
	public function setDebugMode($debugMode) {
		$this->debugMode = $debugMode;
		return $this;
	}
	
	/**
	 * The name of the debug file where debug information will be written.
	 * If it does not start with /, it will be relative to the ROOT_PATH
	 * 
	 * @param string $debugFile
	 * @return \Mouf\Integration\HybridAuth\HybridAuthFactory
	 */
	public function setDebugFile($debugFile) {
		$this->debugFile = $debugFile;
		return $this;
	}

	/**
	 * @return ProvidersInterface[]
	 */
	public function getProviders() {
		return $this->providers;
	}
	
}