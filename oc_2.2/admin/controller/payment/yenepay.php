<?php
class ControllerPaymentYenePay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/yenepay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('yenepay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_sale'] = $this->language->get('text_sale');

		$data['entry_merchant_code'] = $this->language->get('entry_merchant_code');
		$data['entry_checkout_endpoint'] = $this->language->get('entry_checkout_endpoint');
		$data['entry_IPN_verify_endpoint'] = $this->language->get('entry_IPN_verify_endpoint');
		$data['entry_PDT_verify_endpoint'] = $this->language->get('entry_PDT_verify_endpoint');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_transaction'] = $this->language->get('entry_transaction');
		$data['entry_debug'] = $this->language->get('entry_debug');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_canceled_reversal_status'] = $this->language->get('entry_canceled_reversal_status');
		$data['entry_completed_status'] = $this->language->get('entry_completed_status');
		$data['entry_denied_status'] = $this->language->get('entry_denied_status');
		$data['entry_expired_status'] = $this->language->get('entry_expired_status');
		$data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$data['entry_processed_status'] = $this->language->get('entry_processed_status');
		$data['entry_refunded_status'] = $this->language->get('entry_refunded_status');
		$data['entry_reversed_status'] = $this->language->get('entry_reversed_status');
		$data['entry_voided_status'] = $this->language->get('entry_voided_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_expires_in_days'] = $this->language->get('entry_expires_in_days');

		$data['help_test'] = $this->language->get('help_test');
		$data['help_debug'] = $this->language->get('help_debug');
		$data['help_total'] = $this->language->get('help_total');
		$data['help_merchant_code'] = $this->language->get('help_merchant_code');
		$data['help_expires_in_days'] = $this->language->get('help_expires_in_days');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_order_status'] = $this->language->get('tab_order_status');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

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
					

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/yenepay', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('payment/yenepay', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['yenepay_merchant_code'])) {
			$data['yenepay_merchant_code'] = $this->request->post['yenepay_merchant_code'];
		} else {
			$data['yenepay_merchant_code'] = $this->config->get('yenepay_merchant_code');
		}
		
		if (isset($this->request->post['expires_in_days'])) {
			$data['expires_in_days'] = $this->request->post['expires_in_days'];
		} else {
			$data['expires_in_days'] = $this->config->get('expires_in_days');
			if(!isset($data['expires_in_days'])){
				$data['expires_in_days'] = 30;
			}
		}
		
		if (isset($this->request->post['yenepay_checkout_endpoint'])) {
			$data['yenepay_checkout_endpoint'] = $this->request->post['yenepay_checkout_endpoint'];
		} else {
			$data['yenepay_checkout_endpoint'] = $this->config->get('yenepay_checkout_endpoint');
			if(!isset($data['yenepay_checkout_endpoint'])){
				$data['yenepay_checkout_endpoint'] = 'https://checkout.yenepay.com/';
			}
		}
		
		if (isset($this->request->post['yenepay_IPN_verify_endpoint'])) {
			$data['yenepay_IPN_verify_endpoint'] = $this->request->post['yenepay_IPN_verify_endpoint'];
		} else {
			$data['yenepay_IPN_verify_endpoint'] = $this->config->get('yenepay_IPN_verify_endpoint');
			if(!isset($data['yenepay_IPN_verify_endpoint'])){
				$data['yenepay_IPN_verify_endpoint'] = 'https://endpoints.yenepay.com/api/verify/ipn/';
			}
		}
		
		if (isset($this->request->post['yenepay_PDT_verify_endpoint'])) {
			$data['yenepay_PDT_verify_endpoint'] = $this->request->post['yenepay_PDT_verify_endpoint'];
		} else {
			$data['yenepay_PDT_verify_endpoint'] = $this->config->get('yenepay_PDT_verify_endpoint');
			if(!isset($data['yenepay_PDT_verify_endpoint'])){
				$data['yenepay_PDT_verify_endpoint'] = 'https://endpoints.yenepay.com/api/verify/pdt';
			}
		}

		if (isset($this->request->post['yenepay_test'])) {
			$data['yenepay_test'] = $this->request->post['yenepay_test'];
		} else {
			$data['yenepay_test'] = $this->config->get('yenepay_test');
		}

		if (isset($this->request->post['yenepay_transaction'])) {
			$data['yenepay_transaction'] = $this->request->post['yenepay_transaction'];
		} else {
			$data['yenepay_transaction'] = $this->config->get('yenepay_transaction');
		}

		if (isset($this->request->post['yenepay_debug'])) {
			$data['yenepay_debug'] = $this->request->post['yenepay_debug'];
		} else {
			$data['yenepay_debug'] = $this->config->get('yenepay_debug');
		}

		if (isset($this->request->post['yenepay_total'])) {
			$data['yenepay_total'] = $this->request->post['yenepay_total'];
		} else {
			$data['yenepay_total'] = $this->config->get('yenepay_total');
		}

		if (isset($this->request->post['yenepay_canceled_reversal_status_id'])) {
			$data['yenepay_canceled_reversal_status_id'] = $this->request->post['yenepay_canceled_reversal_status_id'];
		} else {
			$data['yenepay_canceled_reversal_status_id'] = $this->config->get('yenepay_canceled_reversal_status_id');
		}

		if (isset($this->request->post['yenepay_completed_status_id'])) {
			$data['yenepay_completed_status_id'] = $this->request->post['yenepay_completed_status_id'];
		} else {
			$data['yenepay_completed_status_id'] = $this->config->get('yenepay_completed_status_id');
		}

		if (isset($this->request->post['yenepay_denied_status_id'])) {
			$data['yenepay_denied_status_id'] = $this->request->post['yenepay_denied_status_id'];
		} else {
			$data['yenepay_denied_status_id'] = $this->config->get('yenepay_denied_status_id');
		}

		if (isset($this->request->post['yenepay_expired_status_id'])) {
			$data['yenepay_expired_status_id'] = $this->request->post['yenepay_expired_status_id'];
		} else {
			$data['yenepay_expired_status_id'] = $this->config->get('yenepay_expired_status_id');
		}

		if (isset($this->request->post['yenepay_failed_status_id'])) {
			$data['yenepay_failed_status_id'] = $this->request->post['yenepay_failed_status_id'];
		} else {
			$data['yenepay_failed_status_id'] = $this->config->get('yenepay_failed_status_id');
		}

		if (isset($this->request->post['yenepay_pending_status_id'])) {
			$data['yenepay_pending_status_id'] = $this->request->post['yenepay_pending_status_id'];
		} else {
			$data['yenepay_pending_status_id'] = $this->config->get('yenepay_pending_status_id');
		}

		if (isset($this->request->post['yenepay_processed_status_id'])) {
			$data['yenepay_processed_status_id'] = $this->request->post['yenepay_processed_status_id'];
		} else {
			$data['yenepay_processed_status_id'] = $this->config->get('yenepay_processed_status_id');
		}

		if (isset($this->request->post['yenepay_refunded_status_id'])) {
			$data['yenepay_refunded_status_id'] = $this->request->post['yenepay_refunded_status_id'];
		} else {
			$data['yenepay_refunded_status_id'] = $this->config->get('yenepay_refunded_status_id');
		}

		if (isset($this->request->post['yenepay_reversed_status_id'])) {
			$data['yenepay_reversed_status_id'] = $this->request->post['yenepay_reversed_status_id'];
		} else {
			$data['yenepay_reversed_status_id'] = $this->config->get('yenepay_reversed_status_id');
		}

		if (isset($this->request->post['yenepay_voided_status_id'])) {
			$data['yenepay_voided_status_id'] = $this->request->post['yenepay_voided_status_id'];
		} else {
			$data['yenepay_voided_status_id'] = $this->config->get('yenepay_voided_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['yenepay_geo_zone_id'])) {
			$data['yenepay_geo_zone_id'] = $this->request->post['yenepay_geo_zone_id'];
		} else {
			$data['yenepay_geo_zone_id'] = $this->config->get('yenepay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['yenepay_status'])) {
			$data['yenepay_status'] = $this->request->post['yenepay_status'];
		} else {
			$data['yenepay_status'] = $this->config->get('yenepay_status');
		}

		if (isset($this->request->post['yenepay_sort_order'])) {
			$data['yenepay_sort_order'] = $this->request->post['yenepay_sort_order'];
		} else {
			$data['yenepay_sort_order'] = $this->config->get('yenepay_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/yenepay.tpl', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/yenepay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['yenepay_merchant_code']) {
			$this->error['merchant_code'] = $this->language->get('error_merchant_code');
		}
		
		if (!$this->request->post['yenepay_checkout_endpoint']) {
			$this->error['checkout_endpointmerchant_code'] = $this->language->get('error_checkout_endpoint');
		}
		
		if (!$this->request->post['yenepay_IPN_verify_endpoint']) {
			$this->error['IPN_endpointmerchant_code'] = $this->language->get('error_IPN_verify_endpoint');
		}
		
		//if (!$this->request->post['yenepay_PDT_endpoint']) {
		//	$this->error['PDT_endpoint'] = $this->language->get('error_PDT_endpoint');
		//} else {
		//	if (!$this->request->post['yenepay_PDT_token']) {
		//	$this->error['IPN_endpointmerchant_code'] = $this->language->get('error_IPN_endpoint');
		//}
		//}		

		return !$this->error;
	}
}