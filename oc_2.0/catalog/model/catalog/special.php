<?php
class ModelCatalogSpecial extends Model {
	public function getProductSpecials($product_id) {
		//$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";
		$sql = "SELECT * FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = '" . (int)$product_id . "' AND ps.date_start < NOW() AND ps.date_end > NOW() ORDER BY ps.date_start DESC LIMIT 1";

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return array(
				'product_id'    => $query->row['product_id'],
				'price'         => $query->row['price'],
				'date_start'	=> $query->row['date_start'],
				'date_end'		=> $query->row['date_end']
			);
		}
	}

}