{debug}
{if $lookbook_page_type eq 0 || $lookbook_page_type eq 1}
  <h1>{$cms->meta_title|escape:'htmlall':'UTF-8'}</h1>
  {$cms->content}
{/if}

{if $lookbook_page_type eq 0}
  {if isset($lookbooks)}
    <ul>
    {foreach from=$lookbooks item=loobook}
      <li>
        <a href="{$loobook['link']}">
          <img src="{$loobook['img']}" alt="{$loobook['meta_title']}" title="{$loobook['meta_title']}" />
        </a>
      </li>
    {/foreach}
    <ul>
  {/if}
{elseif $lookbook_page_type eq 1}
  {if isset($looks)}
    <ul>
    {foreach from=$looks item=look}
      <li>
        <a href="{$look['link']}">
          <img src="{$look['img']}" alt="{$look['meta_title']}" title="{$look['meta_title']}" />
        </a>
      </li>
    {/foreach}
    <ul>
  {/if}
{elseif $lookbook_page_type eq 2}
  {if isset($look) && isset($lookProducts)}
    {foreach from=$lookProducts key=k item=product}
      {$product->name|escape:'htmlall':'UTF-8'}
      {if (!$PS_CATALOG_MODE AND ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order)))}
        {if isset($product->show_price) && $product->show_price && !isset($restricted_country_mode)}<span style="display: inline;">{if !$priceDisplay}{convertPrice price=$product->price}{else}{convertPrice price=$product->price_tax_exc}{/if}</span><br />{/if}
      {/if}
      <a href="#" class="lookbook_link_product" id="product_id_{$product->id}"><img src="{$link->getImageLink($product->link_rewrite, $cover[$k].id_image, 'home')}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} /></a>
    {/foreach}
  {/if}
  <div class="temp">
    
  </div>
{/if}