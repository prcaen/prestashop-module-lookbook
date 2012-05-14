<div id="content-inner">
{capture name=path}<a href="{$link->getPageLink('home.php', true)}">{l s='Accueil'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Lookbook'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	<h2>Lookbook</h2>
	<p>Découvrez nos idées de looks pour un style toujours plus affirmé et contemporain pour cette nouvelle saison. Faites défiler la barre horizontale de gauche vers la droite pour voir tous les looks.</p>
<div class="lookbook_general" style="width: 950px; overflow-x: auto">
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