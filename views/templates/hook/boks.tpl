{foreach $boksy as $boks}
    {if (
(isset($cms) && $page_type == "static" && $boks.static_page_id == $cms.id) ||
(isset($category) && $page_type == "category" && $boks.category_page_id == $category.id) ||
($page_type == "homePage" && $boks.category_page_id == 2 && $page.page_name == "index") ||
(isset($product) && $page_type == "product" && $boks.product_page_id == $product.id)
)}
    {if $boks.link != null}
        <a href="{$boks.link}">
        {/if}
        <div class="ibif_box text-sm-center text-xs-center"
            style="background-image: url('{$boks.image_path}'); background-size: cover; background-position: center;">

            <h3 class="ibif_box_title text-secondary">{$boks.title}</h3>

        </div>
        {if $boks.link != null}

        </a>
    {/if}
{/if}
{/foreach}