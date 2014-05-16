<?php

/**
 * Created by PhpStorm.
 * User: fattah
 * Date: 5/16/14
 * Time: 11:40 AM
 */
class DbblLib
{
    #Card_type=1 DBBL NEXUS
    #Card_type=2 dbbl MasterDebit
    #Card_type=3 dbbl VisaDebit
    #Card_type=4 VISA
    #Card_type=5 MasterCard
    const CARD_TYPE_NEXUS = 1;
    const CARD_TYPE_DBBL_MASTER = 2;
    const CARD_TYPE_VISA_DEBIT = 3;
    const CARD_TYPE_VISA = 4;
    const CARD_TYPE_MASTER = 5;

    public $dbbl_lib_directory = "/home/deployer/dbbl/production";
    public $payment_url = "https://ecom1.dutchbanglabank.com/ecomm2/ClientHandler";

    function create_transaction($amount, $description, $mrch_transaction_id, $provider, $retry_count = 5)
    {
        $transaction_command = $this->transaction_command($amount, $description, $mrch_transaction_id, $provider);
//logger . info "Transaction command: #{transaction_command}"
//$transaction_command = "curl --get --data command=#{CGI::escape(transaction_command)} #{DBBL_CONFIG["command_runner_url"]}";
//logger . info "Transaction command: #{transaction_command}"

        $transaction_id = null;
        $response_lines = null;
        $transaction_response = null;

        try {
            //$command_output = $this->system_call($transaction_command);
            //Sample output format: "TRANSACTION_ID: gTNXnba/b9afxsnJpdoWXE5plds=\nMRCH_TRANSACTION_ID: test-merchent-trans-1";
            $command_output = "TRANSACTION_ID: gTNXnba/b9afxsnJpdoWXE5plds=\nMRCH_TRANSACTION_ID: test-merchent-trans-1";
            $transaction_response = $this->parse_transaction_id_response($command_output);
        } catch (Exception $ex) {
            //logger . error ex . message
            //logger . error ex . backtrace . join("\n")
        }

        if ($transaction_response == null || $transaction_response == "")
            $transaction_response = array('transaction_id' => "invalid", 'details' => "Failed to get transaction id according to dbbl spec");

        $transaction_response['payment_url'] = $this->payment_url . "?card_type=$provider&trans_id=" . $transaction_response["transaction_id"];

        return $transaction_response;
    }

    function parse_transaction_id_response($command_response)
    {
        $response_lines = explode("\n", $command_response);

        if (strpos($response_lines[0], "TRANSACTION_ID") === false) {
            return null;
        }

        $transaction_id = $response_lines[0];
        $transaction_id = substr($transaction_id, 16, 28);
        return array('transaction_id' => $transaction_id, 'details' => $response_lines);
    }

    function transaction_command($amount, $description, $mrch_transaction_id, $provider)
    {
        $ip_address = "106.186.115.31"; //DBBL_CONFIG["ip_address"], read from configuration
        $amount_in_paisa = $amount * 100;
        //return 'test1';
        return "java -jar \"" . $this->dbbl_lib_directory . "/ecomm_merchant.jar\" " . $this->dbbl_lib_directory. "/merchant.properties\" -v " .
            $amount_in_paisa . " 050 $ip_address $provider^\"$description\" --mrch_transaction_id='$mrch_transaction_id'" . " 2>&1";
    }

    function verify_transaction_command($transaction_id, $mrch_transaction_id = "")
    {
//        return 'test2';
        $ip_address = "106.186.115.31"; //DBBL_CONFIG["ip_address"], read from configuration
        return "java -jar \"" . $this->dbbl_lib_directory . "/ecomm_merchant.jar\" " . "\"" . $this->dbbl_lib_directory . "/merchant.properties\" -c " .
        $transaction_id . " $ip_address -mrch_transaction_id" . " 2>&1";
    }

    function system_call($command)
    {
        $time_format = "%Y-%m-%d %H:%M:%S %z";
        //$command = $_GET['command'];
        $output = shell_exec($command);

        $log_message = "\n" . strftime($time_format, time()) . ": " . $command;
        $log_message .= "\nOutput: \n" . "$output";

        file_put_contents(_PS_ROOT_DIR_ . "/log/dbbl-commands.log", $log_message, FILE_APPEND);

        return $output;
    }
}
