							
{strip}                            
	<div id="walkinsContainer-{$eventInstanceId}">
		<strong>{translate text="Total Walk-ins" isAdminFacing=true}: </strong>
		<input type="text" id="totalWalkinsDisplay-{$eventInstanceId}" value="{$totalWalkins|default:0}" disabled style="width: 50px; text-align: center; display: inline-block;" class="form-control input-sm">
		<div class="btn-group">
			<button type="button" class="btn btn-sm btn-default" onclick="AspenDiscovery.Events.updateTotalWalkins({$eventInstanceId}, -1);" title="{translate text="Decrease" isAdminFacing=true}">
				<i class="fas fa-minus"></i>
			</button>
			<button type="button" class="btn btn-sm btn-default" onclick="AspenDiscovery.Events.updateTotalWalkins({$eventInstanceId}, 1);" title="{translate text="Increase" isAdminFacing=true}">
				<i class="fas fa-plus"></i>
			</button>
		</div>
	</div>
{/strip}