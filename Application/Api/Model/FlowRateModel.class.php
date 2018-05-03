<?php

namespace Api\Model;

use Think\Model;

class FlowRateModel extends Model{
	
	protected $tableName = 'store_flow_rate';
	
	public function today(){
		$dd_count = $this->get_count($this->get_where('add_time').' = 0 and store_id = '.$_REQUEST['store_id']);
		$old_dd_count = $this->get_count($this->get_where('add_time').' = -1 and store_id = '.$_REQUEST['store_id']);		
		$user_order_count = M('order')->where($this->get_where('pay_time').' = 0 and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		$old_user_order_count = M('order')->where($this->get_where('pay_time').' = -1 and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		if(!$user_order_count) $user_order_count = 0;
		if(!$old_user_order_count) $old_user_order_count = 0;
		$result = array(
			'title' => date('Y-m-d'),
			'sort_title' => '比昨日',
			'dd_count' => $dd_count,
			'dd_item' => ($dd_count >= $old_dd_count ? '+&nbsp;'.($dd_count - $old_dd_count) : '-&nbsp;'.($old_dd_count - $dd_count)),
			'order_count' => $user_order_count,
			'order_item' => ($user_order_count >= $old_user_order_count ? '+&nbsp;'.($user_order_count - $old_user_order_count) : '-&nbsp;'.($old_user_order_count - $user_order_count))
		);
		return $result;
	}
	
	public function yestoday(){
		$dd_count = $this->get_count($this->get_where('add_time').' = -1 and store_id = '.$_REQUEST['store_id']);
		$old_dd_count = $this->get_count($this->get_where('add_time').' = -2 and store_id = '.$_REQUEST['store_id']);		
		$user_order_count = M('order')->where($this->get_where('pay_time').' = -1 and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		$old_user_order_count = M('order')->where($this->get_where('pay_time').' = -2 and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		if(!$user_order_count) $user_order_count = 0;
		if(!$old_user_order_count) $old_user_order_count = 0;
		$result = array(
			'title' => date('Y-m-d',strtotime('-1 day')),
			'sort_title' => '比前日',
			'dd_count' => $dd_count,
			'dd_item' => ($dd_count >= $old_dd_count ? '+&nbsp;'.($dd_count - $old_dd_count) : '-&nbsp;'.($old_dd_count - $dd_count)),
			'order_count' => $user_order_count,
			'order_item' => ($user_order_count >= $old_user_order_count ? '+&nbsp;'.($user_order_count - $old_user_order_count) : '-&nbsp;'.($old_user_order_count - $user_order_count))
		);
		return $result;
	}
	
	public function near_7th(){
		$begin_time = date('Y-m-d');
		$end_time = date('Y-m-d',strtotime('-7 day'));
		$q_end_time = date('Y-m-d',strtotime('-14 day'));
		$dd_count = $this->get_count('add_time between '.strtotime($end_time).' and '.strtotime($begin_time).' and store_id = '.$_REQUEST['store_id']);
		$old_dd_count = $this->get_count('add_time between '.strtotime($q_end_time).' and '.strtotime($end_time).' and store_id = '.$_REQUEST['store_id']);		
		$user_order_count = M('order')->where('pay_time between '.strtotime($end_time).' and '.strtotime($begin_time).' and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		$old_user_order_count = M('order')->where('pay_time between '.strtotime($q_end_time).' and '.strtotime($end_time).' and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		if(!$user_order_count) $user_order_count = 0;
		if(!$old_user_order_count) $old_user_order_count = 0;
		$result = array(
			'title' => date('Y-m-d',strtotime('-7 day')).'&nbsp;至&nbsp;'.date('Y-m-d',strtotime('-1 day')),
			'sort_title' => '比前&nbsp;7&nbsp;日',
			'dd_count' => $dd_count,
			'dd_item' => ($dd_count >= $old_dd_count ? '+&nbsp;'.($dd_count - $old_dd_count) : '-&nbsp;'.($old_dd_count - $dd_count)),
			'order_count' => $user_order_count,
			'order_item' => ($user_order_count >= $old_user_order_count ? '+&nbsp;'.($user_order_count - $old_user_order_count) : '-&nbsp;'.($old_user_order_count - $user_order_count))
		);
		return $result;
	}
	
	public function near_30th(){
		$begin_time = date('Y-m-d');
		$end_time = date('Y-m-d',strtotime('-30 day'));
		$q_end_time = date('Y-m-d',strtotime('-60 day'));
		$dd_count = $this->get_count('add_time between '.strtotime($end_time).' and '.strtotime($begin_time).' and store_id = '.$_REQUEST['store_id']);
		$old_dd_count = $this->get_count('add_time between '.strtotime($q_end_time).' and '.strtotime($end_time).' and store_id = '.$_REQUEST['store_id']);		
		$user_order_count = M('order')->where('pay_time between '.strtotime($end_time).' and '.strtotime($begin_time).' and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		$old_user_order_count = M('order')->where('pay_time between '.strtotime($q_end_time).' and '.strtotime($end_time).' and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		if(!$user_order_count) $user_order_count = 0;
		if(!$old_user_order_count) $old_user_order_count = 0;
		$result = array(
			'title' => date('Y-m-d',strtotime('-30 day')).'&nbsp;至&nbsp;'.date('Y-m-d',strtotime('-1 day')),
			'sort_title' => '比前&nbsp;30&nbsp;日',
			'dd_count' => $dd_count,
			'dd_item' => ($dd_count >= $old_dd_count ? '+&nbsp;'.($dd_count - $old_dd_count) : '-&nbsp;'.($old_dd_count - $dd_count)),
			'order_count' => $user_order_count,
			'order_item' => ($user_order_count >= $old_user_order_count ? '+&nbsp;'.($user_order_count - $old_user_order_count) : '-&nbsp;'.($old_user_order_count - $user_order_count))
		);
		return $result;
	}
	
	public function custom(){
		$cha = ceil((strtotime($_REQUEST['end_time'].' 23:59:59') - strtotime($_REQUEST['begin_time'].' 00:00:00')) / 86400);
		if($cha < 0) $cha = 0;
		$q_begin_time = strtotime($_REQUEST['begin_time'].' 00:00:00') - 86400 * $cha;
		$dd_count = $this->get_count('add_time between '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and '.strtotime($_REQUEST['end_time'].' 23:59:59').' and store_id = '.$_REQUEST['store_id']);
		$old_dd_count = $this->get_count('add_time between '.$q_begin_time.' and '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and store_id = '.$_REQUEST['store_id']);		
		$user_order_count = M('order')->where('pay_time between '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and '.strtotime($_REQUEST['end_time'].' 23:59:59').' and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		$old_user_order_count = M('order')->where('pay_time between '.$q_begin_time.' and '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and store_id = '.$_REQUEST['store_id'])->group('user_id')->count();
		if(!$user_order_count) $user_order_count = 0;
		if(!$old_user_order_count) $old_user_order_count = 0;
		$result = array(
			'title' => $_REQUEST['begin_time'].'&nbsp;至&nbsp;'.$_REQUEST['end_time'],
			'sort_title' => '比前&nbsp;'.$cha.'&nbsp;日',
			'dd_count' => $dd_count,
			'dd_item' => ($dd_count >= $old_dd_count ? '+&nbsp;'.($dd_count - $old_dd_count) : '-&nbsp;'.($old_dd_count - $dd_count)),
			'order_count' => $user_order_count,
			'order_item' => ($user_order_count >= $old_user_order_count ? '+&nbsp;'.($user_order_count - $old_user_order_count) : '-&nbsp;'.($old_user_order_count - $user_order_count))
		);
		return $result;
	}
	
	public function get_count($where){
		$count = $this->where($where)->count();
		if(!$count) $count = 0;
		return $count;
	}
	
	public function get_where($field){
		return 'datediff(FROM_UNIXTIME('.$field.',\'%Y-%m-%d %H%:%i:%s\'),now())';
	}
	
}