<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Example usage of Authorize.net's
 * Advanced Integration Method (AIM)
 */
class Authorize extends MX_Controller {	
	  function Payment() {
      parent::__construct();
      $this->load->helper('template_inheritance');
      $this->load->library('session');
      $this->load->library('encrypt');
      $this->load->helper('form');

  	}

	// Example auth & capture of a credit card
	public function index(){
		$account_data = $this->session->userdata("accountinfo");
	        $data["accountid"] = $account_data["id"];
      		$data["accountid"] = $account_data["id"];
      		$data["page_title"] = "Account Recharge";



		$this->load->view("user_authorize",$data);		
	}
	public function pay()
	{
		$input_data = json_decode(trim(file_get_contents('php://input')), true);
		
		$this->db->trans_start();
		// Authorize.net lib
		$this->load->library('authorize_net');
		$amount = $input_data['amount']; //$this->input->post("amount",true);
		$cc = $input_data['cc']; //$this->input->post("cc", true);
		$sc = $input_data['sc']; //$this->input->post("sc", true);
		$xm = $input_data['xm']; //$this->input->post("ex", true);
		$xy = $input_data['xy'];
		$auth_net = array(
			'x_card_num'			=> $cc, // Visa
			'x_exp_date'			=> $xm."/".$xy,
			'x_card_code'			=> $sc,
			'x_description'			=> 'A test transaction',
			'x_amount'			=> $amount,
			'x_first_name'			=> 'John',
			'x_last_name'			=> 'Doe',
			'x_address'				=> '123 Green St.',
			'x_city'				=> 'Lexington',
			'x_state'				=> 'KY',
			'x_zip'					=> '40502',
			'x_country'				=> 'US',
			'x_phone'				=> '555-123-4567',
			'x_email'				=> 'test@example.com',
			'x_customer_ip'			=> $this->input->ip_address(),
			);
		$this->authorize_net->setData($auth_net);
		$res = $this->authorize_net->authorizeAndCapture();
		// Try to AUTH_CAPTURE
		$fp=fopen("/var/log/astpp/astpp_payment.log","w+");
		if( $res )
		{
			
        	$date = date("Y-m-d H:i:s");
            fwrite($fp,"====================".$date."===============================\n");
            foreach($input_data as $key => $value){
                    fwrite($fp,$key.":::>".$value."\n");
            }
		fwrite($fp, $this->authorize_net->getTransactionId()." : ".$this->authorize_net->getApprovalCode());
		fwrite($fp, json_encode($input_data));

	    $response_arr = $input_data;
            $account_data = $this->db_model->getSelect("*", "accounts", array("id" => $response_arr["item_number"]));
            $account_data = $account_data->result_array();
            $account_data = $account_data[0];

            $currency = $this->db_model->getSelect('currency,currencyrate', 'currency', array("id"=>$account_data["currency_id"]));
            $currency = $currency->result_array();
            $currency =$currency[0];

	    $response_arr['txn_id'] = $this->authorize_net->getTransactionId();
            $response_arr['appr_code'] = $this->authorize_net->getApprovalCode();
	    $date = date('Y-m-d H:i:s');
	    $paypalfee = .30;
            $payment_trans_array = array("accountid"=>$response_arr["item_number"],"amount"=>$amount,
                "tax"=>"1","payment_method"=>"Authorize","actual_amount"=>$amount,"paypal_fee"=>$paypalfee,
                "user_currency"=>$currency["currency"],"currency_rate"=>$currency["currencyrate"],"transaction_details"=>json_encode($response_arr),"date"=>$date);
            $this->db->insert('payment_transaction',$payment_trans_array);
            $paymentid = $this->db->insert_id();

            $payment_arr = array("accountid"=> $response_arr["item_number"],"payment_mode"=>"1","credit"=>$amount,
                    "type"=>"Authorize","payment_by"=>$response_arr["item_number"],"notes"=>"authorize.net|".$date."|".$response_arr["txn_id"]."|".$response_arr['appr_code'],"paypalid"=>$paymentid, "reference"=>$response_arr["txn_id"]."|".$response_arr['appr_code'],
                    "txn_id"=>$response_arr["txn_id"],'payment_date'=>gmdate('Y-m-d H:i:s',strtotime($date)));
            $this->db->insert('payments', $payment_arr);


			$this->db_model->update_balance($amount,$response_arr['item_number'],"credit");
			$this->db->trans_complete();
			$data['response']['code'] = '200';
			$data['response']['message'] = $amount;
			//$this->response(json_encode($data), 200);
			$this->output->set_status_header(200);
			$this->output
    			  ->set_content_type('application/json')
    			  ->set_output(json_encode($data));
			//redirect(base_url() . 'user/authorize/done');
			//$this->load->view("user_authorize_success", $this-authorize_net);
			//echo '<h2>Success!</h2>';
			//echo '<p>Transaction ID: ' . $this->authorize_net->getTransactionId() . '</p>';
			//echo '<p>Approval Code: ' . $this->authorize_net->getApprovalCode() . '</p>';
		}
		else
		{
			//$this->load->view("user_authorize_fail", $this-authorize_net);
			//echo '<h2>Fail!</h2>';
			// Get error
			//echo '<p>' . $this->authorize_net->getError() . '</p>';
			// Show debug data
			//fwrite($fp, $this->authorize_net->debug());
			
			$data['response']['code']='400';
			$data['response']['message'] = $this->authorize_net->getError();
			//$this->response(json_encode($data), 400);
                        $this->output->set_status_header(400);
                        $this->output
                          ->set_content_type('application/json')
                          ->set_output(json_encode($data));
		}
	}
	public function done(){
		$this->load->view("user_authorize_success");
	}
	
}

/* EOF */
