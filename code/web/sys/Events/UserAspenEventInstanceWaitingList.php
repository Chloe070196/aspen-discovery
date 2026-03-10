<?php /** @noinspection PhpMissingFieldTypeInspection */

class UserAspenEventInstanceWaitingList extends DataObject {
	public $__table = 'user_aspen_event_instance_waiting_list';
	public $id;
	public $userId;
	public $position;
	public $status;
	public $joinedAt;
	public $leftAt;
	public $notifiedAt;
	public $expiresAt;
	public $eventInstanceId;
	public $canRegister;
	public $canRegisterUntil;
	public $toastShown;

	public function updateWaitingListPositions(): void {
		require_once ROOT_DIR . '/sys/Events/Event.php';
		$event = new Event();
		$event->id = $eventInstance->eventId;
		if (!$event->find(true)) {
			return;
		}
		if ((int)$event->waitingListNumberOfSeats <= (int)$eventInstance->availableNumberOfWaitingListSeats) {
			return;
		}
		require_once ROOT_DIR . '/sys/Events/EventInstance.php';
		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			return;
		}

		$this->eventInstanceId = $eventInstanceId;
		$currentPosition = $this->position;
		$this->find();
		while ($listEntry = $this->fetch()) {
			(int)$listEntry->position--;
			$listEntry->update();
			$currentPosition++;
		}

	}
	
	public function incrementAvailableWaitingListSeats($eventInstanceId): void {
		require_once ROOT_DIR . '/sys/Events/Event.php';
		$event = new Event();
		$event->id = $eventInstance->eventId;
		if (!$event->find(true)) {
			return;
		}
		if ((int)$event->waitingListNumberOfSeats <= (int)$eventInstance->availableNumberOfWaitingListSeats) {
			return;
		}
		require_once ROOT_DIR . '/sys/Events/EventInstance.php';
		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if ($eventInstance->find(true)) {
			$eventInstance->availableNumberOfWaitingListSeats++;
			$logger->log("updated number of waiting list seats", Logger::LOG_ERROR);
			$eventInstance->update();
		}
	}
}
