<?php
class ControllerPaymentYenePay extends Controller {
	public function index() {
		$this->language->load('payment/yenepay');

		$data['text_testmode'] = $this->language->get('text_testmode');
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['testmode'] = $this->config->get('yenepay_test');

		if (!$this->config->get('yenepay_test')) {
			$data['action'] = 'http://sisay-laptop/EtPay.CheckoutProcessor/Home/Index';
		} else {
			$data['action'] = 'http://sisay-laptop/EtPay.CheckoutProcessor/Home/Index';
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
			$data['business'] = $this->config->get('yenepay_email');
			$data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

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

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

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
			$data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
			$data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
			$data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$data['country'] = $order_info['payment_iso_code_2'];
			$data['email'] = $order_info['email'];
			$data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['lc'] = $this->session->data['language'];
			$data['return'] = $this->url->link('checkout/success');
			$data['notify_url'] = $this->url->link('payment/yenepay/callback', '', 'SSL');
			$data['cancel_return'] = $this->url->link('checkout/checkout', '', 'SSL');

			if (!$this->config->get('yenepay_transaction')) {
				$data['paymentaction'] = 'authorization';
			} else {
				$data['paymentaction'] = 'sale';
			}

			$data['custom'] = $this->session->data['order_id'];

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/yenepay.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/payment/yenepay.tpl', $data);
			} else {
				return $this->load->view('default/template/payment/yenepay.tpl', $data);
			}
		}
	}

	public function callback() {
		if (isset($this->request->post['MerchantOrderId'])) {
			$order_id = $this->request->post['MerchantOrderId'];
		} else {
			$order_id = 0;
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$request = '';

			foreach ($this->request->post as $key => $value) {
				$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
			}

			if (!$this->config->get('yenepay_test')) {
				$curl = curl_init('http://sisay-laptop/ETPay.Api/api/Verify/IPN');
			} else {
				$curl = curl_init('http://sisay-laptop/ETPay.Api/api/Verify/IPN');
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

			if ($this->config->get('yenepay_debug')) {
				$this->log->write('YENEPAY :: IPN REQUEST: ' . $request);
				$this->log->write('YENEPAY :: IPN RESPONSE: ' . $response);
			}

			if ((strcmp($response, 'VERIFIED') != 0 || strcmp($response, 'UNVERIFIED') == 0) && isset($this->request->post['Status'])) {
				$order_status_id = $this->config->get('config_order_status_id');

				switch($this->request->post['Status']) {
					case 'Canceled_Reversal':
						$order_status_id = $this->config->get('yenepay_canceled_reversal_status_id');
						break;
					case 'Paid':
					case 'Delivered':
					case 'Completed':
						$receiver_match = (strtolower($this->request->post['MerchantCode']) == strtolower($this->config->get('yenepay_email')));

						$total_paid_match = ((float)$this->request->post['TotalAmmount'] == $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false));

						if ($receiver_match && $total_paid_match) {
							$order_status_id = $this->config->get('yenepay_completed_status_id');
						}
						
						if (!$receiver_match) {
							$this->log->write('YENEPAY :: RECEIVER ID MISMATCH! ' . strtolower($this->request->post['MerchantCode']));
						}
						
						if (!$total_paid_match) {
							$this->log->write('YENEPAY :: TOTAL PAID MISMATCH! ' . $this->request->post['TotalAmmount']);
						}
						break;
					case 'Canceled':
						$order_status_id = $this->config->get('yenepay_denied_status_id');
						break;
					case 'Expired':
						$order_status_id = $this->config->get('yenepay_expired_status_id');
						break;
					case 'ErrorOccured':
						$order_status_id = $this->config->get('yenepay_failed_status_id');
						break;
					case 'Waiting':
					case 'Processing':
					case 'PendingVerification':
					case 'New':
						$order_status_id = $this->config->get('yenepay_pending_status_id');
						break;
					case 'Processed':
						$order_status_id = $this->config->get('yenepay_processed_status_id');
						break;
					case 'Refunded':
						$order_status_id = $this->config->get('yenepay_refunded_status_id');
						break;
					case 'Reversed':
						$order_status_id = $this->config->get('yenepay_reversed_status_id');
						break;
					case 'Voided':
						$order_status_id = $this->config->get('yenepay_voided_status_id');
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