<?php
namespace Mouf\Integration\HybridAuth\Html;

use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Html\Renderer\Renderable;
use Mouf\Utils\Common\UrlInterface;
use Mouf\Integration\HybridAuth\HybridAuthFactory;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Integration\HybridAuth\ProvidersInterface;


/**
 * This will display the profile picture of the user.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class SocialProfilePicture implements HtmlElementInterface {

	use Renderable;
	
	/**
	 * Pointer to HybridAuth
	 *
	 * @var HybridAuthFactory
	 */
	private $hybridAuth;
	
	/**
	 * The HybridAuth providers we target (Facebook, Google, etc...)
	 * If empty array, we will scan all providers until we find one we are logged to.
	 * Each provider will be checked in turn. The first provider that returns a picture
	 * will be displayed.
	 * 
	 * @var array<ProvidersInterface>
	 */
	private $providers;
	
	private $cssClassName;
	
	/**
	 * 
	 * @param HybridAuthFactory $hybridAuth Pointer to HybridAuth
	 * @param array<ProvidersInterface> $providers The HybridAuth providers we target (Facebook, Google, etc...). If empty array, we will scan all providers until we find one we are logged to.
	 * @param string $cssClassName the CSS class name to be applied to the image.
	 */
	public function __construct(HybridAuthFactory $hybridAuth, array $providers = array(), $cssClassName = "social_picture") {
		$this->hybridAuth = $hybridAuth;
		$this->providers = $providers;
		$this->cssClassName =$cssClassName;
	}

	/**
	 * @return HybridAuthFactory
	 */
	public function getHybridAuth() {
		return $this->hybridAuth;
	}
	
	private $pictureUrl;
	
	/**
	 * @return string
	 */
	public function getPictureUrl() {
		if ($this->pictureUrl) {
			return $this->pictureUrl;
		}
		
		$providers = $this->providers;
		if (empty($providers)) {
			$providers = $this->hybridAuth->getProviders();
		}
		$hybridAuth = $this->hybridAuth->getHybridAuth();
		foreach ($providers as $provider) {
			$providerName = $provider->getProviderName();
			if ($hybridAuth->isConnectedWith($providerName)) {
				$url = $hybridAuth->getAdapter($providerName)->getUserProfile()->photoURL;
				if ($url) {
					$this->pictureUrl = $url;
					return $url;
				}
			}
		}
		return null;
	}

	/**
	 * Returns the CSS class name to be applied to the image.
	 * @return string
	 */
	public function getCssClassName() {
		return $this->cssClassName;
	}
	
	
	
}