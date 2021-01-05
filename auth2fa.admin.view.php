<?php

/**
 * 2FA 인증 모듈
 * 
 * Copyright (c) Waterticket
 * 
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class Auth2faAdminView extends Auth2fa
{
	/**
	 * 초기화
	 */
	public function init()
	{
		// 관리자 화면 템플릿 경로 지정
		$this->setTemplatePath($this->module_path . 'tpl');
	}
	
	/**
	 * 관리자 설정 화면 예제
	 */
	public function dispAuth2faAdminConfig()
	{
		// 현재 설정 상태 불러오기
		$config = $this->getConfig();

		$oModuleModel = getModel('module');
		$skin_list = $oModuleModel->getSkins($this->module_path);
		$auth_type = array("GGL"=>"Google OTP", "ATY"=>"Authy");
		
		// Context에 세팅
		Context::set('auth2fa_config', $config);
		Context::set('skin_list', $skin_list);
		Context::set('auth_type', $auth_type);
		
		// 스킨 파일 지정
		$this->setTemplateFile('config');
	}
}
