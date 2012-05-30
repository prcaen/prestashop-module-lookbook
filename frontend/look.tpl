<div id="content-inner">
	<script src="{$modules_dir}lookbook/frontend/lookbook.js"></script>
{capture name=path}<a href="{$link->getPageLink('home.php', true)}">{l s='Accueil'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Look'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	<div id="back-to-list">
		<i class="sprite-product"></i><a href="{$base_dir}lookbook.php">{l s='Retour aux looks'}</a>
	</div>
<div class="focus_look clearfix">
<img class="look_big_one" src="{$base_dir}modules/lookbook/img/slides/{$look->images[0]['image']}" />
<div class="details clearfix">
<h3>{$look->meta_title}</h3>
<ul id="look_products">
{foreach from=$look->products item=product}
	<li class="clearfix">
		<a href="{$link->getProductLink($product->id, $product->link_rewrite)}">
			<span>{$product->name}</span>
			<span>{displayPrice price=$product->price}</span>
		</a>
	</li>
{/foreach}
</ul>
<p class="description_look">{$look->description}</p>
<ul class="other_lookbook_view clearfix">
{foreach from=$look->images item=image}
	<li>
		<a href="#">
			<img src="{$base_dir}modules/lookbook/img/thumbs/{$image['image']}" />
		</a>
	</li>
{/foreach}
</ul>
<div id="social">
	<!-- Social plugins -->
	<div class="addthis_toolbox addthis_default_style">
		<a class="addthis_button_facebook_like" fb:like:layout="button_count" fb:like:send="false" fb:like:href="{$link->getProductLink($product->id)}" fb:like:width="100"></a>
		<a class="addthis_button_tweet" tw:text="" tw:count="none" tw:url="{$link->getProductLink($product->id)}" tw:via="BellamMode" tw:lang="fr" style="width: 65px"></a>
	</div>
</div><!-- END#social -->
<div class="block_other_look">
<ul>
	{if $look->prevLook}
	<li class="arrow"><a href="{$link->getPageLink('look.php')}?id_look={$look->prevLook[0].id_look}"><img src="{$img_dir}/look_arrow_left.png" /></a></li>
	{/if}
	<li class="look_other_expl">Les autres looks</li>
	{if $look->nextLook}
	<li class="arrow"><a href="{$link->getPageLink('look.php')}?id_look={$look->nextLook[0].id_look}"><img src="{$img_dir}/look_arrow_right.png" /></a></li>
	{/if}
</ul>
</div>
</div>
</div>
</div>