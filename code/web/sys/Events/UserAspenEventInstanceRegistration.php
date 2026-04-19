<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventInstance.php';

class UserAspenEventInstanceRegistration extends DataObject {
	public $__table = 'user_aspen_event_instance_registrations';
	public $id;
	public $userId;
	public $eventInstanceId;
	public $status;
	public $createdAt;
	public $notifiedAt;
	public $registeredByStaffId;

	const VALID_STATUSES = ['waiting', 'invited', 'registered'];

	public function getUniquenessFields(): array {
		return [
			'userId',
			'eventInstanceId',
		];
	}

	public function getNumericColumnNames(): array {
		return [
			'userId',
			'eventInstanceId',
			'success',
			'attended',
			'registeredByStaffId',
		];
	}

	public function delete(bool $useWhere = false, bool $hardDelete = false): bool|int {
		if (!$useWhere && $this->id) {
			require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistrationAttendee.php';
			UserAspenEventInstanceRegistrationAttendee::deleteForRegistration((int)$this->id);
		}
		return parent::delete($useWhere, $hardDelete);
	}

	public function isUserRegisteredForEvent(): bool {
		if($this->status) {
			return $this->status === 'registered';
		}

		if (!$this->find(true)) {
			return false;
		}
		return $this->status === 'registered';
	}

	public function registerUser(): bool {
		$status = 'registered';

		if ($this->status === 'registered') {
			return false;
		}

		if (!$this->validateStatus($status)) {
			return false;
		}

		if ($this->find(true)) {
			$this->status = $status;
			return $this->update();
		}

		$this->status = $status;
		$this->createdAt = date('Y-m-d H:i:s');
		return $this->insert();
	}

	public function addUserToWaitingList(): bool {
		if ($this->find(true)) {
			return false;
		}

		$status = 'waiting';

		if (!$this->validateStatus($status)) {
			return false;
		}

		$this->createdAt = date('Y-m-d H:i:s');
		$this->status = $status;
		return $this->insert();
	}

	public function getWaitingListInfo(): array {
		$default = ['onWaitingList' => false, 'position' => null, 'canRegister' => false];

		if (!$this->status && !$this->find(true)) {
			return $default;
		}

		if (!in_array($this->status, ['waiting', 'invited'], true)) {
			return $default;
		}

		return [
			'onWaitingList' => true,
			'position' => self::getWaitingListPosition($this->eventInstanceId, $this->createdAt),
			'canRegister' => $this->status === 'invited',
		];
	}

	/**
	 * Static because DataObject uses set properties as implicit WHERE clauses.
	 * Calling this on an instance with userId set would contaminate the count
	 * query, returning only the caller's own rows instead of all queue entries.
	 */
	public static function getWaitingListPosition(int $eventInstanceId, string $createdAt): ?int {
		$query = new UserAspenEventInstanceRegistration();
		$query->eventInstanceId = $eventInstanceId;
		$query->whereAdd('status IN ("waiting", "invited")');
		$query->whereAdd("createdAt < " . $query->escape($createdAt));

		return $query->count() + 1;
	}

	private function validateStatus(string $status): bool {
		return in_array($status, self::VALID_STATUSES, true);
	}

	/**
	 * Get the user object for the registered patron
	 * @return User|false
	 */
	public function getUser(): User|false {
		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = new User();
		$user->id = $this->userId;
		if ($user->find(true)) {
			return $user;
		}
		return false;
	}

	/**
	 * Get the staff user who registered this patron (if applicable)
	 * @return User|false
	 */
	public function getStaffUser(): User|false {
		if (empty($this->registeredByStaffId)) {
			return false;
		}
		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = new User();
		$user->id = $this->registeredByStaffId;
		if ($user->find(true)) {
			return $user;
		}
		return false;
	}

	/**
	 * Get the event instance this registration is for
	 * @return EventInstance|false
	 */
	public function getEventInstance(): EventInstance|false {
		$eventInstance = new EventInstance();
		$eventInstance->id = $this->eventInstanceId;
		if ($eventInstance->find(true)) {
			return $eventInstance;
		}
		return false;
	}

	/**
	 * Check if this registration was made by staff
	 * @return bool
	 */
	public function wasRegisteredByStaff(): bool {
		return !empty($this->registeredByStaffId);
	}

	public function saveEventFieldValue(int $eventFieldId, string $value): void {
		if (empty($value) && $value !== '0') {
			return;
		}
		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistrationEventField.php';
		$fieldEntry = new UserAspenEventInstanceRegistrationEventField();
		$fieldEntry->eventInstanceRegistrationId = $this->id;
		$fieldEntry->eventFieldId = $eventFieldId;
		if ($fieldEntry->find(true)) {
			return;
		}
		$fieldEntry->value = $value;
		$fieldEntry->insert();
	}
}
