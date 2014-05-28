{capture name=path}{l s='Payment' mod='banglapay'}{/capture}

<br/>
<div class="box">
    {if $error_message != "" }
        <p>{$error_message}</p>
    {/if}
    <form action="{$link->getModuleLink('banglapay', 'dbblredirect', [], true)|escape:'html'}" method="post">
        Select your card type
        <br/><br/>
        <b>Dutch Bangla bank cards</b>
        <br/><br/>

        <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_NEXUS}"/>DBBL Nexus</div>
        <br/>

        <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA_DEBIT}">DBBL Visa</div>
        <br/>

        <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_DBBL_MASTER}">DBBL Master</div>
        <hr/>
        <b>Other bank cards</b>
        <br/><br/>

        <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA}">Visa</div>
        <br/>

        <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_MASTER}">Master</div>

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
</div>