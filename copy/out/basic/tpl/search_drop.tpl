[{if $oView->getArticleList() }]
	<table cellspacing="0" cellpadding="0" class="search_table">
		[{if $pageNavigation->iArtCnt>0}]
			<tr style="height: 24px;">
				<td colspan="4" style="text-align: left"><a id="live_search_drop" href="[{ $oViewConf->getBaseDir() }]index.php?cl=search&searchparam=">Rasta produktų: [{$pageNavigation->iArtCnt}]</a></td>
			</tr>
		[{/if}]
		[{foreach from=$oView->getArticleList() name=search item=product}]
			<tr>
				<td><a href="[{$product->getLink()}]"><img title="" alt="" src="[{$product->getIconUrl()}]"></a></td>
				<td class="url"><a class="url" href="[{$product->getLink()}]">[{$product->oxarticles__oxartnum->value}]</a></td>
				<td class="url"><a class="url" href="[{$product->getLink()}]">[{$product->oxarticles__oxtitle->value}]</a></td>
				<td class="price">[{ $product->getFPrice()}] [{ $currency->sign}]</td>
			</tr>
		[{/foreach}]
	</table>
[{else}]
	<table cellspacing="0" cellpadding="0" class="search_table_empty">
		<tr><td colspan="3"><strong>[{ oxmultilang ident="SEARCH_NO_PRODUCT" }]</strong></td></tr>
	</table>
[{/if}]