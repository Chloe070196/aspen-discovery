<?php
// DRAFT - AI Generated (Gemini)
require_once ROOT_DIR . '/sys/UserAccount.php';

class Step {
    private string $key;
    private string $title;
    private ?array $data = null;

    public function __construct(string $key, string $title, $data = null) {
        $this->key = $key;
        $this->title = $title;
        $this->loadData($data);
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getData(): ?array {
        return $this->data;
    }

    protected function loadData($data = null): void {
        if (!empty($data)) {
            $this->data = $data;
            return;
        }
        $this->data = UserAccount::getStepperWorkflowCache($this->key);
    }

    public function saveData(array $data): void {
        $this->data = $data;
        UserAccount::setStepperWorkflowCache($this->key, $data);
    }
}