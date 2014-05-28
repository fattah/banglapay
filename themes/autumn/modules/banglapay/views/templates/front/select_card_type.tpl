{capture name=path}{l s='Payment' mod='banglapay'}{/capture}

<br/>
<div class="box banglapay">
    Select your card type
    <br/><br/>

    {if $error_message != "" }
        <p class="error">{$error_message}</p>
    {/if}
    <form action="{$link->getModuleLink('banglapay', 'dbblredirect', [], true)|escape:'html'}" method="post">
        <b>Dutch Bangla bank cards</b>
        <br/>

        <div class="card-type"><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_NEXUS}" id="card_type_1"/><label
                    for="card_type_1">DBBL Nexus</label></div>
        <br/>

        <div class="card-type"><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA_DEBIT}"
                    id="card_type_2"/><label for="card_type_2">DBBL Visa</label></div>
        <br/>

        <div class="card-type"><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_DBBL_MASTER}"
                    id="card_type_3"/><label for="card_type_3">DBBL Master</label></div>

        <hr/>
        <b>Other bank cards</b>
        <br/>

        <div class="card-type"><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA}" id="card_type_4"/><label for="card_type_4">Visa</label></div>
        <br/>

        <div class="card-type"><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_MASTER}" id="card_type_5"/><label for="card_type_5">Master</label></div>

        <div class="cart_navigation" id="cart_navigation">
            <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}"
               class="button-2 fill inline">
                <span class="wpicon wpicon-arrow-left3 small"></span>{l s='Other payment methods' mod='cashondelivery'}
            </a>
            <button type="submit" class="button-1 fill button-icon">
                <span class="wpicon wpicon-checkmark medium"></span>
                <span>{l s='Proceed for payment' mod='banglapay'}</span>
            </button>
        </div>
    </form>

    {literal}
        <script type="text/javascript">
            $(".banglapay div.card-type").click(function(){
                $(this).find("input").attr('checked', true);
            });
        </script>
    {/literal}
</div>
