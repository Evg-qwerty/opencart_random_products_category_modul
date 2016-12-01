<?php

class ModelExtensionModuleRandomProductsCategory extends Model {

	public function getRandomProductsCategory($product_id, $limit) {

		$this->load->model('catalog/product');

		$product_data = array();

		if($product_id){

			$category = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" .$product_id. "'");

			$category_id = $category->row['category_id'];

			// выбираем id активных товаров их категории $category_id
			$all_id_in_category = $this->db->query("SELECT p.product_id FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = '".(int)$category_id."' AND p.status = '1' AND p.date_available <= NOW() ORDER BY p.product_id ASC");

			// если лимит больше чем общее количество активного товара в категории, устанавливаем лимит равный количеству товаров в категории минус один, чтобы в выборку попали все кроме текущего
			$limit < $all_id_in_category->num_rows ?: $limit = $all_id_in_category->num_rows-1;

			// случайные товары из категории исключая текущий
			$id_rand = $this->getRandomProducts($product_id, $all_id_in_category, $limit);

			foreach ($id_rand as $result) {
				$result ? $product_data[$result] = $this->model_catalog_product->getProduct($result) : $product_data = false;
			}

		}

		return $product_data;
	}

	public function getRandomProducts($product_id, $all_id_in_category, $limit){

		$all_id = array();

		foreach ($all_id_in_category->rows as $value) {
			if ($value['product_id'] != $product_id) {
				$all_id[] = $value['product_id'];
			}
		}

		if($limit >= 1) {

			$result_all_key = array_rand($all_id, $limit);
			$result_all_id = [];

			if ($limit == 1) {
				$result_all_id[] = $all_id[$result_all_key];
			} elseif ($limit > 1) {
				foreach ($result_all_key as $key) {
					$result_all_id[] = $all_id[$key];
				}
			}

		} else {
			$result_all_id[] = false;
		}

		return $result_all_id;
	}
}
