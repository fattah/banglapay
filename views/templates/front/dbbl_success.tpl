<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='banglapay'}
    <br /><br />
    {l s='You have chosen the DBBL payment method.' mod='banglapay'}
    <br /><br /><span class="bold">{l s='Your order will be sent very soon.' mod='banglapay'}</span>
    <br /><br />{l s='For any questions or for further information, please contact our' mod='banglapay'} <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='banglapay'}</a>.
</p>

<ul data-role="listview" data-inset="true" id="list_myaccount">
    <li data-theme="a" data-icon="check">
        <a href="{$link->getPageLink('index', true)}" data-ajax="false">{l s='Continue shopping' mod='banglapay'}</a>
    </li>
    <li data-theme="b" data-icon="back">
        <a href="{$link->getPageLink('history.php', true, NULL, 'step=1&amp;back={$back}')}" data-ajax="false">{l s='Back to orders' mod='banglapay'}</a>
    </li>
</ul>