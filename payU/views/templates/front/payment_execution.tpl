{*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{capture name=path}{l s='PayU payment.' mod='payU'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='payU'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='payU'}</p>
{else}

<form action="{$link->getModuleLink('payU', 'validation', [], true)}" method="post">
<h3>{l s='PayU payment.' mod='payU'}</h3>
<p>
	<img src="{$this_path}payU.gif" alt="{l s='Pay U' mod='payU'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by PayU.' mod='payU'}
	<br/><br />
	{l s='Here is a short summary of your order:' mod='payU'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='payU'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='payU'}
    {/if}
</p>

<p>
	{l s='PayU account information will be displayed on the next page.' mod='payU'}
	<br /><br />
	<b>{l s='Please confirm your order by clicking "Place my order."' mod='payU'}.</b>
</p>
<p class="cart_navigation">
	<input type="submit" name="submit" value="{l s='Place my order' mod='payU'}" class="exclusive_large" />
	<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='payU'}</a>
</p>
</form>
{/if}
