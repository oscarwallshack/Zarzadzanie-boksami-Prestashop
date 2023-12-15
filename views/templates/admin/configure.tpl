{*
* 2007-2023 PrestaShop
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
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Zarzadzanie_boksami' mod='zarzadzanie_boksami'}</h3>
	<p>
		<strong>{l s='Sprawdź moje rozwiązanie zadania!' mod='zarzadzanie_boksami'}</strong><br />
		{l s='Zarądzaj boksami na swoim sklepie!' mod='zarzadzanie_boksami'}<br />
	</p>
</div>


<div class="panel">
	<span class="panel-heading-action">
		<a id="desc-product-new" class="list-toolbar-btn" href="{$adminLink}&configure=zarzadzanie_boksami&add_new=1">
			<span title="" data-toggle="tooltip" class="label-tooltip"
				data-original-title="{l s='Add new' d='Admin.Actions'}" data-html="true">
				<i class="process-icon-new "></i>
			</span>
		</a>
	</span>
</div>

{* Style są inline ze względu na problemy z funkcją addCSS dla BO *}
{foreach $boksy as $index=>$boks}
	<div class="panel ">
		<div style="
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
		width: auto;
		">
			<div style="display: flex; flex-direction: row; flex-wrap: wrap; gap: 10%; width:85%; align-items: center;">
				<div>
					{if $boks.image_path != null}
						<img src="{$boks.image_path}" width="300" height="150" style="object-fit: contain;">
					{else}
						<img src="http://localhost/ibif//modules/zarzadzanie_boksami/uploads/where_zdjecie.jpg" width="300"
							height="150" style="object-fit: contain;">
					{/if}

				</div>
				<div style="text-align: center;
				font-weight: bold; font-size: 15px;">
					<p>#{$index+1} - {$boks.name}</p>
				</div>
			</div>
			<div style="width:15%; display: flex; align-items: center;     justify-content: flex-end;">

				<div>
					<a id="" class="list-toolbar-btn" href="{$adminLink}&configure=zarzadzanie_boksami&edit={$boks.id}">
						<span title="" data-toggle="tooltip" class="label-tooltip"
							data-original-title="{l s='Edit' d='Admin.Actions'}" data-html="true">
							<i class="process-icon-edit "></i>
						</span>
					</a>
					<a id="" class="list-toolbar-btn" href="{$adminLink}&configure=zarzadzanie_boksami&delete={$boks.id}">
						<span title="" data-toggle="tooltip" class="label-tooltip"
							data-original-title="{l s='Delete' d='Admin.Actions'}" data-html="true">
							<i class="process-icon-delete "></i>
						</span>
					</a>
				</div>

			</div>
		</div>
	</div>
{/foreach}