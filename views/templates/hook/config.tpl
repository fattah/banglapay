<div class="bootstrap panel">
    <h3><i class="icon-cogs"></i> DBBL gateway configuration</h3>
    <form method="POST" action="">
        <label>DBBL lib directory:</label>

        <div>
            <input type="text" name="banglapay_lib_dir" value="{$banglapay_lib_dir}"/>
        </div>

        <label>DBBL transaction id prefix:</label>

        <div>
            <input type="text" name="banglapay_transaction_id_prefix" value="{$banglapay_transaction_id_prefix}"/>
        </div>

        <div>
            <input type="submit" name="banglapay_config" value="Save" class="button"/>
        </div>
    </form>
</div>

