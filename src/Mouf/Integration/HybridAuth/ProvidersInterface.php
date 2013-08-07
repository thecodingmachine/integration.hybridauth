<?php
namespace Mouf\Integration\HybridAuth;

/**
 * Any class extending this interface can be used by HybridAuth.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
interface ProvidersInterface {
	/**
	 * The provider name (the key in the config array passed to HybridAuth constructor)
	 */
	public function getProviderName();
	
	/**
	 * The configuration array passed to HybridAuth constructor
	 */
	public function getConfigArray();
}