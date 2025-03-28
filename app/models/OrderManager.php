<?php

namespace app\models;

class OrderManager
{
	public function returnOrder(string $id): array|false
	{
		return Db::queryOne('
			SELECT `createdAt`,
				   `order_title`,
				   `order_price`,
				   `currency`,
				   `state`,
				   `item_title`,
				   `item_price`
			FROM `order`
			JOIN `order_item` on `order`.`order_id` = ?
			JOIN `item` on `order_item`.`item_id`
			where `order_item`.`item_id` = `item`.`item_id`
		', array($id));
	}
}