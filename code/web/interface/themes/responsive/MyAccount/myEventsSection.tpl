{strip}
	<section class="row">
		<h2>{translate text={$sectionTitle} isPublicFacing=true}</h2>
		<div class="col-lg-12" id="myEventsPlaceholder">
			{translate text="Loading {$sectionName} Events" isPublicFacing=true}
		</div>
		<script type="text/javascript">
			{literal}
			$(document).ready(function() {
				AspenDiscovery.Account.loadEvents({/literal}{$page}, '{$eventsFilter|escape}', '{$sectionName|escape}'{literal});
			});
			{/literal}
		</script>
	</div>
{/strip}