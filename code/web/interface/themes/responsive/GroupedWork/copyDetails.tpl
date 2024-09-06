{strip}
	{if !empty($separateItemsByEditionInWhereIsIt) && $separateItemsByEditionInWhereIsIt == 1 && !empty($summaryList)}
		<div id="itemSummaryPopup_{$itemSummaryId|escapeCSS}_{$relatedManifestation->format|escapeCSS}"
			class="itemSummaryPopup">
			<table class="table table-striped table-condensed itemSummaryTable">
				<thead>
					<tr>
						{if !empty($showEditionCovers) && $showEditionCovers == 1}<th></th>{/if}
						<th>{translate text="Edition" isPublicFacing=true}</th>
						<th>{translate text="Available Copies" isPublicFacing=true}</th>
						<th>{translate text="Location" isPublicFacing=true}</th>
						<th>{translate text="Call #" isPublicFacing=true}</th>
					</tr>
				</thead>
				<tbody>
					{foreach $summaryList item="item"}
						<tr class="sectionHeader">
							{if !empty($showEditionCovers) && $showEditionCovers == 1}
								<th class="col-tn-2 col-md-2 col-lg-2">
									<img src="{$item['editionCoverUrl']}" class="img-thumbnail {$coverStyle}">
								</th>
							{/if}
							<th>
								{if !empty($item['edition'])}
									{$item['edition']}
								{else}
									{translate text="-" isPublicFacing=true}
								{/if}
							</th>
							<th colspan="2"><th>
						</tr>
						{foreach from=$item['summary'] item="item"}
							<tr {if !empty($item.availableCopies)}class="available" {/if}>
								{if !empty($showEditionCovers) && $showEditionCovers == 1}
									<td></td>
								{/if}
								<td></td>
								{if $item.onOrderCopies > 0}
									{if !empty($showOnOrderCounts)}
										<td>{translate text="%1% on order" 1=$item.onOrderCopies isPublicFacing=true}</td>
									{else}
										<td>{translate text="Copies on order" isPublicFacing=true}</td>
									{/if}
								{else}
									<td>{translate text="%1% of %2%" 1=$item.availableCopies 2=$item.totalCopies isPublicFacing=true}{if !empty($item.availableCopies)}
										<i class="fa fa-check"></i>{/if}
									</td>
								{/if}
								<td class="notranslate">{$item.shelfLocation}</td>
								<td class="notranslate">
									{if empty($item.isEContent)}
										{$item.callNumber}
									{/if}
								</td>
							</tr>
						{/foreach}
					{/foreach}
				</tbody>
			</table>
		</div>
	{else}
		<div id="itemSummaryPopup_{$itemSummaryId|escapeCSS}_{$relatedManifestation->format|escapeCSS}"
			class="itemSummaryPopup">
			<table class="table table-striped table-condensed itemSummaryTable">
				<thead>
					<tr>
						<th>{translate text="Available Copies" isPublicFacing=true}</th>
						<th>{translate text="Location" isPublicFacing=true}</th>
						<th>{translate text="Call #" isPublicFacing=true}</th>
					</tr>
				</thead>
				<tbody>
					{assign var=numRowsShown value=0}
					{foreach from=$summary item="item"}
						<tr {if !empty($item.availableCopies)}class="available" {/if}>
							{if $item.onOrderCopies > 0}
								{if !empty($showOnOrderCounts)}
									<td>{translate text="%1% on order" 1=$item.onOrderCopies isPublicFacing=true}</td>
								{else}
									<td>{translate text="Copies on order" isPublicFacing=true}</td>
								{/if}
							{else}
								<td>{translate text="%1% of %2%" 1=$item.availableCopies 2=$item.totalCopies isPublicFacing=true}{if !empty($item.availableCopies)}
									<i class="fa fa-check"></i>{/if}
								</td>
							{/if}
							<td class="notranslate">{$item.shelfLocation}</td>
							<td class="notranslate">
								{if empty($item.isEContent)}
									{$item.callNumber}
								{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	{/if}
{/strip}