{if $error_message != "" }
    <p>{$error_message}</p>
{/if}
<form action="{$link->getModuleLink('banglapay', 'dbblredirect', [], true)|escape:'html'}" method="post">
    Select your card type
    <br/><br/>
    <b>Dutch Bangla bank cards</b>
    <br/><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_NEXUS}" id="card_type_1"/><label for="card_type_1">DBBL Nexus</label></div><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA_DEBIT}" id="card_type_2"/><label for="card_type_1">DBBL Visa</label></div><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_DBBL_MASTER}" id="card_type_3"/><label for="card_type_1">DBBL MasterCard</label></div>
    <hr/>
    <b>Other bank cards</b>
    <br/><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_VISA}">Visa</div><br/>
    <div><input type="radio" name="bangla_card_type" value="{DbblLib::CARD_TYPE_MASTER}">MasterCard</div><br/>
    <br/>

    <a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='banglapay'}</a>
    <input type="submit" name="paymentSubmit" value="{l s='Submit Order' mod='creditcard'}" class="exclusive_large"/>
</form>
