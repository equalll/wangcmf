<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Validate;

class PublicUser extends Controller
{
	/**
	 * 用户登录
	 * @return mixed
	 */
	public function login()
	{
		// 登录后不能再进入登录页
		if(is_login())
		{
			$this->redirect('index/index');
		}
		if(request()->isPost())
		{
			// 获取POST提交的数据
			$data = input('post.');
			// 表单提交数据验证
			$checkResult = $this->checkFormData($data);
			if($checkResult !== '')
			{
				$this->assign('show_error_tips' , $checkResult);
			}
			// 在表单中展示上一次的提交数据
			$this->assign('data' , $data);
			// 执行登录操作
			if(!$this->doLogin($data))
			{
				$this->assign('show_error_tips' , '用户名或者密码错误');
			}
			else
			{
				$this->success('登录成功' , url('index/index'));
			}
		}
		return $this->fetch();
	}

	/**
	 * 执行登录操作
	 * @param $data
	 * @return bool
	 */
	private function doLogin($data)
	{
		if(!empty($data))
		{
			// 构建查询数据
			$map = [
				'mobile'    => $data['mobile'],
				'password'  => sys_md5($data['password']),
				'status'    => 1
			];
			// 查询用户是否存在
			$user_info = Db::name('user')->field('id as user_id,name,mobile')->where($map)->find();
			if($user_info === null)
			{
				return false;
			}
			// 记录会话信息
			session('user_auth' , $user_info);
			session('user_auth_sign' , data_auth_sign($user_info));
			return true;
		}
		return false;
	}

	/**
	 * 验证表单提交数据
	 * @param $data
	 * @return array|string
	 */
	public function checkFormData($data)
	{
		$rule = [
			'mobile'  => 'require|regex:\d{11}|token',
			'password'  => 'require|max:25|min:6',
		];
		$msg = [
			'mobile.regex' => '手机号格式错误',
			'password.require' => '密码不能为空'
		];
		$validate = new Validate($rule , $msg);
		$result   = $validate->check($data);
		if(!$result){
			return $validate->getError();
		}
		return '';
	}

	/**
	 * 注销登录
	 */
	public function logout(){
		session('user_auth', null);
		$this->success('退出成功！',url('public_user/login'));
	}
}
