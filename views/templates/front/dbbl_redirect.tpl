You have selected card type: {$bangla_card_type}
<br/><br/>
This page will be redirected to dbbl. Click <a href="{$redirect_url}" style="color: red;">here</a> to redirect to dbbl gateway.

<br/><br/>

Later Dbbl will the be redirected to
<b><a href="{$link->getModuleLink('banglapay', 'dbblcallback', $callback_params, true)|escape:'html'}" style="color:green;">Success</a></b> or
<b><a href="{$link->getModuleLink('banglapay', 'dbblcallback', $callback_params, true)|escape:'html'}" style="color:red;">Failure</a></b> page
<br/><br/>
<b>dbbl lib directory:</b> {$dbbl_lib->dbbl_lib_directory}
<br>
<b>dbbl transaction command:</b> {$dbbl_lib->transaction_command(10, "test", "test-merchant-id", '1')}
<br>
<b>dbbl transaction details command:</b> {$dbbl_lib->verify_transaction_command("dbbl-transaction-id")}
