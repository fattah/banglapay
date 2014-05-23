{if $error_message != "" }
    <p>{$error_message}</p>
{/if}
<form action="{$link->getModuleLink('banglapay', 'dbblredirect', [], true)|escape:'html'}" method="post">
    Select your card type
    <br/><br/>
    <b>Dutch Bangla bank cards</b>
    <br/><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_NEXUS}"/>DBBL Nexus</div><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA_DEBIT}">DBBL Visa</div><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_DBBL_MASTER}">DBBL Master</div>
    <hr/>
    <b>Other bank cards</b>
    <br/><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA}">Visa</div><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_MASTER}">Master</div><br/>
    <br/>

    <a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='banglapay'}</a>
    <input type="submit" name="paymentSubmit" value="{l s='Submit Order' mod='creditcard'}" class="exclusive_large"/>
</form>
