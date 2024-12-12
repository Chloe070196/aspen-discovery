<?php
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsRequest.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsSetting.php';
require_once ROOT_DIR . '/sys/Utils/StringUtils.php';

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OCLCResourceSharingForGroupsDriver {
	private $accessToken;
	private $_registryId;

	public function __construct() {
		$homeLocation = Location::getUserHomeLocation();
		$this->_registryId = $homeLocation ? $homeLocation->oclcRegistryId : "" ;
	}

	// Controllers

	// Services - interacts with Aspen DB

	// Services - interacts with the Resource Sharing Request API from OCLC

	private function setAccessToken(OCLCResourceSharingForGroupsSetting $setting): void {
		require_once 'oauth2_client_php_league/autoload.php';
		$basicAuth_provider = new HttpBasicAuthOptionProvider();
		$setup_options = [
			'clientId' => $setting->clientKey,
			'clientSecret' => $setting->clientSecret,
			'urlAuthorize' => $setting->authBaseUrl . "auth", // not used for this grant type yet field still required - could set to ''
			'urlAccessToken' => $setting->authBaseUrl . "token",
			'urlResourceOwnerDetails' => '',
			'redirectUri' => '',
		];
		$provider = new GenericProvider($setup_options, ['optionProvider' => $basicAuth_provider]);
		try {
			$this->accessToken = $provider->getAccessToken('client_credentials', ['scope' => $setting->scopes . " context:" . $this->_registryId]);
			return;
		} catch (IdentityProviderException $e) {
			throw  new Exception($e->getMessage());
		};
	}

	// Helpers
}
