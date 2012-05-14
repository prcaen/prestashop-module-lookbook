<div id="content-inner">
{capture name=path}<a href="{$link->getPageLink('home.php', true)}">{l s='Accueil'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Look'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	<div id="back-to-list">
		<i class="sprite-product"></i><a href="">{l s='Retour aux looks'}</a>
	</div>
<div class="focus_look clearfix">
<img class="look_big_one" src="{$base_dir}modules/lookbook/img/slides/{$look->images[0]['image']}" />
<div class="details clearfix">
<h3>{$look->meta_title}</h3>
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
	<ul id="usefull_link_block" class="clearfix">
		<!-- usefull links-->
		<li><a href="https://www.facebook.com/pages/Bellam-Mode/265280330176743"><i class="sprite-product fb"></i></a></li>
		<li><a href="https://twitter.com/#!/BellamMode"><i class="sprite-product tw"></i></a></li>
		{if $HOOK_EXTRA_LEFT}{$HOOK_EXTRA_LEFT}{/if}
		<li><a href="javascript:print();"><i class="sprite-product print"></i></a></li>
	</ul>
</div><!-- END#social -->
<div class="block_other_look">
<ul>
<li class="arrow"><a href=""><img src="{$img_dir}/look_arrow_left.png" /></a></li>
<li class="look_other_expl">Les autres looks</li>
<li class="arrow"><a href=""><img src="{$img_dir}/look_arrow_right.png" /></a></li>
</ul>
</div>
</div>
</div>
</div>