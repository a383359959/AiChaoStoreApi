<?php

namespace Api\Controller;

use Think\Controller;

class ServiceController extends Controller{

	public function __construct(){
		parent::__construct();
		$this->assign('time',time());
		$this->assign('input_time',date('Y-m-d'));
		$this->assign('store_id',$_REQUEST['store_id']);
	}

	/*
	*	商品编辑
	*/
	public function goods(){
		$this->display();
	}
	
	/*
	*	添加商品
	*/
	public function goods_edit(){
		if($_REQUEST['type'] == 'edit'){
			$rs = M('store_category_goods')->where('id = '.$_REQUEST['goods_id'])->find();
			$category_name = M('store_category')->where('id = '.$rs['category_id'])->getField('name');
		}
		$this->assign('rs',$rs);
		$this->assign('category_name',$category_name);
		$this->assign('store_category',M('store_category')->where('store_del = 0 and store_id = '.$_REQUEST['store_id'])->select());
		$this->display();
	}
	
	public function goods_select_catalog(){
		$this->assign('catalog_id',!empty($_REQUEST['catalog_id']) ? $_REQUEST['catalog_id'] : 0);
		$this->assign('list',M('store_category')->where('store_del = 0 and store_id = '.$_REQUEST['store_id'])->select());
		$this->display();
	}
	
	public function goods_edit_ajax(){
		$data['store_id'] = $_REQUEST['store_id'];
		$data['category_id'] = $_REQUEST['category_id'];
		$data['goods_name'] = $_REQUEST['goods_name'];
		$data['litpic'] = $_REQUEST['litpic'];
		$data['price'] = $_REQUEST['price'];
		if($_REQUEST['type'] == 'add'){
			M('store_category_goods')->add($data);
		}else if($_REQUEST['type'] == 'edit'){
			M('store_category_goods')->where('id = '.$_REQUEST['goods_id'])->save($data);
		}
		die(json_encode(array('status' => 'success')));
	}
	
	public function goods_img_upload(){
		$file = $_FILES['file'];
		$name = $file['name'];
		$type = strtolower(substr($name,strrpos($name,'.')+1));
		$allow_type = array('jpg','jpeg','gif','png');
		if(!in_array($type,$allow_type)) die(json_encode(array('status' => 'error','msg' => '文件类型不正确')));
		$upload_path = dirname(dirname(THINK_PATH)).'/static_upload/';
		$up_name = time().'_'.uniqid().'.'.$type;
		if(move_uploaded_file($file['tmp_name'],$upload_path.$up_name)){
			die(json_encode(array('status' => 'success','path' => $up_name)));
		}else{
			die(json_encode(array('status' => 'error','msg' => '上传图片错误，请联系网站管理员')));
		}
	}
	
	public function getCatalog(){
		$list = M('store_category')->field('name,id')->where('store_del = 0 and store_id = '.$_REQUEST['store_id'])->order('sort asc')->select();
		$result['list'] = $list;
		die(json_encode($result));
	}
	
	public function getCatalogGoods(){
		$list = M('store_category_goods')->field('id,sale,litpic,goods_name,price,status')->where('category_id = '.$_REQUEST['category_id'].' and store_id = '.$_REQUEST['store_id'])->order('sort asc')->select();
		$category_name = M('store_category')->where('id = '.$_REQUEST['category_id'])->getField('name');
		$result['list'] = $list;
		$result['category_name'] = $category_name;
		die(json_encode($result));
	}
	
	public function setStatus(){
		$data['status'] = $_REQUEST['status'];
		M('store_category_goods')->where('id = '.$_REQUEST['goods_id'])->save($data);
		$result = array(
			'status' => 'success',
			'index' => $data['status']
		);
		die(json_encode($result));
	}

	public function goods_sort(){
		$this->display();
	}

	public function goods_sort_ajax(){
		$goods_ids = explode(',',$_REQUEST['goods_id']);
		foreach($goods_ids as $key => $value){
			$index = $key + 1;
			M('store_category_goods')->where('id = '.$value)->save(array('sort' => $index));
		}
		$result['status'] = 'success';
		die(json_encode($result));
	}

	public function goods_catalog(){
		$this->display();
	}

	public function goods_catalog_edit(){
		if($_REQUEST['type'] == 'edit') $rs = M('store_category')->where('id = '.$_REQUEST['category_id'])->find();
		$this->assign('rs',$rs);
		$this->display();
	}

	public function goods_catalog_edit_ajax(){
		$data['name'] = $_REQUEST['name'];
		$data['store_id'] = $_REQUEST['store_id'];
		if($_REQUEST['type'] == 'add'){
			M('store_category')->add($data);
		}else if($_REQUEST['type'] == 'edit'){
			M('store_category')->where('id = '.$_REQUEST['category_id'])->save($data);
		}else if($_REQUEST['type'] == 'del'){
			M('store_category')->where('id = '.$_REQUEST['category_id'])->delete();
		}
		die(json_encode(array('status' => 'success')));
	}

	public function category_sort_ajax(){
		$ids = explode(',',$_REQUEST['category_id']);
		foreach($ids as $key => $value){
			$index = $key + 1;
			M('store_category')->where('id = '.$value)->save(array('sort' => $index));
		}
		$result['status'] = 'success';
		die(json_encode($result));
	}

	public function seo(){
		$store = M('store')->where('id = '.$_REQUEST['store_id'])->find();
		$this->assign('store',$store);
		$this->display();
	}

	public function seo_action(){
		$data = array(
			'ranking_status' => $_REQUEST['ranking_status'],
			'ranking' => $_REQUEST['ranking'],
			'ranking_budget' => $_REQUEST['ranking_budget']
		);
		M('store')->where('id = '.$_REQUEST['store_id'])->save($data);
		$result['status'] = 'success';
		die(json_encode($result));
	}

	public function activity(){
		$this->display();
	}

	public function activity_edit(){
		if($_REQUEST['action'] == 'edit'){
			$find = M('store_activity')->where('id = '.$_REQUEST['id'])->find();
			if($find['type'] == 1){
				$arr = array();
				$list = explode(',',$find['data']);
				foreach($list as $key => $value){
					$arr[] = explode('-',$value);
				}
				$this->assign('data',json_encode($arr));
			}else{
				$arr = array();
				if($find['type'] == 4){
					$ids = explode(',',$find['data']);
					foreach($ids as $key => $value){
						$value = explode('-',$value);
						$goods_name = M('store_category_goods')->where('id = '.$value[0])->getField('goods_name');
						$arr[] = array($value[0],$goods_name,$value[1]);
					}
				}else{
					$list = M('store_category_goods')->field('id,goods_name')->where('id in ('.$find['data'].')')->select();
					foreach($list as $key => $value){
						$arr[] = array($value['id'],$value['goods_name']);
					}
				}
				$this->assign('data',json_encode($arr));
				$this->assign('begin_time',date('Y-m-d H:i:s',$find['begin_time']));
				$this->assign('end_time',date('Y-m-d H:i:s',$find['end_time']));
			}
		}
		$this->display('activity_'.$_REQUEST['type']);
	}

	public function setActivity(){
		$data['store_id'] = $_REQUEST['store_id'];
		$data['type'] = $_REQUEST['type'];
		$data['data'] = $_REQUEST['data'];
		switch($_REQUEST['type']){
			case 1:
				if($_REQUEST['action'] == 'add'){
					$data['add_time'] = time();
					M('store_activity')->add($data);
				}else if($_REQUEST['action'] == 'edit'){
					M('store_activity')->where('id = '.$_REQUEST['id'])->save($data);
				}
				$result['status'] = 'success';
				break;
			case 2:
				$data['begin_time'] = strtotime($_REQUEST['begin_time']);
				$data['end_time'] = strtotime($_REQUEST['end_time']);
				if($data['begin_time'] > $data['end_time']){
					$result['status'] = 'error';
					$result['msg'] = '开始时间不能大于结束时间！';
				}else{
					if($_REQUEST['action'] == 'add'){
						$data['add_time'] = time();
						M('store_activity')->add($data);
					}else if($_REQUEST['action'] == 'edit'){
						M('store_activity')->where('id = '.$_REQUEST['id'])->save($data);
					}
					$result['status'] = 'success';
				}
			case 3:
				$data['begin_time'] = strtotime($_REQUEST['begin_time']);
				$data['end_time'] = strtotime($_REQUEST['end_time']);
				if($data['begin_time'] > $data['end_time']){
					$result['status'] = 'error';
					$result['msg'] = '开始时间不能大于结束时间！';
				}else{
					if($_REQUEST['action'] == 'add'){
						$data['add_time'] = time();
						M('store_activity')->add($data);
					}else if($_REQUEST['action'] == 'edit'){
						M('store_activity')->where('id = '.$_REQUEST['id'])->save($data);
					}
					$result['status'] = 'success';
				}
				break;
			case 4:
				$data['begin_time'] = strtotime($_REQUEST['begin_time']);
				$data['end_time'] = strtotime($_REQUEST['end_time']);
				if($data['begin_time'] > $data['end_time']){
					$result['status'] = 'error';
					$result['msg'] = '开始时间不能大于结束时间！';
				}else{
					if($_REQUEST['action'] == 'add'){
						$data['add_time'] = time();
						M('store_activity')->add($data);
					}else if($_REQUEST['action'] == 'edit'){
						M('store_activity')->where('id = '.$_REQUEST['id'])->save($data);
					}
					$result['status'] = 'success';
				}
				break;
		}
		die(json_encode($result));
	}

	public function select_goods(){
		$this->display();
	}

	public function activity_list(){
		$this->display();
	}

	public function activity_ajax(){
		$list = M('store_activity')->where('store_id = '.$_REQUEST['store_id'])->order('id desc')->select();
		foreach($list as $key => $value){
			$html = '';
			$value['status'] = 1;	// 0：未开始 1：进行中 2：已结束
			switch($value['type']){
				case 1:
					$data = explode(',',$value['data']);
					$html = '';
					foreach($data as $k => $v){
						$v = explode('-',$v);
						$html .= '
						<div class="activity_list_layout_box">
							<span>满&nbsp;'.$v[0].'&nbsp;元，减&nbsp;'.$v[1].'&nbsp;元</span>
							<div class="clear"></div>
						</div>
						';
					}
					break;
				case 4:
					if(time() < $value['begin_time']) $value['status'] = 0;
					if(time() > $value['end_time']) $value['status'] = 2;
					$goods = explode(',',$value['data']);
					$goods_arr = array();
					foreach($goods as $k => $v){
						$row = explode('-',$v);
						$goods_name = M('store_category_goods')->where('id = '.$row[0])->getField('goods_name');
						$goods_arr[] = $goods_name.'&nbsp;-&nbsp;'.number_format($row[1],2).'元';
					}
					$html = '
						<div class="activity_list_layout_box">
							<span>开始时间：</span>
							<span>'.date('Y-m-d H:i:s',$value['begin_time']).'</span>
							<div class="clear"></div>
						</div>
						<div class="activity_list_layout_box">
							<span>结束时间：</span>
							<span>'.date('Y-m-d H:i:s',$value['end_time']).'</span>
							<div class="clear"></div>
						</div>
						<div class="activity_list_layout_box">
							<span>商品列表：</span>
							<span>'.implode('，',$goods_arr).'</span>
							<div class="clear"></div>
						</div>
					';
					break;
				default:
					if(time() < $value['begin_time']) $value['status'] = 0;
					if(time() > $value['end_time']) $value['status'] = 2;
					$goods = M('store_category_goods')->where('id in ('.$value['data'].')')->getField('group_concat(goods_name)');
					$html = '
						<div class="activity_list_layout_box">
							<span>开始时间：</span>
							<span>'.date('Y-m-d H:i:s',$value['begin_time']).'</span>
							<div class="clear"></div>
						</div>
						<div class="activity_list_layout_box">
							<span>结束时间：</span>
							<span>'.date('Y-m-d H:i:s',$value['end_time']).'</span>
							<div class="clear"></div>
						</div>
						<div class="activity_list_layout_box">
							<span>商品列表：</span>
							<span>'.$goods.'</span>
							<div class="clear"></div>
						</div>
					';
			}
			$value['html'] = $html;
			$list[$key] = $value;
		}
		$result['list'] = $list;
		die(json_encode($result));
	}

	public function delActivity(){
		M('store_activity')->where('id = '.$_REQUEST['id'])->delete();
		die(json_encode(array('status' => 'success')));
	}

}