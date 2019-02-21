{*
* 2007-2017 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="row">
	<div class="col-xs-12 col-md-6">
		<p class="payment_module" id="firstDataIpg_payment_button">
			{if $cart->getOrderTotal() < 1}
				<a href="">
					<img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}" alt="{l s='Pay with card' mod='firstDataIpg'}" />
					{l s='Minimum amount required in order to pay with my payment module:' mod='firstDataIpg'} {convertPrice price=1}
				</a>
			{else}
				<a id="firstdata_process_payment" class="paymentName" href="{$link->getModuleLink('firstDataIpg', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with card' mod='firstDataIpg'}">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{l s='Pay with card' mod='firstDataIpg'}" width="32" height="32" />
					{l s='Pay with your card on-line' mod='firstDataIpg'}
				</a>
                <span class="description">{l s='You will be redirected to payment gate' mod='cashondelivery'}</span>
			{/if}
		</p>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('#firstdata_process_payment').click(function(){
			$('#firstdata_payment_form').submit();
            return false;
		})
	});
</script>

<form id="firstdata_payment_form" action="{$gateway_url|escape:'htmlall':'UTF-8'}" method="post">
    {foreach from=$args key=param item=value}
        <input type="hidden" name="{$param}" value="{$value}">
    {/foreach}
</form>
