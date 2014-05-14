<?php
class Orders_model extends CI_Model {

    public function record_count($filter = array()) {
		if (!empty($filter['filter_search'])) {
			$this->db->like('order_id', $filter['filter_search']);
			$this->db->or_like('location_name', $filter['filter_search']);
			$this->db->or_like('first_name', $filter['filter_search']);
			$this->db->or_like('last_name', $filter['filter_search']);
		}

		if (isset($filter['filter_type']) AND is_numeric($filter['filter_type'])) {
			$this->db->where('order_type', $filter['filter_type']);
		}
	
		if (isset($filter['filter_status']) AND is_numeric($filter['filter_status'])) {
			$this->db->where('orders.status_id', $filter['filter_status']);
		}
	
		if (!empty($filter['filter_date'])) {
			$date = explode('-', $filter['filter_date']);
			$this->db->where('YEAR(date_added)', $date[0]);
			$this->db->where('MONTH(date_added)', $date[1]);
		}

		$this->db->from('orders');
		$this->db->join('locations', 'locations.location_id = orders.location_id', 'left');
		return $this->db->count_all_results();
    }
    
    public function customer_record_count($filter = array()) {
		if (!empty($filter['customer_id'])) {

			$this->db->where('orders.customer_id', $filter['customer_id']);

			$this->db->from('orders');
			return $this->db->count_all_results();
		}
    }
    
	public function getList($filter = array()) {
		if ($filter['page'] !== 0) {
			$filter['page'] = ($filter['page'] - 1) * $filter['limit'];
		}
			
		if ($this->db->limit($filter['limit'], $filter['page'])) {
			$this->db->select('order_id, location_name, customer_id, first_name, last_name, order_type, order_time, order_total, orders.status_id, status_name, orders.date_added, orders.date_modified');
			$this->db->from('orders');
			$this->db->join('statuses', 'statuses.status_id = orders.status_id', 'left');
			$this->db->join('locations', 'locations.location_id = orders.location_id', 'left');
			
			if (!empty($filter['sort_by']) AND !empty($filter['order_by'])) {
				$this->db->order_by($filter['sort_by'], $filter['order_by']);
			} else {
				$this->db->order_by('date_added', 'DESC');
			}
		
			if (!empty($filter['filter_search'])) {
				$this->db->like('order_id', $filter['filter_search']);
				$this->db->or_like('location_name', $filter['filter_search']);
				$this->db->or_like('first_name', $filter['filter_search']);
				$this->db->or_like('last_name', $filter['filter_search']);
			}

			if (isset($filter['filter_type']) AND is_numeric($filter['filter_type'])) {
				$this->db->where('order_type', $filter['filter_type']);
			}
	
			if (isset($filter['filter_status']) AND is_numeric($filter['filter_status'])) {
				$this->db->where('orders.status_id', $filter['filter_status']);
			}
	
			if (!empty($filter['filter_date'])) {
				$date = explode('-', $filter['filter_date']);
				$this->db->where('YEAR(date_added)', $date[0]);
				$this->db->where('MONTH(date_added)', $date[1]);
			}

			$query = $this->db->get();
			$result = array();
		
			if ($query->num_rows() > 0) {
				$result = $query->result_array();
			}
		
			return $result;
		}
	}
	
	public function getAdminOrder($order_id = FALSE) {
		if ($order_id !== FALSE) {
			$this->db->from('orders');
			$this->db->join('statuses', 'statuses.status_id = orders.status_id', 'left');
		
			$this->db->where('order_id', $order_id);			
			$query = $this->db->get();
		
			if ($query->num_rows() > 0) {
				return $query->row_array();
			}
		}
	}

	public function getOrderDates() {
		$this->db->select('date_added, MONTH(date_added) as month, YEAR(date_added) as year');
		$this->db->from('orders');
		$this->db->group_by('MONTH(date_added)');
		$this->db->group_by('YEAR(date_added)');
		$query = $this->db->get();
		$result = array();

		if ($query->num_rows() > 0) {
			$result = $query->result_array();
		}

		return $result;
	}
	
	public function getMainOrders($customer_id = FALSE) {
		if ($customer_id !== FALSE) {
			$this->db->from('orders');
			$this->db->join('statuses', 'statuses.status_id = orders.status_id', 'left');
			$this->db->join('locations', 'locations.location_id = orders.location_id', 'left');
			$this->db->order_by('order_id', 'DESC');

			$this->db->where('customer_id', $customer_id);

			$query = $this->db->get();
			$result = array();
		
			if ($query->num_rows() > 0) {
				$result = $query->result_array();
			}
		
			return $result;
		}
	}
	
	public function getMainOrder($order_id, $customer_id) {
		if (isset($order_id, $customer_id)) {
			$this->db->from('orders');
			$this->db->where('order_id', $order_id);
			$this->db->where('customer_id', $customer_id);
			
			$query = $this->db->get();
		
			if ($query->num_rows() > 0) {
				return $query->row_array();
			}
		}
		
		return FALSE;
	}

	public function getCheckoutOrder($order_id, $customer_id) {
		if (isset($order_id, $customer_id)) {
			$this->db->from('orders');
			$this->db->where('order_id', $order_id);
			$this->db->where('customer_id', $customer_id);
			$this->db->where('status_id', NULL);
			
			$query = $this->db->get();
		
			if ($query->num_rows() > 0) {
				return $query->row_array();
			}
		}
		
		return FALSE;
	}

	public function getCustomerOrders($filter = array()) {
		if ($filter['customer_id'] === '') {
			return array();
		}

		if ($filter['page'] !== 0) {
			$filter['page'] = ($filter['page'] - 1) * $filter['limit'];
		}
		
		if ($this->db->limit($filter['limit'], $filter['page'])) {
			$this->db->select('order_id, location_name, customer_id, first_name, last_name, order_type, order_time, status_name, orders.date_added, orders.date_modified');
			$this->db->from('orders');
			$this->db->join('statuses', 'statuses.status_id = orders.status_id', 'left');
			$this->db->join('locations', 'locations.location_id = orders.location_id', 'left');
			$this->db->order_by('order_id', 'DESC');
	
			$this->db->where('orders.customer_id', $filter['customer_id']);

			$query = $this->db->get();
			$result = array();
	
			if ($query->num_rows() > 0) {
				$result = $query->result_array();
			}
	
			return $result;
		}
	}

	public function getOrderMenus($order_id) {
		$this->db->select('order_menus.order_menu_id, order_menus.order_id, order_menus.menu_id, order_menus.name, order_menus.quantity, order_menus.price, order_menus.subtotal, order_menus.order_option_id, option_name, option_price');
		$this->db->from('order_menus');
		$this->db->join('order_options', 'order_options.order_option_id = order_menus.order_option_id', 'left');
		$this->db->where('order_menus.order_id', $order_id);
			
		$query = $this->db->get();
		$result = array();
	
		if ($query->num_rows() > 0) {
			$result = $query->result_array();
		}
	
		return $result;
	}

	public function getOrderTotals($order_id) {
		$this->db->from('order_totals');
		$this->db->where('order_id', $order_id);
			
		$query = $this->db->get();
		$result = array();
	
		if ($query->num_rows() > 0) {
			$result = $query->result_array();
		}
	
		return $result;
	}

	public function updateOrder($update = array()) {
		
		if (!empty($update['status_id'])) {
			$this->db->set('status_id', $update['status_id']);
		}
		
		if (!empty($update['date_modified'])) {
			$this->db->set('date_modified', $update['date_modified']);
		}
		
		if (!empty($update['order_id'])) {
			$this->db->where('order_id', $update['order_id']);
			$this->db->update('orders');
		}	

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		}
	}

	public function addOrder($order_info = array(), $cart_contents = array()) {

		$current_time = time();

		if (!empty($order_info['location_id'])) {
			$this->db->set('location_id', $order_info['location_id']);
		}

		if (!empty($order_info['customer_id'])) {
			$this->db->set('customer_id', $order_info['customer_id']);
		} else {
			$this->db->set('customer_id', '0');
		}

		if (!empty($order_info['first_name'])) {
			$this->db->set('first_name', $order_info['first_name']);
		}

		if (!empty($order_info['last_name'])) {
			$this->db->set('last_name', $order_info['last_name']);
		}

		if (!empty($order_info['email'])) {
			$this->db->set('email', $order_info['email']);
		}

		if (!empty($order_info['telephone'])) {
			$this->db->set('telephone', $order_info['telephone']);
		}

		if (!empty($order_info['order_type'])) {
			$this->db->set('order_type', $order_info['order_type']);
		}

		if (!empty($order_info['order_time'])) {
			$order_time = (strtotime($order_info['order_time']) < strtotime($current_time)) ? $current_time : $order_info['order_time'];
			$this->db->set('order_time', mdate('%H:%i', strtotime($order_time)));
			$this->db->set('date_added', mdate('%Y-%m-%d %H:%i:%s', $current_time));
			$this->db->set('date_modified', mdate('%Y-%m-%d', $current_time));
			$this->db->set('ip_address', $this->input->ip_address());
			$this->db->set('user_agent', $this->input->user_agent());
		}

		if (!empty($order_info['address_id'])) {
			$this->db->set('address_id', $order_info['address_id']);
		}

		if (!empty($order_info['payment'])) {
			$this->db->set('payment', $order_info['payment']);
		}

		if (!empty($order_info['comment'])) {
			$this->db->set('comment', $order_info['comment']);
		}

		if (isset($cart_contents['order_total'])) {
			$this->db->set('order_total', $cart_contents['order_total']);
		}

		if (isset($cart_contents['total_items'])) {
			$this->db->set('total_items', $cart_contents['total_items']);
		}

		if (!empty($order_info)) {
			$this->db->insert('orders');
		
			if ($this->db->affected_rows() > 0) {
				$order_id = $this->db->insert_id();
				
				$this->addDefaultAddress($order_info['customer_id'], $order_info['address_id']);
				$this->addOrderMenus($order_id, $cart_contents);
				
				$order_totals = array(
					'cart_total' => array('title' => 'Sub Total', 'value' => $cart_contents['cart_total']),
					'delivery' => array('title' => 'Delivery', 'value' => $cart_contents['delivery']),
					'coupon' => array('title' => 'Coupon', 'value' => $cart_contents['coupon'])
				);
			
				$this->addOrderTotals($order_id, $order_totals);
			
				if (!empty($order_info['coupon_code'])) {
					$this->addOrderCoupon($order_id, $order_info['customer_id'], $cart_contents['coupon'], $order_info['coupon_code']);
				}
			
				return $order_id;
			
			} else {
				return FALSE;
			}
		}
	}	
	
	public function completeOrder($order_id, $order_info) {
		$current_time = time();

		if ($order_id AND !empty($order_info)) {
		
			if ($order_info['payment'] === 'paypal' AND $this->config->item('paypal_order_status')) {
				$status_id = (int)$this->config->item('paypal_order_status');
				$this->db->set('status_id', $status_id);
			} else if ($order_info['payment'] === 'cod' AND $this->config->item('cod_order_status')) {
				$status_id = (int)$this->config->item('cod_order_status');
				$this->db->set('status_id', $status_id);
			} else {
				$status_id = (int)$this->config->item('order_status_new');
				$this->db->set('status_id', $status_id);
			}

			$notify = $this->_sendMail($order_id);
			$this->db->set('notify', $notify);
		
			$this->db->where('order_id', $order_id);
			$this->db->update('orders'); 
			
			$this->load->model('Statuses_model');
			$status = $this->Statuses_model->getStatus($status_id);
			$order_history = array(
				'order_id' 		=> $order_id, 
				'status_id' 	=> $status_id, 
				'notify' 		=> $notify, 
				'comment' 		=> $status['status_comment'], 
				'date_added' 	=> mdate('%Y-%m-%d %H:%i:%s', $current_time)
			);
			
			$this->Statuses_model->addStatusHistory('order', $order_history);

			$this->cart->destroy();
			$this->session->unset_userdata('order_data');
		}
	}
	
	public function addOrderMenus($order_id, $cart_contents = array()) {
		if (is_array($cart_contents) AND !empty($cart_contents)) {
			$order_option_id = 0;
			foreach ($cart_contents as $key => $item) {
				if (is_array($item) AND $key === $item['rowid']) {			
					if (!empty($item['options']['option_id'])) {
						$order_option_id = $this->addOrderMenuOption($order_id, $item['id'], $item['options']); //$options = serialize($item['options']);
					}
			
					$order_menus = array (
						'order_id' 		=> $order_id,
						'menu_id' 		=> $item['id'],
						'name' 			=> $item['name'],
						'quantity' 		=> $item['qty'],
						'price' 		=> $item['price'],
						'subtotal' 		=> $item['subtotal'],
						'order_option_id' => $order_option_id
					);
				
					$this->db->insert('order_menus', $order_menus); 
				
					$this->load->model('Menus_model');
					$menu_data = $this->Menus_model->getAdminMenu($item['id']);
				
					if ($menu_data['subtract_stock'] === '1') {
						$this->db->set('stock_qty', 'stock_qty - '. $item['qty'], FALSE);
				
						$this->db->where('menu_id', $item['id']);
						$this->db->update('menus'); 
					}
				}
			}
	
			return TRUE;
		}
	}

	public function addOrderMenuOption($order_id, $menu_id, $option) {
		if (!empty($order_id)) {
			$this->db->set('order_id', $order_id);
		}

		if (!empty($menu_id)) {
			$this->db->set('menu_id', $menu_id);
		}

		if (!empty($option)) {
			if ($menu_option) {
				$this->db->set('option_id', $option['option_id']);
				$this->db->set('option_name', $option['name']);
				$this->db->set('option_price', $option['price']);
			}
		}

		$this->db->insert('order_options');

		if ($this->db->affected_rows() > 0) {
			$order_option_id = $this->db->insert_id();
			return $order_option_id;
		}
	}

	public function addOrderTotals($order_id, $order_totals) {
		foreach ($order_totals as $key => $value) {
			if (is_numeric($value['value'])) {
				$this->db->set('order_id', $order_id);
				$this->db->set('code', $key);
				$this->db->set('title', $value['title']);

				if ($key === 'code') {
					$this->db->set('value', '-'. $value['value']);
				} else {
					$this->db->set('value', $value['value']);
				}

				$this->db->insert('order_totals'); 
			}
		}
		
		if ($this->db->affected_rows() > 0) {
			return TRUE;
		}
	}
						
	public function addOrderCoupon($order_id, $customer_id, $coupon_amt, $coupon_code) {
		if (is_numeric($coupon_amt)) {
			$this->load->model('Coupons_model');
			$coupon = $this->Coupons_model->getCouponByCode($coupon_code);
			$this->db->set('order_id', $order_id);
			$this->db->set('customer_id', $customer_id);
			$this->db->set('coupon_id', $coupon['coupon_id']);
			$this->db->set('code', $coupon['code']);
			$this->db->set('amount', '-'. $coupon_amt);
			$this->db->set('date_used', mdate('%Y-%m-%d %H:%i:%s', time()));
			$this->db->insert('coupons_history'); 
		}
	}
	
	public function addDefaultAddress($customer_id, $address_id) {
		$this->db->set('address_id', $address_id);
		$this->db->where('customer_id', $customer_id);
		$this->db->update('customers'); 
	}
						
	public function deleteOrder($order_id) {
		$delete_data = array();

		$delete_data['order_id'] = $order_id;
			
		return $this->db->delete('orders', $delete_data);
	}

	public function getMailData($order_id) {
		$data = array();
	
		$result = $this->getAdminOrder($order_id);
		if ($result) {
			$this->load->library('country');
	   		$this->load->library('currency');
	   		
			$data['order_number'] 		= $result['order_id'];
			$data['order_link'] 		= site_url('main/orders?id='. $result['order_id']);
			$data['order_type']			= ($result['order_type'] === '1') ? 'delivery' : 'collection';
			$data['order_time']			= mdate('%H:%i', strtotime($result['order_time']));
			$data['order_date']			= mdate('%d %M %y', strtotime($result['date_added']));
			$data['first_name'] 		= $result['first_name'];
			$data['last_name'] 			= $result['last_name'];
			$data['email'] 				= $result['email'];
			$data['signature'] 			= $this->config->item('site_name');

			$data['order_address'] = 'This is a collection order';
			if (!empty($result['address_id'])) {
				$this->load->model('Customers_model');
				$order_address = $this->Customers_model->getCustomerAddress($result['customer_id'], $result['address_id']);
				$data['order_address'] = $this->country->addressFormat($order_address);
			}
			
			$menus = $this->getOrderMenus($result['order_id']);
			if ($menus) {
				$data['menus'] = $options = array();
				foreach ($menus as $menu) {
					if (!empty($menu['order_option_id'])) {
						$options[] = array('option_name' => $menu['option_name'], 'option_price' => $menu['option_price']);
					}
					
					$data['menus'][] = array(
						'name' 			=> $menu['name'],
						'quantity' 		=> $menu['quantity'],
						'price'			=> $this->currency->format($menu['price']),
						'subtotal'		=> $this->currency->format($menu['subtotal']),
						'options'		=> $options
					);
				}
			}
			
			$order_totals = $this->getOrderTotals($result['order_id']);
			if ($order_totals) {
				$data['order_totals'] = array();			
				foreach ($order_totals as $total) {
					$data['order_totals'][] = array(
						'title' 		=> $total['title'],
						'value' 		=> $this->currency->format($total['value'])			
					);
				}
			}

			if (!empty($result['location_id'])) {
				$this->load->model('Locations_model');
				$location = $this->Locations_model->getLocation($result['location_id']);
				$data['location_name'] = $location['location_name'];
			}
		}
		
		return $data;
	}

	public function _sendMail($order_id) {
	   	$this->load->library('email');
		$this->load->library('mail_template'); 
		
		$notify = '0';
		
		$mail_data = $this->getMailData($order_id);
		if ($mail_data) {
			$this->email->set_protocol($this->config->item('protocol'));
			$this->email->set_mailtype($this->config->item('mailtype'));
			$this->email->set_smtp_host($this->config->item('smtp_host'));
			$this->email->set_smtp_port($this->config->item('smtp_port'));
			$this->email->set_smtp_user($this->config->item('smtp_user'));
			$this->email->set_smtp_pass($this->config->item('smtp_pass'));
			$this->email->set_newline("\r\n");
			$this->email->initialize();

			$this->email->from($this->config->item('site_email'), $this->config->item('site_name'));
			if ($this->config->item('send_order_email') === '1') {
				$this->email->cc($this->location->getEmail());
			}
		
			$message = $this->mail_template->parseTemplate('order', $mail_data);
			$this->email->to(strtolower($mail_data['email']));
			$this->email->subject($this->mail_template->getSubject());
			$this->email->message($message);
			
			if ( ! $this->email->send()) {
				$notify = '0';
			} else {
				$notify = '1';
			}			
		}
		
		return $notify;
	}
	
	public function validateOrder($order_id) {
		if (!empty($order_id)) {
			$this->db->from('orders');		
			$this->db->where('order_id', $order_id);
		
			$query = $this->db->get();

			if ($query->num_rows() > 0) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
}

/* End of file orders_model.php */
/* Location: ./application/models/orders_model.php */