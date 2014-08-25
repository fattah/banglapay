{"<?xml version=\"1.0\"?>"|escape:'html'}
<br/>
{"<catalog>"|escape:'html'}
<br/>
&nbsp;&nbsp;{"<name>Transaction Logs</name>"|escape:'html'}
<br/>
&nbsp;&nbsp;{"<creationDate>2014-08-25 20:56:58</creationDate>"|escape:'html'}
<br/>
&nbsp;&nbsp;{"<copyright>Copyright (C). Nascenia Limited. All rights reserved.</copyright>"|escape:'html'}
<br/>
&nbsp;&nbsp;{"<version>1.0</version>"|escape:'html'}
<br/>
&nbsp;&nbsp;{"<description>Today transactions details.</description>"|escape:'html'}
<br/>
    {foreach from=$results item=row}
        &nbsp;&nbsp;{"<transaction id=\""|cat:$row["dbbl_transaction_id"]|cat:"\">"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"<merchant>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"<id>000599990100000</id>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"<name>Nascenia Limited</name>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"<totalAmount>"|cat:floor($row["amount"]*100)|cat:"</totalAmount>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"<accountName>Nascenia Limited</accountName>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"<accountNumber>108.110.19642</accountNumber>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"<transactionDate>"|cat:$row["created_at"]|cat:"</transactionDate>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;&nbsp;&nbsp;{"</merchant>"|escape:'html'}
        <br/>
        &nbsp;&nbsp;{"</transaction>"|escape:'html'}
        <br/>
    {/foreach}
{"</catalog>"|escape:'html'}