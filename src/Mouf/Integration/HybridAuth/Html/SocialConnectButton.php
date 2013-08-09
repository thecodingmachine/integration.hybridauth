<?php
namespace Mouf\Integration\HybridAuth\Html;

use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Html\Renderer\Renderable;
use Mouf\Utils\Common\UrlInterface;
use Mouf\Integration\HybridAuth\HybridAuthFactory;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Integration\HybridAuth\ProvidersInterface;

/**
 * This class can display a social connect button (Facebook connect, Twitter connect, Google connect...)
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class SocialConnectButton implements HtmlElementInterface {

	use Renderable;
	
	/**
	 * Pointer to HybridAuth
	 *
	 * @var HybridAuthFactory
	 */
	private $hybridAuth;
	
	/**
	 * The HybridAuth provider we target (Facebook, Google, etc...)
	 *
	 * @var ProvidersInterface
	 */
	private $provider;
	
	/**
	 * The URL to the Facebook login.
	 * 
	 * @var string|UrlInterface
	 */
	private $url;
	
	/**
	 * Url to the image.
	 * 
	 * @var string
	 */
	private $imageUrl;
	

	public function __construct(HybridAuthFactory $hybridAuth, ProvidersInterface $provider, $url, 
			$imageUrl) {
		$this->hybridAuth = $hybridAuth;
		$this->provider = $provider;
		$this->url = $url;
		$this->imageUrl = $imageUrl;
	}

	/**
	 * @return HybridAuthFactory
	 */
	public function getHybridAuth() {
		return $this->hybridAuth;
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		if ($this->url instanceof UrlInterface) {
			return $this->url->getUrl();
		}
		return $this->url;
	}
	
	/**
	 * @return string
	 */
	public function getImageUrl() {
		if (strpos($this->imageUrl, '/') === 0 || strpos($this->imageUrl, 'http://') === 0 || strpos($this->imageUrl, 'https://') === 0) {
			return $this->imageUrl;
		} else {
			return ROOT_URL.$this->imageUrl;
		}
	}
	
	/**
	 * @return HybridAuthFactory
	 */
	public function isConnected() {
		return $this->hybridAuth->getHybridAuth()->isConnectedWith($this->provider->getProviderName());
	}
	
	/**
	 * Returns the name of the provider (Facebook, Twitter, ...)
	 * @return string
	 */
	public function getProviderName() {
		return $this->provider->getProviderName();
	}
}