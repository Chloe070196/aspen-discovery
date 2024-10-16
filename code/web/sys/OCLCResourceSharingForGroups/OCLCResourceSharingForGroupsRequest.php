<?php

class OCLCResourceSharingForGroupsRequest extends DataObject {
	public $__table = 'user_oclc_resource_sharing_for_groups_request';
	public $id;
	public $oclcRequestId; // as assigned upon submission
	public $requestStatus;
	public $serviceType; // LOAN or COPY
	public $verification; // specifies the request comes from an Aspen Discovery site
	public $oclcRequesterRegistryId;
	public $userId;
	public $email;
	public $isbn;
	public $title;
	public $author;
	public $edition;
	public $publisher;
	public $feeAccepted;
	public $maximumFeeAmount;
	public $catalogKey;
	public $note;
	public $pickupLocation;
	public $datePlaced;
}
