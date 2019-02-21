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

{if (isset($status) == true) && ($status == 'ok')}
<h3>{l s='Payment for your order has been accepted' sprintf=$shop_name mod='firstDataIpg'}</h3>
<p>
	{l s='Order amount' mod='firstDataIpg'}: <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br /><br />{l s='Confirmation email has been sent with this information.' mod='firstDataIpg'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='firstDataIpg'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='customer support team.' mod='firstDataIpg'}</a>
</p>
{else}
<h3 style="color:#dd0000;">{l s='Payment for your order has not been accepted' sprintf=$shop_name mod='firstDataIpg'}</h3>
<p>
	{l s='Please, try to order again.' mod='firstDataIpg'}<br /><br />
	{l s='If you have questions, comments or concerns, please contact our' mod='firstDataIpg'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='customer support team.' mod='firstDataIpg'}</a>
</p>
{/if}
<hr />