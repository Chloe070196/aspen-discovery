<?php 

class OCLCResourceSharingForGroupsForm extends DataObject{
	public $__table = 'oclc_resource_sharing_for_groups_form';
	public $id;
	public $name;
	public $introText;
	public $showAuthor;
	public $showEdition;
	public $showPublisher;
	public $showIsbn;
	public $showIssn;
	public $showOclcNumber;
	public $showAcceptFee;
	public $showMaximumFee;
	public $feeInformationText;
	public $showCatalogKey;

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
				'description' => 'The Name of the Form',
				'maxLength' => 50,
			],
			'introText' => [
				'property' => 'introText',
				'type' => 'textarea',
				'label' => 'Intro Text',
				'description' => 'Introductory Text to be displayed at the top of the form',
				'maxLength' => 5000,
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
			'showOclcNumber' => [
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
			'showCatalogKey' => [
				'property' => 'showCatalogKey',
				'type' => 'checkbox',
				'label' => 'Show Catalog Key?',
				'description' => 'Whether or not the user should be prompted for the catalog key',
			],
		];
	}

	public function getFormFields(?MarcRecordDriver $marcRecordDriver, ?string $volumeInfo = null): array {
		$fields = [];
		if ($this->introText) {
			$fields['introText'] = [
				'property' => 'introText',
				'type' => 'label',
				'label' => $this->introText,
				'description' => '',
			];
		}
		require_once ROOT_DIR . '/sys/Utils/StringUtils.php';
		$fields['title'] = [
			'property' => 'title',
			'type' => 'text',
			'label' => 'Title',
			'description' => 'The title of the title to be request',
			'maxLength' => 255,
			'required' => true,
			'default' => ($marcRecordDriver != null ? StringUtils::removeTrailingPunctuation($marcRecordDriver->getTitle()) : ''),
		];
		$fields['author'] = [
			'property' => 'author',
			'type' => ($this->showAuthor ? 'text' : 'hidden'),
			'label' => 'Author',
			'description' => 'The author of the title to request',
			'maxLength' => 255,
			'required' => false,
			'default' => ($marcRecordDriver != null ? $marcRecordDriver->getAuthor() : ''),
		];
		$publisher = '';
		if ($marcRecordDriver != null) {
			$publishers = $marcRecordDriver->getPublishers();
			if (count($publishers) > 0) {
				$publisher = reset($publishers);
			}
		}
		$fields['publisher'] = [
			'property' => 'publisher',
			'type' => ($this->showPublisher ? 'text' : 'hidden'),
			'label' => 'Publisher',
			'description' => 'The publisher of the title to request',
			'maxLength' => 255,
			'required' => false,
			'default' => $publisher,
		];

		// if an ISBN, ISSN, or OCLC number is present, pre-fill the field and only display this identifier
		if ($marcRecordDriver != null) {
			if (!empty($marcRecordDriver->getCleanISBN())) {
				$fields['isbn'] = [
					'property' => 'isbn',
					'type' => ($this->showIsbn ? 'text' : 'hidden'),
					'label' => 'ISBN',
					'description' => 'The ISBN of the title to request',
					'maxLength' => 20,
					'required' => true,
					'default' => ($marcRecordDriver != null ? $marcRecordDriver->getCleanISBN() : ''),
				];
			} else if (!empty($marcRecordDriver->getISSNs())) {
				$fields['issn'] = [
					'property' => 'issn',
					'type' => ($this->showIssn ? 'text' : 'hidden'),
					'label' => 'ISBN',
					'description' => 'The ISSN of the title to request',
					'maxLength' => 20,
					'required' => true,
					'default' => ($marcRecordDriver != null ? $marcRecordDriver->getISSNs()[0] : ''),
				];
			} else if (!empty($oclcNumber != null)) {
				/** @var File_MARC_Control_Field $oclcNumber */
				$oclcNumber = $marcRecordDriver->getMarcRecord()->getField('001');
				if ($oclcNumber != null) {
					$oclcNumberString = StringUtils::truncate($oclcNumber->getData(), 50);
				} else {
					$oclcNumberString = '';
				}
				$fields['oclcNumber'] = [
					'property' => 'oclcNumber',
					'type' => ($this->oclcNumber ? 'text' : 'hidden'),
					'lable' => 'OCLC Number',
					'description' => 'The OCLC Number of the title to request',
					'maxLength' => 50,
					'required' => true,
					'default' => $oclcNumberString,
				];
			} else {
				// if no ISBN, ISSN, or OCLC number are present, then let the user pick one and fill in the field
				$fields['uniqueIdentifierKey'] = [
					'property' => 'uniqueIdentifierKey',
					'type' => 'enum',
					'label' => 'Unique Record Identifier Key',
					'description' => 'Whether to use ISBN, ISSN, or OCLC Number to make the request',
					'required' => true,
					'values' => [
						'isbn' => 'ISBN',
						'issn' => 'ISSN',
						'oclcNumber' => 'OCLC Number'
					],
				];
				$fields['uniqueIdentifierValue'] = [
					'property' => 'uniqueIdentifierValue', 
					'type' => 'text',
					'label' => 'Unique Record Identifier',
					'description' => 'The unique identifier number',
					'maxLength' => 20,
					'required' => true,
				];
			}
		} else {
			// if no Marc Record driver is found, then let the user pick whther to submit an ISBN, ISSN, or OCLC and fill in the field
			$fields['uniqueIdentifierKey'] = [
				'property' => 'uniqueIdentifierKey',
				'type' => 'enum',
				'label' => 'Unique Record Identifier Key',
				'description' => 'Whether to use ISBN, ISSN, or OCLC Number to make the request',
				'required' => true,
				'values' => [
					'isbn' => 'ISBN',
					'issn' => 'ISSN',
					'oclcNumber' => 'OCLC Number'
				],
			];
			$fields['uniqueIdentifierValue'] = [
				'property' => 'uniqueIdentifierValue', 
				'type' => 'text',
				'label' => 'Unique Record Identifier',
				'description' => 'The unique identifier number',
				'maxLength' => 20,
				'required' => true,
			];
		}
	
		if ($this->showAcceptFee) {
			$fields['feeInformationText'] = [
				'property' => 'feeInformationText',
				'type' => 'label',
				'label' => $this->feeInformationText,
				'description' => '',
			];
			if ($this->showMaximumFee) {
				$fields['maximumFeeAmount'] = [
					'property' => 'maximumFeeAmount',
					'type' => 'currency',
					'label' => 'Maximum Fee ',
					'description' => 'The maximum fee you are willing to pay to have this title transferred to the library.',
					'default' => 0,
					'displayFormat' => '%0.2f',
				];
				$fields['acceptFee'] = [
					'property' => 'acceptFee',
					'type' => 'checkbox',
					'label' => 'I will pay any fees associated with this request up to the maximum amount defined above',
					'description' => '',
				];
			} else {
				$fields['acceptFee'] = [
					'property' => 'acceptFee',
					'type' => 'checkbox',
					'label' => 'I will pay any fees associated with this request',
					'description' => '',
				];
			}
		}
		$user = UserAccount::getLoggedInUser();
		$locations = $user->getValidPickupBranches($user->getCatalogDriver()->accountProfile->recordSource);
		$pickupLocations = [];
		foreach ($locations as $key => $location) {
			if ($location instanceof Location) {
				$pickupLocations[$location->code] = $location->displayName;
			} else {
				if ($key == '0default') {
					$pickupLocations[-1] = $location;
				}
			}
		}
		$fields['pickupLocation'] = [
			'property' => 'pickupLocation',
			'type' => 'enum',
			'values' => $pickupLocations,
			'label' => 'Pickup Location',
			'description' => 'Where you would like to pickup the title',
			'required' => true,
			'default' => $user->getHomeLocationCode(),
		];
		$fields['note'] = [
			'property' => 'note',
			'type' => 'textarea',
			'label' => 'Note',
			'description' => 'Any additional information you want us to have about this request',
			'required' => false,
			'default' => ($volumeInfo == null) ? '' : $volumeInfo,
		];
		$fields['catalogKey'] = [
			'property' => 'catalogKey',
			'type' => (($this->showCatalogKey && $marcRecordDriver != null) ? 'text' : 'hidden'),
			'label' => 'Record Number',
			'description' => 'The record number to be requested',
			'maxLength' => 20,
			'required' => false,
			'default' => ($marcRecordDriver != null ? $marcRecordDriver->getId() : ''),
		];
		return $fields;
	}
}