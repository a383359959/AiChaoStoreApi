<?php

/*
*	写入流水
*	注意：一定要写在钱变动后；
*/
function storePriceLog($store_id,$price,$type,$desc = '',$order_id = 0){
	$store = M('store')->field('price')->where('id = '.$store_id)->find();
	$data = array(
		'store_id' => $store_id,
		'add_time' => time(),
		'price' => $price,
		'order_id' => $order_id,
		'type' => $type,
		'surplus_price' => $store['price'],
		'desc' => $desc
	);
	M('store_price_log')->add($data);
}