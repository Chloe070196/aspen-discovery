<?php
// DRAFT - AI Generated (Gemini)
require_once ROOT_DIR . '/sys/UserAccount.php';
require_once ROOT_DIR . '/services/Stepper/Step.php';

class Stepper {
    private array $steps = [];
    private string $currentStepKey;
    private string $workflowName;

    public function __construct(string $workflowName, array $steps) {
        $this->workflowName = $workflowName;
        foreach ($steps as $step) {
            if ($step instanceof Step) {
                $this->steps[$step->getKey()] = $step;
            } else {
                throw new InvalidArgumentException("All steps must be instances of Step.");
            }
        }

        // Load current step from session or default to first step
        $cachedCurrentStep = UserAccount::getStepperWorkflowCache($this->workflowName . '_current_step');
        if ($cachedCurrentStep && isset($this->steps[$cachedCurrentStep])) {
            $this->currentStepKey = $cachedCurrentStep;
        } else {
            $this->currentStepKey = array_key_first($this->steps);
        }
    }

    public function getSteps(): array {
        return $this->steps;
    }

    public function getCurrentStep(): ?Step {
        return $this->steps[$this->currentStepKey] ?? null;
    }

    public function goToStep(string $stepKey): bool {
        if (isset($this->steps[$stepKey])) {
            $this->currentStepKey = $stepKey;
            UserAccount::setStepperWorkflowCache($this->workflowName . '_current_step', $stepKey);
            return true;
        }
        return false;
    }

    public function goToNextStep(): bool {
        $keys = array_keys($this->steps);
        $currentIndex = array_search($this->currentStepKey, $keys);
        if ($currentIndex !== false && isset($keys[$currentIndex + 1])) {
            $this->goToStep($keys[$currentIndex + 1]);
            return true;
        }
        return false;
    }

    public function goToPreviousStep(): bool {
        $keys = array_keys($this->steps);
        $currentIndex = array_search($this->currentStepKey, $keys);
        if ($currentIndex !== false && isset($keys[$currentIndex - 1])) {
            $this->goToStep($keys[$currentIndex - 1]);
            return true;
        }
        return false;
    }

    public function getProgress(): array {
        $progress = [];
        $foundCurrent = false;
        foreach ($this->steps as $key => $step) {
            $status = 'pending';
            if ($key === $this->currentStepKey) {
                $status = 'current';
                $foundCurrent = true;
            } elseif (!$foundCurrent && $step->isComplete()) {
                $status = 'completed';
            }
            $progress[] = [
                'key' => $key,
                'title' => $step->getTitle(),
                'status' => $status, // 'pending', 'current', 'completed'
                'isComplete' => $step->isComplete(),
            ];
        }
        return $progress;
    }

    public function isLastStep(): bool {
        $keys = array_keys($this->steps);
        $currentIndex = array_search($this->currentStepKey, $keys);
        return $currentIndex === (count($keys) - 1);
    }

    public function isFirstStep(): bool {
        $keys = array_keys($this->steps);
        $currentIndex = array_search($this->currentStepKey, $keys);
        return $currentIndex === 0;
    }

    public function clearWorkflow(): void {
        foreach ($this->steps as $step) {
            UserAccount::setStepperWorkflowCache($step->getKey(), null);
        }
        UserAccount::setStepperWorkflowCache($this->workflowName . '_current_step', null);
        UserAccount::clearStepperWorkflowCache();
    }
}
