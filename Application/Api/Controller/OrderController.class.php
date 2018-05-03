<?php

namespace Api\Controller;

use Think\Controller;

class OrderController extends Controller{

	public function lists(){
		$limit = (($_REQUEST['page'] - 1) * 3).',3';
		switch($_REQUEST['status']){
			case 0:
				$order = M('order')->where('pay_status = 1 and (`status` = 1 or `status` = 6) and store_id = '.$_REQUEST['store_id'])->limit($limit)->order('pay_time desc')->select();
				break;
			case 1:
				$order = M('order')->where('pay_status = 1 and (`status` = 7 or `status` = 8 or `status` = 9) and store_id = '.$_REQUEST['store_id'])->limit($limit)->order('pay_time desc')->select();
				break;
			case 2:
				$order = M('order')->where('is_qucan = 1 and pay_status = 1 and `status` = 6 and store_id = '.$_REQUEST['store_id'])->limit($limit)->order('pay_time desc')->select();
				break;
			case 3:
				$order = M('order')->where('is_qucan = 1 and pay_status = 1 and (`status` = 7 or `status` = 8 or `status` = 9) and store_id = '.$_REQUEST['store_id'])->limit($limit)->order('pay_time desc')->select();
				break;
		}
		foreach($order as $key => $value){
			$value['delivery_time_ch'] = $value['delivery_time'] == 0 ? '尽快送达' : date('Y-m-d H:i:s',$value['delivery_time']);
			$value['pay_time'] = date('Y-m-d H:i:s',$value['pay_time']);
			$value['goods_number'] = M('order_goods')->where('ziying = 0 and order_id = '.$value['id'])->count();
			$goods = M('order_goods')->field('goods_name,goods_number,goods_price')->where('ziying = 0 and order_id = '.$value['id'])->select();
			foreach($goods as $k => $v){
				$v['goods_price'] = number_format($v['goods_number'] * $v['goods_price'],2);
				$goods[$k] = $v;
			}
			$value['goods'] = $goods;
			$order[$key] = $value;
		}
		$result['list'] = $order;
		die(json_encode($result));
	}

}