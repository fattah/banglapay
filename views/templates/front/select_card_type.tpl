<form action="{$link->getModuleLink('banglapay', 'dbblredirect', [], true)|escape:'html'}" method="post">
    Select your card type
    <br/><br/>
    <div><input type="radio" name="card_type" value="dbbl-nexus"/>DBBL Nexus</div><br/>
    <div><input type="radio" name="card_type" value="dbbl-visa">DBBL Visa</div><br/>
    <div><input type="radio" name="card_type" value="dbbl-master">DBBL Master</div>
    <hr/>
    <div><input type="radio" name="card_type" value="visa">Visa</div><br/>
    <div><input type="radio" name="card_type" value="master">Master</div><br/>
    <br/>

    <a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='banglapay'}</a>
    <input type="submit" name="paymentSubmit" value="{l s='Submit Order' mod='creditcard'}" class="exclusive_large"/>
</form>
