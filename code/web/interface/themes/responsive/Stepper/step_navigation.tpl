{strip}
<div class="stepper-nav-buttons">
    {if !$stepper->isFirstStep()}
        <button type="submit" name="stepper_action" value="previous" class="btn btn-secondary">Previous</button>
    {/if}

    {if !$stepper->isLastStep()}
        <button type="submit" name="stepper_action" value="next" class="btn btn-primary">Next</button>
    {else}
        <button type="submit" name="stepper_action" value="submit" class="btn btn-success">Finish</button>
    {/if}
</div>
{/strip}