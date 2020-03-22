<?php
class ControllerExtensionPaymentYenePay extends Controller {
	public function index() {
		$this->log->write('starting yenepay plugin...');
		$this->load->language('extension/payment/yenepay');

		$data['text_testmode'] = $this->language->get('text_testmode');
		$data['button_confirm'] = $this->language->get('button_confirm');
	 //custome error
		$data['text_currency_error'] = $this->language->get('text_currency_error');
		$data['testmode'] = $this->config->get('payment_yenepay_test');
	 //up to here

		if (!$this->config->get('payment_yenepay_test')) {
			//$data['action'] = $this->config->get('yenepay_checkout_endpoint');
			$data['action'] = 'https://www.yenepay.com/checkout/';
		} else {
			$data['action'] = 'https://test.yenepay.com/';
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		//custom
		$this->load->model('localisation/currency');
		$data['currencies'] = $this->model_localisation_currency->getCurrencies();
		if ($order_info) {
			$data['business'] = $this->config->get('payment_yenepay_merchant_code');
			$data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'); 
			$this->load->model('catalog/special');
			//$min_order_expires_in_days = 0;
			$data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						
						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}
				//custom Currency Yenepay code
				// $item_price = $this->currency->convert($product['price'], $this->session->data['currency'], 'ETB');
				// $product_specials = $this->model_catalog_special->getProductSpecials($product['product_id']);
				// if($product_specials){
				// 	$date_end = new DateTime($product_specials['date_end']);
				// 	$today = new DateTime(date('Y-m-d'));
				// 	//todo: check if php is > 5.3
				// 	$exp_days = $date_end->diff($today)->format("%a");
				// 	if($min_order_expires_in_days == 0 || $min_order_expires_in_days > $exp_days){
				// 		$min_order_expires_in_days = $exp_days;
				// 	}
				// }
				//up to here

				$data['products'][] = array(
					'name'     => htmlspecialchars($product['name']),
					'model'    => htmlspecialchars($product['model']),
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
					'quantity' => $product['quantity'],
					'option'   => $option_data,
					'weight'   => $product['weight']
				);
			}

			$data['discount_amount_cart'] = 0;

			//custom currency conversion code
			// $total_converted = $this->currency->convert($order_info['total'] - $this->cart->getSubTotal(), $this->session->data['currency'], 'ETB');
			// $total = $this->currency->format($total_converted, $order_info['currency_code'], false, false);
			//Up to here

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);
			$this->log->write('Total amount is: ' . $total);
			if ($total > 0) {
				$data['products'][] = array(
					'name'     => $this->language->get('text_total'),
					'model'    => '',
					'price'    => $total,
					'quantity' => 1,
					'option'   => array(),
					'weight'   => 0
				);
			} else {
				$data['discount_amount_cart'] -= $total;
			}

			$data['currency_code'] = $order_info['currency_code'];
			$data['first_name'] = $order_info['payment_firstname'];
			$data['last_name'] = $order_info['payment_lastname'];
			$data['address1'] = $order_info['payment_address_1'];
			$data['address2'] = $order_info['payment_address_2'];
			$data['city'] = $order_info['payment_city'];
			$data['zip'] = $order_info['payment_postcode'];
			$data['country'] = $order_info['payment_iso_code_2'];
			$data['email'] = $order_info['email'];
			$data['invoice'] = $this->session->data['order_id'] . ' - ' . $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
			$data['lc'] = $this->session->data['language'];
			$data['return'] = $this->url->link('checkout/success');
			$data['notify_url'] = $this->url->link('extension/payment/yenepay/callback', '', 'SSL');
			$data['cancel_return'] = $this->url->link('checkout/checkout', '', 'SSL');
			if (!$this->config->get('payment_yenepay_transaction')) {
				$data['paymentaction'] = 'authorization';
			} else {
				$data['paymentaction'] = 'sale';
			}

			$data['custom'] = $this->session->data['order_id'];

			return $this->load->view('extension/payment/yenepay', $data);
		}
	}

	public function callback() {
		//custom array value to Merchant..
		if (isset($this->request->post['MerchantOrderId'])) {
			$order_id = $this->request->post['MerchantOrderId'];
		} else {
			$order_id = 0;
		}
		$this->log->write('Order Id is: ' . $order_id);

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			//custom request
			$request = '';
			//$request = 'cmd=_notify-validate';

			foreach ($this->request->post as $key => $value) {
				$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
			}

			if (!$this->config->get('payment_yenepay_test')) {
				$curl = curl_init($this->config->get('yenepay_IPN_verify_endpoint'));
			} else {
				$curl = curl_init('http://testapi.yenepay.com/api/verify/ipn');
			}

			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($curl);

			if (!$response) {
				$this->log->write('YENEPAY :: CURL failed ' . curl_error($curl) . '(' . curl_errno($curl) . ')');
			}

			if ($this->config->get('payment_yenepay_debug')) {
				$this->log->write('YENEPAY :: IPN REQUEST: ' . $request);
				$this->log->write('YENEPAY :: IPN RESPONSE: ' . $response);
			}
	 //custom change == to !=
		if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0) && isset($this->request->post['payment_status'])) {
			$order_status_id = $this->config->get('config_order_status_id');

				switch($this->request->post['Status']) {
					case 'Canceled_Reversal':
						$order_status_id = $this->config->get('payment_yenepay_canceled_reversal_status_id');
						break;
					case 'Paid':
					case 'Delivered':
					case 'Completed':
						$receiver_match = (strtolower($this->request->post['MerchantCode']) == strtolower($this->config->get('payment_yenepay_merchant_code')));
						$total_paid_amount = $this->currency->convert($order_info['total'], $this->currency->getCode(), 'ETB');
						echo($total_paid_amount);
						$total_paid_match = ((float)$this->request->post['TotalAmmount'] == $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false));
                        echo($total_paid_match);
						if ($receiver_match && $total_paid_match) {
							$order_status_id = $this->config->get('payment_yenepay_completed_status_id');
						}
						
						if (!$receiver_match) {
							$this->log->write('YENEPAY :: RECEIVER ID MISMATCH! ' . strtolower($this->request->post['receiver_email']));
						}
						
						if (!$total_paid_match) {
							$this->log->write('YENEPAY :: TOTAL PAID MISMATCH! ' . $this->request->post['mc_gross']);
						}
						break;
					case 'Denied':
						$order_status_id = $this->config->get('payment_yenepay_denied_status_id');
						break;
					case 'Expired':
						$order_status_id = $this->config->get('payment_yenepay_expired_status_id');
						break;
					case 'Failed':
						$order_status_id = $this->config->get('payment_yenepay_failed_status_id');
						break;
					case 'Pending':
						$order_status_id = $this->config->get('payment_yenepay_pending_status_id');
						break;
					case 'Processed':
						$order_status_id = $this->config->get('payment_yenepay_processed_status_id');
						break;
					case 'Refunded':
						$order_status_id = $this->config->get('payment_yenepay_refunded_status_id');
						break;
					case 'Reversed':
						$order_status_id = $this->config->get('payment_yenepay_reversed_status_id');
						break;
					case 'Voided':
						$order_status_id = $this->config->get('payment_yenepay_voided_status_id');
						break;
				}

				$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
			} else {
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
			}

			curl_close($curl);
		}
	}
}