{strip}
<div class="stepper-component">
    <div class="stepper-indicators">
        {include file="Stepper/step_indicators.tpl"}
    </div>
    <div class="stepper-content">
        {include file=$currentStep->getTemplatePath()}
    </div>
    <div class="stepper-navigation">
        {include file="Stepper/step_navigation.tpl"}
    </div>
</div>
{/strip}