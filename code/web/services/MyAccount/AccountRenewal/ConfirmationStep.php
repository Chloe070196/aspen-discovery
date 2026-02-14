<?php
// DRAFT - AI Generated (Gemini)
require_once ROOT_DIR . '/services/Stepper/Step.php';
require_once ROOT_DIR . '/Drivers/Koha.php';
require_once ROOT_DIR . '/sys/UserAccount.php';
require_once ROOT_DIR . '/sys/Interface.php'; // Required for global translate() function

class ConfirmationStep extends Step {
    private const KEY = 'confirmation';
    private const TITLE = 'Confirmation';

    public function __construct() {
        parent::__construct(self::KEY, self::TITLE);
    }

    public function process(array $formData): array {
        $errors = [];

        // This step implies the final action: calling postAccountRenewal
        if (!empty($formData['confirm_renewal_action'])) { // Assuming a button click or hidden field to trigger final action
            $patron = UserAccount::getActiveUserObj();
            if ($patron) {
                // Need to ensure the Koha driver is instantiated with the correct AccountProfile
                // Assuming getAccountProfile() correctly fetches the profile for the current user's ILS.
                $kohaDriver = new Koha($patron->getAccountProfile());
                $renewalResult = $kohaDriver->postAccountRenewal(); // The method we updated

                if ($renewalResult['success']) {
                    $this->saveData(['renewal_success' => true, 'renewal_data' => $renewalResult['data']]);
                    UserAccount::clearStepperWorkflowCache(); // Clear the entire workflow cache on successful completion
                } else {
                    $errors[] = $renewalResult['message'] ?? translate(['text' => 'Failed to complete account renewal.', 'isPublicFacing' => true]);
                    $this->saveData(['renewal_success' => false, 'renewal_error' => ($renewalResult['message'] ?? 'Failed to complete account renewal.')]);
                }
            } else {
                $errors[] = translate(['text' => 'User not logged in or active patron not found.', 'isPublicFacing' => true]);
            }
        } else {
            $errors[] = translate(['text' => 'Please confirm the account renewal to proceed.', 'isPublicFacing' => true]);
        }

        return $errors;
    }

    public function getTemplatePath(): string {
        return 'AccountRenewal/confirmation.tpl';
    }

    public function isComplete(): bool {
        // This step is considered complete if the renewal process was triggered and successful.
        return ($this->data['renewal_success'] ?? false) === true;
    }
}
