<?php 

class OCLCResourceSharingForGroupsForm extends DataObject{
    public $__table = 'oclc_resource_sharing_for_groups_form';
    public $id;
    public $name;
    public $showAuthor;
    public $showEdition;
    public $showPublisher;
    public $showIsbn;
    public $showIssn;
    public $showAcceptFee;
    public $showMaximumFee;
    public $feeInformationText;
    // public $showTitle; - alway show
    // public $showPickupLocation; - alway show
    // public $showNote; - alway show

    // TODO: identify and add necessary methods
	// TODO: add the oclc_resource_sharing_for_groups_form_location table so that forms can be assigned to specific locations
    public static function getObjectStructure($context = ''): array {
		// $locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer OCLC Resource Sharing For Groups Forms'));

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
				'description' => 'The Name of the Form',
				'maxLength' => 50,
			],
			'showAuthor' => [
				'property' => 'showAuthor',
				'type' => 'checkbox',
				'label' => 'Show Author?',
				'description' => 'Whether or not the user should be prompted to enter the author name',
			],
			'showPublisher' => [
				'property' => 'showPublisher',
				'type' => 'checkbox',
				'label' => 'Show Publisher?',
				'description' => 'Whether or not the user should be prompted to enter the publisher name',
			],
			'showIsbn' => [
				'property' => 'showIsbn',
				'type' => 'checkbox',
				'label' => 'Show ISBN?',
				'description' => 'Whether or not the user should be prompted to enter the ISBN',
			],
            'showIssn' => [
				'property' => 'showIssn',
				'type' => 'checkbox',
				'label' => 'Show ISSN?',
				'description' => 'Whether or not the user should be prompted to enter the ISSN',
			],
			'showAcceptFee' => [
				'property' => 'showAcceptFee',
				'type' => 'checkbox',
				'label' => 'Show Accept Fee?',
				'description' => 'Whether or not the user should be prompted to accept the fee (if any)',
			],
			'showMaximumFee' => [
				'property' => 'showMaximumFee',
				'type' => 'checkbox',
				'label' => 'Show Maximum Fee?',
				'description' => 'Whether or not the user should be prompted for the maximum fee they will pay',
			],
			'feeInformationText' => [
				'property' => 'feeInformationText',
				'type' => 'textarea',
				'label' => 'Fee Information Text',
				'description' => 'Text to be displayed to give additional information about the fees charged.',
				'maxLength' => 5000,
			],
			// 'locations' => [
			// 	'property' => 'locations',
			// 	'type' => 'multiSelect',
			// 	'listStyle' => 'checkboxSimple',
			// 	'label' => 'Locations',
			// 	'description' => 'Define locations that make up this hold group',
			// 	'values' => $locationList,
			// 	'hideInLists' => false,
			// ],
		];
	}
}