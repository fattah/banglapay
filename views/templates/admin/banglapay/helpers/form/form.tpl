{extends file="helpers/form/form.tpl"}

{block name="script"}
    var ps_force_friendly_product = false;
{/block}

{block name="label"}

	{if $input['type'] == 'shop' && isset($id_wpblog_cats) && $id_wpblog_cats == 1}
        
	{else}
        {$smarty.block.parent}
	{/if}
    
{/block}

{block name="input"}
    
	{if $input.name == "link_rewrite"}
		
        <script type="text/javascript">
		{if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
			var PS_ALLOW_ACCENTED_CHARS_URL = 1;
		{else}
			var PS_ALLOW_ACCENTED_CHARS_URL = 0;
		{/if}
		</script>
	
        {$smarty.block.parent}
    
	{elseif $input.name == "checkBoxShopAsso" && isset($id_wpblog_cats) && $id_wpblog_cats == 1}
        
        <div style="visibility: hidden; opacity: 0; display: none;">
            {$smarty.block.parent}
        </div>

	{else}
        
		{$smarty.block.parent}
        
	{/if}
    
{/block}