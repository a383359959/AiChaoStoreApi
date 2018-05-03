<?php

namespace Api\Controller;

use Think\Controller;

class DataController extends Controller{

	public function __construct(){
		parent::__construct();
		$this->assign('time',time());
		$this->assign('input_time',date('Y-m-d'));
		$this->assign('store_id',$_REQUEST['store_id']);
	}

	public function index(){
		$data = D('Business');
		$this->assign('data',$data->today());
		$this->display();
	}
	
	/*
	*	流量分析
	*/
	public function flow_rate(){
		$this->display();
	}

	public function tixian(){
		$store = M('store')->field('price')->where('id = '.$_REQUEST['store_id'])->find();
		$this->assign('store',$store);
		$this->display();
	}

	public function tixianAction(){
		$data = array(
			'store_id' => $_REQUEST['store_id'],
			'store_name' => $_REQUEST['store_name'],
			'alipay_accounts' => $_REQUEST['alipay_accounts'],
			'alipay_name' => $_REQUEST['alipay_name'],
			'price' => $_REQUEST['price'],
			'add_time' => time(),
			'status' => 1
		);
		M('store_cash')->add($data);
		M('store')->where('id = '.$_REQUEST['store_id'])->setDec('price',$_REQUEST['price']);
		storePriceLog($_REQUEST['store_id'],$_REQUEST['price'],1,'提现');
		die(json_encode(array('status' => 'success')));
	}

	public function tixianlog(){
		$limit = (($_REQUEST['page'] - 1) * 10).',10';
		$list = M('store_price_log')->where('store_id = '.$_REQUEST['store_id'])->limit($limit)->order('add_time desc,id desc')->select();
		foreach($list as $key => $value){
			$value['add_time'] = date('Y-m-d H:i:s',$value['add_time']);
			$list[$key] = $value;
		}
		$result['list'] = $list;
		die(json_encode($result));
	}
	
	public function flow_rate_ajax(){
		$data = D('FlowRate');
		switch($_REQUEST['index']){
			case 0:
				$arr = $data->today();
				break;
			case 1:
				$arr = $data->yestoday();
				break;
			case 2:
				$arr = $data->near_7th();
				break;
			case 3:
				$arr = $data->near_30th();
				break;
			case 4:
				$arr = $data->custom();
				break;
		}
		$html = '
			<h1>店铺流量（'.$arr['title'].'）</h1>
			<div class="flow_rate_main">
				<table>
					<tr>
						<td>到店客户数</td>
						<td>'.$arr['sort_title'].'</td>
					</tr>
					<tr>
						<td>'.$arr['dd_count'].'</td>
						<td>'.$arr['dd_item'].'</td>
					</tr>
				</table>
				<table>
					<tr>
						<td>下单客户数</td>
						<td>'.$arr['sort_title'].'</td>
					</tr>
					<tr>
						<td>'.$arr['order_count'].'</td>
						<td>'.$arr['order_item'].'</td>
					</tr>
				</table>
			</div>
		';
		$result['html'] = $html;
		die(json_encode($result));
	}
	
	/*
	*	菜品分析
	*/
	public function goods(){
		$this->display();
	}
	
	public function goods_ajax(){
		switch($_REQUEST['index']){
			case 0:
				$where = 'b.pay_time > 0 and datediff(FROM_UNIXTIME(b.pay_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = 0';
				$goods_eval_where = 'datediff(FROM_UNIXTIME(add_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = 0';
				break;
			case 1:
				$where = 'b.pay_time > 0 and datediff(FROM_UNIXTIME(b.pay_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = -1';
				$goods_eval_where = 'datediff(FROM_UNIXTIME(add_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = -1';
				break;
			case 2:
				$where = 'b.pay_time > 0 and datediff(FROM_UNIXTIME(b.pay_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = -7';
				$goods_eval_where = 'datediff(FROM_UNIXTIME(add_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = -7';
				break;
			case 3:
				$where = 'b.pay_time > 0 and datediff(FROM_UNIXTIME(b.pay_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = -30';
				$goods_eval_where = 'datediff(FROM_UNIXTIME(add_time,\'%Y-%m-%d %H%:%i:%s\'),now()) = -30';
				break;
			case 4:
				$where = 'b.pay_time >= '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and b.pay_time <= '.strtotime($_REQUEST['end_time'].' 23:59:59');
				$goods_eval_where = 'add_time >= '.strtotime($_REQUEST['begin_time'].' 00:00:00').' and add_time <= '.strtotime($_REQUEST['end_time'].' 23:59:59');
				break;
		}
		$goods_list = M('store_category_goods as a')
			->join('`ythink_order_goods` as b on a.id = b.goods_id and '.$where,'LEFT')
			->field('a.id,a.goods_name,ifnull(sum(b.goods_number),0) as goods_number,ifnull(sum(b.goods_price * b.goods_number),0) as goods_price')
			->where('a.ziying = 0 and a.store_id = '.$_REQUEST['store_id'])
			->order('goods_number desc')
			->group('a.id')
			->select();
		$html = '';
		foreach($goods_list as $key => $value){
			
			$goods_eval_h = M('goods_eval')->where($goods_eval_where.' and (lv = 3 or lv = 4) and goods_id = '.$value['id'])->count();
			$goods_eval_c = M('goods_eval')->where($goods_eval_where.' and (lv = 1 or lv = 2) and goods_id = '.$value['id'])->count();
			$html .= '
				<li>
					<h2>'.$value['goods_name'].'</h2>
					<p>销售额：￥'.$value['goods_price'].'&nbsp;&nbsp;销量：'.$value['goods_number'].'&nbsp;&nbsp;好评：'.$goods_eval_h.'&nbsp;&nbsp;差评：'.$goods_eval_c.'</p>
				</li>
			';
		}
		$result['html'] = $html;
		die(json_encode($result));
	}
	
	/*
	*	流量分析
	*/
	public function business(){
		$this->display();
	}
	
	public function business_ajax(){
		$data = D('Business');
		switch($_REQUEST['index']){
			case 0:
				$arr = $data->today();
				break;
			case 1:
				$arr = $data->yestoday();
				break;
			case 2:
				$arr = $data->near_7th();
				break;
			case 3:
				$arr = $data->near_30th();
				break;
			case 4:
				$arr = $data->custom();
				break;
		}
		$html = '
			<h1>营业统计（'.$arr['title'].'）</h1>
			<div class="business_main">
				<table>
					<tr>
						<td>营业额</td>
						<td>'.$arr['sort_title'].'</td>
					</tr>
					<tr>
						<td>'.number_format($arr['price'],2).'</td>
						<td>'.$arr['price_item'].'</td>
					</tr>
				</table>
				<table>
					<tr>
						<td>余额</td>
						<td>'.$arr['sort_title'].'</td>
					</tr>
					<tr>
						<td>'.number_format($arr['balance'],2).'</td>
						<td>'.$arr['balance_item'].'</td>
					</tr>
				</table>
				<table>
					<tr>
						<td>支出</td>
						<td>'.$arr['sort_title'].'</td>
					</tr>
					<tr>
						<td>'.number_format($arr['expenditure'],2).'</td>
						<td>'.$arr['expenditure_item'].'</td>
					</tr>
				</table>
				<table>
					<tr>
						<td>订单</td>
						<td>'.$arr['sort_title'].'</td>
					</tr>
					<tr>
						<td>'.$arr['count'].'</td>
						<td>'.$arr['count_item'].'</td>
					</tr>
				</table>
			</div>
		';
		$result['html'] = $html;
		die(json_encode($result));
	}

}