<?php

/**
 * 2FA 인증 모듈
 * 
 * Copyright (c) Waterticket
 * 
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class Auth2faAdminController extends Auth2fa
{
	/**
	 * 관리자 설정 저장 액션 예제
	 */
	public function procAuth2faAdminInsertConfig()
	{
		// 현재 설정 상태 불러오기
		$config = $this->getConfig();
		
		// 제출받은 데이터 불러오기
		$vars = Context::getRequestVars();
		
		// 제출받은 데이터를 각각 적절히 필터링하여 설정 변경
		if (in_array($vars->module_enabled, ['Y', 'N']))
		{
			$config->module_enabled = $vars->module_enabled;
		}
		else
		{
			return $this->createObject(-1, '설정값이 이상함');
		}
		
		
		$config->auth_allowed_type = $vars->auth_allowed_type;
		$config->authy_api_key = $vars->authy_api_key;
		$config->skin = $vars->skin;
		
		// 변경된 설정을 저장
		$output = $this->setConfig($config);
		if (!$output->toBool())
		{
			return $output;
		}
		
		// 설정 화면으로 리다이렉트
		$this->setMessage('success_registed');
		$this->setRedirectUrl(Context::get('success_return_url'));
	}
}
