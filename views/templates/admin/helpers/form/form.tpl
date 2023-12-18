<div class="panel">
	<h2>{l s='Dodaj nowy boks' mod='zarzadzanie_boksami'}</h2>
	<form action="" method="post" enctype="multipart/form-data">
		{if isset($boks) && $boks.id != null}
			<input type="hidden" name="boks_id" value="{$boks.id}" />
		{/if}
		{if isset($boks) && $boks.image_path != null}
			<br>
			<img name="imageFromDb" src="{$boks.image_path}" width="300" height="auto">
			<input type="hidden" name="imageFromDb" value="{$boks.image_path}" />
		{/if}

		<div class="custom-file">
			<label for="boks_image">{l s='Wybierz plik' mod='zarzadzanie_boksami'}</label>
			<input type="file" class="custom-file-input" name="boks_image" id="boks_image" />
		</div>

		<div class="form-group form-group-lg">
			<label class="form-control-label" for="input2">{l s='Nazwa boksa' mod='zarzadzanie_boksami'}</label>
			<input type="text" class="form-control form-control-lg"
				placeholder="{l s='Podaj nazwę boksa' mod='zarzadzanie_boksami'}" name="boks_name" id="boks_name"
				value="{if isset($boks) && $boks.name != null}{$boks.name}{/if}" />
			<label class="form-control-label" for="input2">{l s='Tytuł boksa' mod='zarzadzanie_boksami'}</label>
			<input type="text" class="form-control form-control-lg"
				placeholder="{l s='Podaj tytuł boksa' mod='zarzadzanie_boksami'}" name="boks_title" id="boks_title"
				value="{if isset($boks) && $boks.title != null}{$boks.title}{/if}" />
			<label class="form-control-label" for="input3">{l s='Link do strony' mod='zarzadzanie_boksami'}</label>
			<input type="text" class="form-control form-control-lg"
				placeholder="{l s='Podaj link do którego ma prowadzić boks' mod='zarzadzanie_boksami'}" name="boks_link"
				id="boks_link" value="{if isset($boks) && $boks.link != null}{$boks.link}{/if}" />
		</div>

		<div class="panel">
			<h3>Zaznacz gdzie ma się wyświetlać boks</h3>
			<div class="panel">
				<h4>Strony statyczne:</h4>
				<div class="form-select">
					<select class="form-control custom-select" name="boks_static_page">
						<option value="">-</option>
						{foreach $staticPages as $staticPage}
							<option value="{$staticPage.id_cms}"
								{if isset($boks) && $staticPage.id_cms == $boks.static_page_id}selected{/if}>
								{$staticPage.meta_title}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="panel">
				<h4>Produkty:</h4>
				<div class="form-select">
					<select class="form-control custom-select" name="boks_product_page">
						<option value="">-</option>
						{foreach $products as $product}
							<option value="{$product.id_product}"
								{if isset($boks) && $product.id_product == $boks.product_page_id}selected{/if}>
								{$product.name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="panel">
				<h4>Kategorie:</h4>
				{$categoryTree}
			</div>
			<button type="submit" name="submit" class="btn btn-primary">Zapisz</button>
	</form>
</div>