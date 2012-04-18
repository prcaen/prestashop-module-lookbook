<div id="content-inner">
{capture name=path}<a href="{$link->getPageLink('home.php', true)}">{l s='Accueil'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Look'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
<img src="{$base_dir}modules/lookbook/img/slides/{$look->images[0]['image']}" />
<h3>{$look->meta_title}</h3>
<p>{$look->description}</p>
<ul>
{foreach from=$look->products item=product}
	<li>
		<a href="{$link->getProductLink($product->id, $product->link_rewrite)}">
			{$product->name}
			<span>{displayPrice price=$product->price}</span>
		</a>
	</li>
{/foreach}
</ul>
<ul>
{foreach from=$look->images item=image}
	<li>
		<a href="#">
			<img src="{$base_dir}modules/lookbook/img/thumbs/{$image['image']}" />
		</a>
	</li>
{/foreach}
</ul>
</div>