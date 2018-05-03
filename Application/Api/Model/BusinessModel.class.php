<?php

namespace Api\Model;

use Think\Model;

class BusinessModel extends Model{
	
	protected $tableName = 'order';
	
	public function today(){
		$pay_time_where = $this->get_where('pay_time').' = 0 and `status` > 6 and store_id = '.$_REQUEST['store_id'];
		$pay_time_old_where = $this->get_where('add_time').' = -1 and `status` > 6 and store_id = '.$_REQUEST['store_id'];
		
		$add_time_where = $this->get_where('add_time').' = 0 and store_id = '.$_REQUEST['store_id'];
		$add_time_old_where = $this->get_where('add_time').' = -1 and store_id = '.$_REQUEST['store_id'];
		
		
		$price = $this->get_sum($pay_time_where);
		$old_price = $this->get_sum($pay_time_old_where);
		
		$expenditure = M('store_price_log')->field('ifnull(sum(price),0) as price')->where($this->get_where('add_time').' = 0 and type = 1 and store_id = '.$_REQUEST['store_id'])->find();
		$expenditure = $expenditure['price'];
		$old_expenditure = M('store_price_log')->field('ifnull(sum(price),0) as price')->where($this->get_where('add_time').' = -1 and type = 1 and store_id = '.$_REQUEST['store_id'])->find();
		$old_expenditure = $old_expenditure['price'];

		$count = $this->get_count($pay_time_where);
		$old_count = $this->get_count($pay_time_old_where);
		
		$old_balance = M('store_price_log')->where($add_time_old_where)->order('id desc')->getField('surplus_price');
		if(!$old_balance) $old_balance = 0;
		
		$result = array(
			'title' => date('Y-m-d'),
			'sort_title' => '比昨日',
			'price' => $price,
			'old_price' => $old_price,
			'price_item' => ($price >= $old_price ? '+&nbsp;'.number_format($price - $old_price,2) : '-&nbsp;'.number_format($old_price - $price,2)),
			'expenditure' => $expenditure,
			'old_expenditure' => $old_expenditure,
			'expenditure_item' => ($expenditure >= $old_expenditure ? '+&nbsp;'.number_format($expenditure - $old_expenditure,2) : '-&nbsp;'.number_format($old_expenditure - $expenditure,2)),
			'count' => $count,
			'old_count' => $old_count,
			'count_item' => ($count >= $old_count ? '+&nbsp;'.($count - $old_count) : '-&nbsp;'.($old_count - $count)),
			'balance' => $result['today_balance'] = M('store')->where('id = '.$_REQUEST['store_id'])->getField('price'),
			'old_balance' => $old_balance,
			'balance_item' => ($balance >= $old_balance ? '+&nbsp;'.number_format($balance - $old_balance,2) : '-&nbsp;'.number_format($old_balance - $balance,2))
		);
		return $result;
	}
	
	public function yestoday(){
		$pay_time_where = $this->get_where('pay_time').' = -1 and `status` > 6 and store_id = '.$_REQUEST['store_id'];
		$pay_time_old_where = $this->get_where('add_time').' = -2 and `status` > 6 and store_id = '.$_REQUEST['store_id'];
		
		$add_time_where = $this->get_where('add_time').' = -1 and store_id = '.$_REQUEST['store_id'];
		$add_time_old_where = $this->get_where('add_time').' = -2 and store_id = '.$_REQUEST['store_id'];
		
		
		$price = $this->get_sum($pay_time_where);
		$old_price = $this->get_sum($pay_time_old_where);
		
		$expenditure = $this->get_sum($pay_time_where,'store_expenditure');
		$old_expenditure = $this->get_sum($pay_time_old_where,'store_expenditure');
		
		$count = $this->get_count($pay_time_where);
		$old_count = $this->get_count($pay_time_old_where);
		
		$balance = M('store_price_log')->where($add_time_where)->order('id desc')->getField('surplus_price');
		$old_balance = M('store_price_log')->where($add_time_old_where)->order('id desc')->getField('surplus_price');
		
		if(!$balance) $balance = 0;
		if(!$old_balance) $old_balance = 0;
		
		$result = array(
			'title' => date('Y-m-d'),
			'sort_title' => '比前日',
			'price' => $price,
			'price_item' => ($price >= $old_price ? '+&nbsp;'.number_format($price - $old_price,2) : '-&nbsp;'.number_format($old_price - $price,2)),
			'expenditure' => $expenditure,
			'expenditure_item' => ($expenditure >= $old_expenditure ? '+&nbsp;'.number_format($expenditure - $old_expenditure,2) : '-&nbsp;'.number_format($old_expenditure - $expenditure,2)),
			'count' => $count,
			'count_item' => ($count >= $old_count ? '+&nbsp;'.($count - $old_count) : '-&nbsp;'.($old_count - $count)),
			'balance' => $balance,
			'balance_item' => ($balance >= $old_balance ? '+&nbsp;'.number_format($balance - $old_balance,2) : '-&nbsp;'.number_format($old_balance - $balance,2))
		);
		return $result;
	}
	
	public function near_7th(){
		$begin_time = date('Y-m-d');
		$end_time = date('Y-m-d',strtotime('-7 day'));
		$q_end_time = date('Y-m-d',strtotime('-14 day'));
		
		$pay_time_where = 'pay_time between '.strtotime($end_time).' and '.strtotime($begin_time);
		$pay_time_old_where = 'pay_time between '.strtotime($q_end_time).' and '.strtotime($end_time);
		
		$add_time_where = 'store_id = '.$_REQUEST['store_id'].' and add_time between '.strtotime($end_time).' and '.strtotime($begin_time);
		$add_time_old_where = 'store_id = '.$_REQUEST['store_id'].' and add_time between '.strtotime($q_end_time).' and '.strtotime($end_time);
		
		$price = $this->get_sum($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		$old_price = $this->get_sum($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		
		$expenditure = $this->get_sum($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id'],'store_expenditure');
		$old_expenditure = $this->get_sum($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id'],'store_expenditure');
		
		$count = $this->get_count($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		$old_count = $this->get_count($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		
		$balance = M('store_price_log')->where($add_time_where)->order('id desc')->getField('surplus_price');
		$old_balance = M('store_price_log')->where($add_time_old_where)->order('id desc')->getField('surplus_price');
		
		if(!$balance) $balance = 0;
		if(!$old_balance) $old_balance = 0;
		
		$result = array(
			'title' => date('Y-m-d',strtotime('-7 day')).'&nbsp;至&nbsp;'.date('Y-m-d',strtotime('-1 day')),
			'sort_title' => '比前日',
			'price' => $price,
			'price_item' => ($price >= $old_price ? '+&nbsp;'.number_format($price - $old_price,2) : '-&nbsp;'.number_format($old_price - $price,2)),
			'expenditure' => $expenditure,
			'expenditure_item' => ($expenditure >= $old_expenditure ? '+&nbsp;'.number_format($expenditure - $old_expenditure,2) : '-&nbsp;'.number_format($old_expenditure - $expenditure,2)),
			'count' => $count,
			'count_item' => ($count >= $old_count ? '+&nbsp;'.($count - $old_count) : '-&nbsp;'.($old_count - $count)),
			'balance' => $balance,
			'balance_item' => ($balance >= $old_balance ? '+&nbsp;'.number_format($balance - $old_balance,2) : '-&nbsp;'.number_format($old_balance - $balance,2))
		);
		return $result;
	}
	
	public function near_30th(){
		$begin_time = date('Y-m-d');
		$end_time = date('Y-m-d',strtotime('-30 day'));
		$q_end_time = date('Y-m-d',strtotime('-60 day'));
		
		$pay_time_where = 'pay_time between '.strtotime($end_time).' and '.strtotime($begin_time);
		$pay_time_old_where = 'pay_time between '.strtotime($q_end_time).' and '.strtotime($end_time);
		
		$add_time_where = 'store_id = '.$_REQUEST['store_id'].' and add_time between '.strtotime($end_time).' and '.strtotime($begin_time);
		$add_time_old_where = 'store_id = '.$_REQUEST['store_id'].' and add_time between '.strtotime($q_end_time).' and '.strtotime($end_time);
		
		$price = $this->get_sum($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		$old_price = $this->get_sum($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		
		$expenditure = $this->get_sum($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id'],'store_expenditure');
		$old_expenditure = $this->get_sum($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id'],'store_expenditure');
		
		$count = $this->get_count($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		$old_count = $this->get_count($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		
		$balance = M('store_price_log')->where($add_time_where)->order('id desc')->getField('surplus_price');
		$old_balance = M('store_price_log')->where($add_time_old_where)->order('id desc')->getField('surplus_price');
		
		if(!$balance) $balance = 0;
		if(!$old_balance) $old_balance = 0;
		
		$result = array(
			'title' => date('Y-m-d',strtotime('-30 day')).'&nbsp;至&nbsp;'.date('Y-m-d',strtotime('-1 day')),
			'sort_title' => '比前日',
			'price' => $price,
			'price_item' => ($price >= $old_price ? '+&nbsp;'.number_format($price - $old_price,2) : '-&nbsp;'.number_format($old_price - $price,2)),
			'expenditure' => $expenditure,
			'expenditure_item' => ($expenditure >= $old_expenditure ? '+&nbsp;'.number_format($expenditure - $old_expenditure,2) : '-&nbsp;'.number_format($old_expenditure - $expenditure,2)),
			'count' => $count,
			'count_item' => ($count >= $old_count ? '+&nbsp;'.($count - $old_count) : '-&nbsp;'.($old_count - $count)),
			'balance' => $balance,
			'balance_item' => ($balance >= $old_balance ? '+&nbsp;'.number_format($balance - $old_balance,2) : '-&nbsp;'.number_format($old_balance - $balance,2))
		);
		return $result;
	}
	
	public function custom(){
		$cha = ceil((strtotime($_REQUEST['end_time'].' 23:59:59') - strtotime($_REQUEST['begin_time'].' 00:00:00')) / 86400);
		if($cha < 0) $cha = 0;
		
		$q_begin_time = strtotime($_REQUEST['begin_time'].' 00:00:00') - 86400 * $cha;
		
		$pay_time_where = 'pay_time between '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and '.strtotime($_REQUEST['end_time'].' 23:59:59');
		$pay_time_old_where = 'pay_time between '.$q_begin_time.' and '.strtotime($_REQUEST['begin_time'].' 00:00:00');
		
		$add_time_where = 'store_id = '.$_REQUEST['store_id'].' and add_time between '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and '.strtotime($_REQUEST['end_time'].' 23:59:59');
		$add_time_old_where = 'store_id = '.$_REQUEST['store_id'].' and add_time between '.$q_begin_time.' and '.strtotime($_REQUEST['begin_time'].' 00:00:00');
		
		$price = $this->get_sum($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		$old_price = $this->get_sum($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		
		$expenditure = $this->get_sum($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id'],'store_expenditure');
		$old_expenditure = $this->get_sum($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id'],'store_expenditure');
		
		$count = $this->get_count($pay_time_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		$old_count = $this->get_count($pay_time_old_where.' and `status` > 6 and store_id = '.$_REQUEST['store_id']);
		
		$balance = M('store_price_log')->where($add_time_where)->order('id desc')->getField('surplus_price');
		$old_balance = M('store_price_log')->where($add_time_old_where)->order('id desc')->getField('surplus_price');
		
		if(!$balance) $balance = 0;
		if(!$old_balance) $old_balance = 0;
		
		$result = array(
			'title' => $_REQUEST['begin_time'].'&nbsp;至&nbsp;'.$_REQUEST['end_time'],
			'sort_title' => '比前日',
			'price' => $price,
			'price_item' => ($price >= $old_price ? '+&nbsp;'.number_format($price - $old_price,2) : '-&nbsp;'.number_format($old_price - $price,2)),
			'expenditure' => $expenditure,
			'expenditure_item' => ($expenditure >= $old_expenditure ? '+&nbsp;'.number_format($expenditure - $old_expenditure,2) : '-&nbsp;'.number_format($old_expenditure - $expenditure,2)),
			'count' => $count,
			'count_item' => ($count >= $old_count ? '+&nbsp;'.($count - $old_count) : '-&nbsp;'.($old_count - $count)),
			'balance' => $balance,
			'balance_item' => ($balance >= $old_balance ? '+&nbsp;'.number_format($balance - $old_balance,2) : '-&nbsp;'.number_format($old_balance - $balance,2))
		);
		return $result;
	}
	
	public function get_count($where){
		$count = $this->where($where)->count();
		if(!$count) $count = 0;
		return $count;
	}
	
	public function get_sum($where,$field = 'goods_price_real'){
		$count = $this->where($where)->getField('sum('.$field.')');
		if(!$count) $count = 0;
		return $count;
	}
	
	public function get_where($field){
		return 'datediff(FROM_UNIXTIME('.$field.',\'%Y-%m-%d %H%:%i:%s\'),now())';
	}
	
}