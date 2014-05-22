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

    const RESULT_CODE_SUCCESS = "000";
    const RESULT_SUCCESS = "OK";

    const STATE_NOT_PAID = 'not_paid';
    const STATE_PAID = 'paid';
    const STATE_FAILED = 'failed';
    const STATE_IN_PROGRESS = 'in_progress';

    public $dbbl_lib_directory = "/home/deployer/dbbl/production";
    public $payment_url = "https://ecom1.dutchbanglabank.com/ecomm2/ClientHandler";
    public $merchant_transaction_id_prefix = "gj-";
    public $environment = "test"; # "test"/"production"

    function create_transaction($amount, $description, $mrch_transaction_id, $provider, $retry_count = 5)
    {
        $transaction_command = $this->transaction_command($amount, $description, $this->merchant_transaction_id_prefix . $mrch_transaction_id, $provider);
//logger . info "Transaction command: #{transaction_command}"
//$transaction_command = "curl --get --data command=#{CGI::escape(transaction_command)} #{DBBL_CONFIG["command_runner_url"]}";
//logger . info "Transaction command: #{transaction_command}"

        $transaction_id = null;
        $response_lines = null;
        $transaction_response = null;

        try {
            //Sample output format: "TRANSACTION_ID: gTNXnba/b9afxsnJpdoWXE5plds=\nMRCH_TRANSACTION_ID: test-merchent-trans-1";

            if ($this->environment == "production") {
                $command_output = $this->system_call($transaction_command);
            } else {
                $sample_command_output1 = "TRANSACTION_ID: URkBZse8v3byMtYL6a15GtJAY9U=\nMRCH_TRANSACTION_ID: test-merchent-trans-1"; #Successful transaction
                $sample_command_output2 = "TRANSACTION_ID: gTNXnba/b9afxsnJpdoWXE5plds=\nMRCH_TRANSACTION_ID: test-merchent-trans-1"; #Failed transaction
                $command_output = $sample_command_output2;
            }
            #TODO: Comment out above line and uncomment the system_call

            $transaction_response = $this->parse_transaction_id_response($command_output);
            #TODO: write command and output to log file
        } catch (Exception $ex) {
            //logger . error ex . message
            //logger . error ex . backtrace . join("\n")
        }

        if ($transaction_response == null || $transaction_response == "")
            $transaction_response = array('transaction_id' => "invalid", 'details' => "Failed to get transaction id according to dbbl spec");

        $transaction_response['payment_url'] = $this->payment_url . "?card_type=$provider&trans_id=" . $transaction_response["transaction_id"];

        return $transaction_response;
    }

    function verify_dbbl_transaction($transaction_id, $mrch_transaction_id = "")
    {
        if ($transaction_id == null)
            $transaction_id = "";

        $transaction_command = $this->verify_transaction_command($transaction_id);
        //$transaction_command = "curl --get --data command=#{CGI::escape(transaction_command)} #{DBBL_CONFIG["command_runner_url"]}";

        if ($this->environment == "production") {
            $command_response = system_call($transaction_command);
        } else {
            $sample_command_output1 = "RESULT: OK\nRESULT_PS: FINISHED\nRESULT_CODE: 000\n3DSECURE: ATTEMPTED\nRRN: 413522233208\nAPPROVAL_CODE: 180180\nCARD_NUMBER: 1**************7701\nMRCH_TRANSACTION_ID: 17895";
            $sample_command_output2 = "RESULT: TIMEOUT\nRESULT_PS: CANCELLED\nMRCH_TRANSACTION_ID: test-merchent-trans-1";
            $command_response = $sample_command_output1;
        }

        //logger.info "Transaction verification command output: #{output_lines.inspect}"
        $response_hash = $this->parse_verification_response($command_response);
        #TODO: write command and output to log

        return array('code' => $response_hash['RESULT_CODE'], 'details' => $command_response, 'response_hash' => $response_hash);
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
        return "java -jar \"" . $this->dbbl_lib_directory . "/ecomm_merchant.jar\" " . $this->dbbl_lib_directory . "/merchant.properties\" -v " .
        $amount_in_paisa . " 050 $ip_address $provider^\"$description\" --mrch_transaction_id='$mrch_transaction_id'" . " 2>&1";
    }

    function is_payment_complete($transaction_details)
    {
        if ($transaction_details['code'] == '000')
            return true;
        return false;
    }

    function parse_verification_response($output_lines)
    {
        if (output_lines == null)
            return array('result' => "FAILED", 'code' => null);

        $output_lines = explode("\n", $output_lines);
        $output = array();
        foreach ($output_lines as $output_line) {
            $key_value = explode(':', $output_line);
            $output[$key_value[0]] = $key_value[1];
        }

        return $output;
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
