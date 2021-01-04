<?php

/**
 * 2FA 인증 모듈
 * 
 * Copyright (c) Waterticket
 * 
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class Auth2faView extends Auth2fa
{
	/**
	 * 초기화
	 */
	public function init()
	{
		// 스킨 경로 지정
		$this->setTemplatePath($this->module_path . 'skins/' . ($this->module_info->skin ?: 'default'));
	}
	
	/**
	 * 메인 화면 예제
	 */
	public function dispAuth2faIndex()
	{
		// 스킨 파일명 지정
		$this->setTemplateFile('index');
	}
	
	public function dispAuth2faUserConfig()
	{
		if (!Context::get("is_logged"))
		{
			return $this->createObject(-1, "로그인해주세요");
		}

		if (Context::get('logged_info')->is_admin === "Y")
		{
			$member_srl = Context::get('member_srl') ? Context::get('member_srl') : Context::get('logged_info')->member_srl;
		}
		else
		{
			$member_srl = Context::get('logged_info')->member_srl;
		}

		$oAuth2FAModel = getModel('auth2fa');
		$domain = parse_url(getFullUrl());

		if (!$oAuth2FAModel->checkUserConfig($member_srl))
		{
			$oAuth2FAModel->insertNewConfig($member_srl);
		}
		$userconfig = $oAuth2FAModel->getUserConfig($member_srl);
		$userconfig->qrcode = $oAuth2FAModel->generateQRCode($domain['host'] . " - " . Context::get('logged_info')->user_id, $userconfig->otp_id);
		Context::set("user_config", $userconfig);
	}
}
