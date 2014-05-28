{capture name=path}{l s='Payment' mod='banglapay'}{/capture}

<br/>
<div class="box">
    {l s='Your order on %s is complete.' sprintf=$shop_name mod='banglapay'}
    <br/><br/>
    {l s='You have chosen the DBBL payment method.' mod='banglapay'}
    <br/><br/><span class="bold">{l s='Your order will be sent very soon.' mod='banglapay'}</span>
    <br/><br/>{l s='For any questions or for further information, please contact our' mod='banglapay'} <a
            href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='banglapay'}</a>.
</div>
<br/>
<div>
    <a href="{$link->getPageLink('history.php', true)}" title="{l s='Back to orders' mod='banglapay'}" data-ajax="false"
       class="button-2 fill inline">
        <span class="wpicon wpicon-arrow-left3 small"></span>
        {l s='Back to orders' mod='paypal'}
    </a>
    <a href="{$link->getPageLink('index', true)|escape:'html':'UTF-8'}" class="button-2 fill inline">
        <span class="wpicon small"></span>{l s='Continue shopping' mod='banglapay'}
    </a>
</div>