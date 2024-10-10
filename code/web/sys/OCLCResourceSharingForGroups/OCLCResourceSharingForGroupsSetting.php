<?php 

class OCLCResourceSharingForGroupsSetting extends DataObject {
	public $__table = 'oclc_resource_sharing_for_groups_setting';
	public $id;
	public $name;
	public $clientKey; 
	public $clientSecret;
	public $oclcSymbol;
	public $serviceBaseUrl;
	public $authBaseUrl;
	public $scopes;
	public $expirationDate; // so Aspen can warn if a WSKey marked as expired is used

	// fields that could be added later
	// public $WSKeyName;
	// public $service; // default: Resource Request Sharing API
	// public $status; // default: ? + should be enum?
	// public $contextOverrides;
	// public $redirectUrl;
	// public $integrationName; 
	// public $corsDomain;

	function getEncryptedFieldNames(): array {
		return ['clientKey', 'clientSecret'];
	}

	public static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'description' => 'The Name of this Setting Profile',
				'maxLength' => 50,
			],
			'clientKey' => [
				'property' => 'clientKey',
				'type' => 'storedPassword',
				'label' => 'OCLC WSKey Client ID',
				'description' => 'The Client ID of the OCLC-issued WSKey to be used for authentication when making requests to Resource Sharing Requests APIs',
				'hideInLists' => true,
			],
			'clientSecret' => [
				'property' => 'clientSecret',
				'type' => 'storedPassword',
				'label' => 'OCLC WSKey Secret',
				'description' => 'The Secret of the OCLC-issued WSKey to be used for authentication when making requests to Resource Sharing Requests APIs',
				'hideInLists' => true,
			],
			'oclcSymbol' => [
				'property' => 'oclcSymbol',
				'type' => 'text',
				'label' => 'OCLC Symbol',
				'description' => 'The OCLC Symbol assigned to your institution',
				'hideInLists' => true,
			],
			'serviceBaseUrl' => [ // may be redundant - might not change
				'property' => 'serviceBaseUrl',
				'type' => 'url',
				'label' => 'Resource Sharing API URL',
				'description' => 'The base URL of the Resource Sharing Requests API',
				'maxLength' => 255,
			],
			'authBaseUrl' => [ // may be redundant - will not change
				'property' => 'authBaseUrl',
				'type' => 'url',
				'label' => 'OCLC Authentication URL ',
				'description' => 'The base URL of the Resource Sharing Requests API',
				'maxLength' => 255,
				'default' => 'https://oauth.oclc.org/'
			],
			'scopes' => [
				'property' => 'scopes',
				'type' => 'textarea',
				'label' => 'Allowed Request Types',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'values' => ['resource-sharing', 'resource-sharing:my-requests', 'resource-sharing:create-requests', 'resource-sharing:manage-requests', 'resource-sharing:read-requests', 'resource-sharing:search-requests'],
				'description' => 'A list of the types of requests that can be made',
				'default' => 'resource-sharing, resource-sharing:my-requests, resource-sharing:create-requests, resource-sharing:manage-requests, resource-sharing:read-requests, resource-sharing:search-requests',
			],
			'expirationDate' => [
				'property' => 'expirationDate',
				'type' => 'date',
				'label' => 'WSKey Expiration Date',
				'description' => 'The data when the WSKey will expire. Format: MM/DD/YYYY',
			],
		];
	}
}

// Libraries can be assigned a setting via the OCLCresourceSharingForGroupsId FK on the Library table
// TODO: add oclcResourceSharingForGroupsSettingId to the library table;
// TODO: determine which fields must be set as required