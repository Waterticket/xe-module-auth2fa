<?php

include_once __DIR__ . '/auth2fa.view.php';

/**
 * 2FA 인증 모듈
 * 
 * Copyright (c) Waterticket
 * 
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class Auth2faMobile extends Auth2faView
{
	/**
	 * 초기화
	 */
	public function init()
	{
		// 스킨 경로 지정
		$this->setTemplatePath($this->module_path . 'm.skins/' . ($this->module_info->mskin ?: 'default'));
	}
	
	/**
	 * 이 클래스에서 따로 정의하지 않은 함수는 View를 따름
	 */
}
