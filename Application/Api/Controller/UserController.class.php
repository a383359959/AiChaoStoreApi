<?php

namespace Api\Controller;

use Think\Controller;

class UserController extends Controller{

	public function login(){
		$username = trim($_REQUEST['username']);
		$password = trim($_REQUEST['password']);
		$store = M('store')->field('id,username,password')->where('username = "'.$username.'"')->find();
		if(!$store) die(json_encode(array('status' => 'error','msg' => '商户不存在！')));
		if(md5($password) != $store['password']) die(json_encode(array('status' => 'error','msg' => '密码输入错误！')));
		$data = array(
			'token' => $_REQUEST['token'],
			'clientid' => $_REQUEST['clientid'],
			'appid' => $_REQUEST['appid'],
			'appkey' => $_REQUEST['appkey']
		);
		M('store')->where('id = '.$store['id'])->save($data);
		die(json_encode(array('status' => 'success','store_id' => $store['id'])));
	}

	public function forget(){
		$find = M('store')->where('store_name = "'.$_REQUEST['store_name'].'" and username = "'.$_REQUEST['username'].'"')->find();
		if($find){
			$data['password'] = md5($_REQUEST['password']);
			M('store')->where('id = '.$find['id'])->save($data);
			$result = array(
				'status' => 'success'
			);
		}else{
			$result = array(
				'status' => 'error',
				'msg' => '店铺名称与用户名不符！'
			);
		}
		die(json_encode($result));
	}

	public function store_info(){
		$field = '*';
		if(!empty($_REQUEST['field'])) $field = $_REQUEST['field'];
		$rs = M('store')->field($field)->where('id = '.$_REQUEST['store_id'])->find();
		$rs['order_data'] = $this->orderData();
		die(json_encode($rs));
	}

	public function orderData(){
		$where = array(
			$this->get_where('pay_time').' = 0 and `status` > 6 and store_id = '.$_REQUEST['store_id'],
			$this->get_where('add_time').' = -1 and `status` > 6 and store_id = '.$_REQUEST['store_id'],
			$this->get_where('add_time').' = -1 and store_id = '.$_REQUEST['store_id']
		);
		$rs_1 = M('order')->field('ifnull(sum(goods_price_real),0) as today_price')->where($where[0])->find();
		$rs_2 = M('order')->field('ifnull(sum(goods_price_real),0) as yestoday_price')->where($where[1])->find();
		$rs_3 = M('store_price_log')->field('ifnull(surplus_price,0) as yestoday_balance')->where($where[2])->order('id desc')->find();
		if(!$rs_3) $rs_3['yestoday_balance'] = number_format(0,2);
		$result['today_price'] = $rs_1['today_price'];
		$result['yestoday_price'] = $rs_2['yestoday_price'];
		$result['today_order'] = M('order')->where($where[0])->count();
		$result['yestoday_order'] = M('order')->where($where[1])->count();
		$result['today_balance'] = M('store')->where('id = '.$_REQUEST['store_id'])->getField('price');
		$result['yestoday_balance'] = $rs_3['yestoday_balance'];
		return $result;
	}

	public function real_time_data(){
		$rs['order_data'] = $this->orderData();
		die(json_encode($rs));
	}

	public function get_where($field){
		return 'datediff(FROM_UNIXTIME('.$field.',\'%Y-%m-%d %H%:%i:%s\'),now())';
	}

	public function business_status(){
		M('store')->where('id = '.$_REQUEST['store_id'])->save(array('business_status' => $_REQUEST['business_status']));
		die(json_encode(array('status' => 'success')));
	}

	public function uploadImg(){
		$upload_path = dirname(dirname(THINK_PATH)).'/static_upload/';
		$file = $_FILES['file'];
		$name = $file['name'];
		$type = strtolower(substr($name,strrpos($name,'.')+1));
		$allow_type = array('jpg','jpeg','gif','png');
		if(!in_array($type, $allow_type)) exit(json_encode(array('status' => 'error','msg' => '文件类型不正确')));
		$up_name = time().'_'.uniqid().'.'.$type;
		if(move_uploaded_file($file['tmp_name'],$upload_path.$up_name)){
			exit(json_encode(array('status' => 'success','path' => $up_name)));
		}else{
			exit(json_encode(array('status' => 'error','msg' => '上传图片错误，请联系网站管理员')));
		}
	}

	public function getCategory(){
		$list = M('category')->where('parent_id = 0')->order('sort asc')->select();
		foreach($list as $key => $value){
			$value['child'] = M('category')->where('parent_id = '.$value['id'])->order('sort asc')->select();
			$list[$key] = $value;
		}
		$result['list'] = $list;
		die(json_encode($result));
	}

	public function store_time(){
		$time_period = M('store')->where('id = '.$_REQUEST['store_id'])->getField('time_period');
		if($time_period != ''){
		$time_period = explode(',',$time_period);
			foreach($time_period as $key => $value){
				$value = explode('-',$value);
				$time_period[$key] = $value;
			}
		}
		$result['list'] = $time_period;
		die(json_encode($result));
	}

	public function setTimePeriod(){
		$data['time_period'] = $_REQUEST['time_period'];
		M('store')->where('id = '.$_REQUEST['store_id'])->save($data);
		die(json_encode(array('status' => 'success')));
	}

	public function getDesc(){
		$desc = M('store')->where('id = '.$_REQUEST['store_id'])->getField('desc');
		$result['desc'] = $desc;
		die(json_encode($result));
	}

	public function setDesc(){
		$data['desc'] = $_REQUEST['desc'];
		M('store')->where('id = '.$_REQUEST['store_id'])->save($data);
		die(json_encode(array('status' => 'success')));
	}

	public function setInfo(){
		$data['logo'] = $_REQUEST['logo'];
		$data['store_name'] = $_REQUEST['store_name'];
		$data['address'] = $_REQUEST['address'];
		$data['type_id'] = $_REQUEST['type_id'];
		$data['store_phone'] = $_REQUEST['store_phone'];
		M('store')->where('id = '.$_REQUEST['store_id'])->save($data);
		die(json_encode(array('status' => 'success')));
	}

	public function pay(){
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$arr = array(
			'appid' => 'wx020ad8b3a2a0c065',
			'mch_id' => '1349659401',
			'nonce_str' => $this->getNonceStr(),
			'body' => '账户充值',
			'out_trade_no' => time(),
			'total_fee' => 0.1 * 100,
			'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
			'notify_url' => 'https://api.smdouyou.com/weixinasd.php',
			'trade_type' => 'APP',
		);
		$arr['sign'] = $this->sign($arr);
		$data = $this->ToXml($arr);
		$result = $this->postData($url,$data);
		print_r($result);
	}

	public function sign($arr){
		ksort($arr);
		$string = '';
		foreach ($arr as $key => $value){
			if($key != 'sign' && $value != '' && !is_array($value)) $string .= $key.'='.$value.'&';
		}
		$string = trim($string,'&');
		$string .= '&key=c86eb87db2fd7713f25d5c5858d1e42b';
		$string = md5($string);
		$string = strtoupper($string);
		return $string;
	}

	public function postData($url,$data){
		$options = array(
			'http' => array(
				'method' => 'POST',
				'content' => $data,
				'timeout' => 15 * 60,
				'header' => 'Content-type:application/xml;encoding=utf-8'
			)
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url,false,$context);
		$result = json_decode(json_encode(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA)),true);
		return $result;
	}

	public function ToXml($arr){
		$xml = '<xml>';
		foreach ($arr as $key => $value){
			if(is_numeric($value)){
				$xml .= '<'.$key.'>'.$value.'</'.$key.'>';
			}else{
				$xml .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
			}
		}
		$xml .= '</xml>';
		return $xml; 
	}

	public function getNonceStr(){
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';  
		$string = '';
		for ($i = 0;$i < 32;$i++ ){  
			$string .= substr($chars,mt_rand(0,strlen($chars) - 1),1);  
		}
		return $string;
	}

}