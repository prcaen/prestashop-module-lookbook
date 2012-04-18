<div id="content-inner">
{capture name=path}<a href="{$link->getPageLink('home.php', true)}">{l s='Accueil'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Lookbook'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
<div style="width: 950px; overflow-x: auto">
<ul style="width: {$lookbook->looks|@count * 232}px; height: 355px">
{foreach from=$lookbook->looks item=look}
	<li style="float: left">
		<a href="{$link->getPageLink('look.php')}?id_look={$look->id}">
			<img src="{$base_dir}modules/lookbook/img/covers/{$look->images[0]['image']}" height="355" width="232" />
		</a>
	</li>
{/foreach}
</ul>
</div>
</div>