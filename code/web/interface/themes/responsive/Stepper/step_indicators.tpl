{strip}
<ul class="stepper-progress">
    {foreach $stepperProgress as $step}
        <li class="step-item step-{$step.status|escape}">
            <span class="step-title">{$step.title|escape}</span>
        </li>
    {/foreach}
</ul>
{/strip}