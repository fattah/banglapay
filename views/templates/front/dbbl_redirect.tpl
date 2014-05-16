You have selected card type: {$bangla_card_type}
<br/><br/>
This page will be redirected to dbbl. Click <a href="{$redirect_url}" style="color: red;">here</a> to redirect to dbbl gateway.

<br/><br/>

Later Dbbl will the be redirected to
<a href="{$link->getModuleLink('banglapay', 'success', [], true)|escape:'html'}">Success</a> or
<a href="{$link->getModuleLink('banglapay', 'failure', [], true)|escape:'html'}">Failure</a> page
<br/><br/>
<b>dbbl lib directory:</b> {$dbbl_lib->dbbl_lib_directory}
<br>
<b>dbbl transaction command:</b> {$dbbl_lib->transaction_command(10, "test", "test-merchant-id", '1')}
<br>
<b>dbbl transaction details command:</b> {$dbbl_lib->verify_transaction_command("dbbl-transaction-id")}
<br>
