<?php
class ControllerExtensionPaymentYenePay extends Controller {
	private $error = array();

	public function index() {
		$this->log->write('starting yenepay admin...');
		$this->load->language('extension/payment/yenepay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_yenepay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		// if (isset($this->error['email'])) {
		// 	$data['error_email'] = $this->error['email'];
		// } else {
		// 	$data['error_email'] = '';
		// }
		//custom input
		if (isset($this->error['merchant_code'])) {
			$data['error_merchant_code'] = $this->error['merchant_code'];
		} else {
			$data['error_merchant_code'] = '';
		}
		
		if (isset($this->error['checkout_endpoint'])) {
			$data['error_checkout_endpoint'] = $this->error['checkout_endpoint'];
		} else {
			$data['error_checkout_endpoint'] = '';
		}
		
		if (isset($this->error['IPN_endpoint'])) {
			$data['error_IPN_endpoint'] = $this->error['IPN_endpoint'];
		} else {
			$data['error_IPN_endpoint'] = '';
		}
		//up to here
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/yenepay', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/yenepay', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		//custom yenepay inputs check
		if (isset($this->request->post['payment_yenepay_merchant_code'])) {
			$data['payment_yenepay_merchant_code'] = $this->request->post['payment_yenepay_merchant_code'];
		} else {
			$data['payment_yenepay_merchant_code'] = $this->config->get('payment_yenepay_merchant_code');
		}
		if (isset($this->request->post['payment_yenepay_chechkout_endpoint'])) {
			$data['payment_yenepay_chechkout_endpoint'] = $this->request->post['payment_yenepay_chechkout_endpoint'];
		} else {
			$data['payment_yenepay_chechkout_endpoint'] = $this->config->get('payment_yenepay_chechkout_endpoint');
			if(!isset($data['payment_yenepay_chechkout_endpoint'])){
				$data['payment_yenepay_chechkout_endpoint'] = 'https://checkout.yenepay.com/';
			}
		}
		
		if (isset($this->request->post['payment_yenepay_IPN_verify_endpoint'])) {
			$data['payment_yenepay_IPN_verify_endpoint'] = $this->request->post['payment_yenepay_IPN_verify_endpoint'];
		} else {
			$data['payment_yenepay_IPN_verify_endpoint'] = $this->config->get('payment_yenepay_IPN_verify_endpoint');
			if(!isset($data['payment_yenepay_IPN_verify_endpoint'])){
				$data['payment_yenepay_IPN_verify_endpoint'] = 'https://endpoints.yenepay.com/api/verify/ipn/';
			}
		}
		
		if (isset($this->request->post['payment_yenepay_PDT_verify_endpoint'])) {
			$data['payment_yenepay_PDT_verify_endpoint'] = $this->request->post['payment_yenepay_PDT_verify_endpoint'];
		} else {
			$data['payment_yenepay_PDT_verify_endpoint'] = $this->config->get('payment_yenepay_PDT_verify_endpoint');
			if(!isset($data['payment_yenepay_PDT_verify_endpoint'])){
				$data['payment_yenepay_PDT_verify_endpoint'] = 'https://endpoints.yenepay.com/api/verify/pdt';
			}
		}

		//up to here

		if (isset($this->request->post['payment_yenepay_email'])) {
			$data['payment_yenepay_email'] = $this->request->post['payment_yenepay_email'];
		} else {
			$data['payment_yenepay_email'] = $this->config->get('payment_yenepay_email');
		}

		if (isset($this->request->post['payment_yenepay_test'])) {
			$data['payment_yenepay_test'] = $this->request->post['payment_yenepay_test'];
		} else {
			$data['payment_yenepay_test'] = $this->config->get('payment_yenepay_test');
		}

		if (isset($this->request->post['payment_yenepay_transaction'])) {
			$data['payment_yenepay_transaction'] = $this->request->post['payment_yenepay_transaction'];
		} else {
			$data['payment_yenepay_transaction'] = $this->config->get('payment_yenepay_transaction');
		}

		if (isset($this->request->post['payment_yenepay_debug'])) {
			$data['payment_yenepay_debug'] = $this->request->post['payment_yenepay_debug'];
		} else {
			$data['payment_yenepay_debug'] = $this->config->get('payment_yenepay_debug');
		}

		if (isset($this->request->post['payment_yenepay_total'])) {
			$data['payment_yenepay_total'] = $this->request->post['payment_yenepay_total'];
		} else {
			$data['payment_yenepay_total'] = $this->config->get('payment_yenepay_total');
		}

		if (isset($this->request->post['payment_yenepay_canceled_reversal_status_id'])) {
			$data['payment_yenepay_canceled_reversal_status_id'] = $this->request->post['payment_yenepay_canceled_reversal_status_id'];
		} else {
			$data['payment_yenepay_canceled_reversal_status_id'] = $this->config->get('payment_yenepay_canceled_reversal_status_id');
		}

		if (isset($this->request->post['payment_yenepay_completed_status_id'])) {
			$data['payment_yenepay_completed_status_id'] = $this->request->post['payment_yenepay_completed_status_id'];
		} else {
			$data['payment_yenepay_completed_status_id'] = $this->config->get('payment_yenepay_completed_status_id');
		}

		if (isset($this->request->post['payment_yenepay_denied_status_id'])) {
			$data['payment_yenepay_denied_status_id'] = $this->request->post['payment_yenepay_denied_status_id'];
		} else {
			$data['payment_yenepay_denied_status_id'] = $this->config->get('payment_yenepay_denied_status_id');
		}

		if (isset($this->request->post['payment_yenepay_expired_status_id'])) {
			$data['payment_yenepay_expired_status_id'] = $this->request->post['payment_yenepay_expired_status_id'];
		} else {
			$data['payment_yenepay_expired_status_id'] = $this->config->get('payment_yenepay_expired_status_id');
		}

		if (isset($this->request->post['payment_yenepay_failed_status_id'])) {
			$data['payment_yenepay_failed_status_id'] = $this->request->post['payment_yenepay_failed_status_id'];
		} else {
			$data['payment_yenepay_failed_status_id'] = $this->config->get('payment_yenepay_failed_status_id');
		}

		if (isset($this->request->post['payment_yenepay_pending_status_id'])) {
			$data['payment_yenepay_pending_status_id'] = $this->request->post['payment_yenepay_pending_status_id'];
		} else {
			$data['payment_yenepay_pending_status_id'] = $this->config->get('payment_yenepay_pending_status_id');
		}

		if (isset($this->request->post['payment_yenepay_processed_status_id'])) {
			$data['payment_yenepay_processed_status_id'] = $this->request->post['payment_yenepay_processed_status_id'];
		} else {
			$data['payment_yenepay_processed_status_id'] = $this->config->get('payment_yenepay_processed_status_id');
		}

		if (isset($this->request->post['payment_yenepay_refunded_status_id'])) {
			$data['payment_yenepay_refunded_status_id'] = $this->request->post['payment_yenepay_refunded_status_id'];
		} else {
			$data['payment_yenepay_refunded_status_id'] = $this->config->get('payment_yenepay_refunded_status_id');
		}

		if (isset($this->request->post['payment_yenepay_reversed_status_id'])) {
			$data['payment_yenepay_reversed_status_id'] = $this->request->post['payment_yenepay_reversed_status_id'];
		} else {
			$data['payment_yenepay_reversed_status_id'] = $this->config->get('payment_yenepay_reversed_status_id');
		}

		if (isset($this->request->post['payment_yenepay_voided_status_id'])) {
			$data['payment_yenepay_voided_status_id'] = $this->request->post['payment_yenepay_voided_status_id'];
		} else {
			$data['payment_yenepay_voided_status_id'] = $this->config->get('payment_yenepay_voided_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_yenepay_geo_zone_id'])) {
			$data['payment_yenepay_geo_zone_id'] = $this->request->post['payment_yenepay_geo_zone_id'];
		} else {
			$data['payment_yenepay_geo_zone_id'] = $this->config->get('payment_yenepay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_yenepay_status'])) {
			$data['payment_yenepay_status'] = $this->request->post['payment_yenepay_status'];
		} else {
			$data['payment_yenepay_status'] = $this->config->get('payment_yenepay_status');
		}

		if (isset($this->request->post['payment_yenepay_sort_order'])) {
			$data['payment_yenepay_sort_order'] = $this->request->post['payment_yenepay_sort_order'];
		} else {
			$data['payment_yenepay_sort_order'] = $this->config->get('payment_yenepay_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/yenepay', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/yenepay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_yenepay_merchant_code']) {
			$this->error['merchant_code'] = $this->language->get('error_merchant_code');
		}
	

		return !$this->error;
	}
}