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
		$config = $this->getConfig();

		$template_path = sprintf("%sskins/%s/", $this->module_path, $config->skin);
		if (!is_dir($template_path) || !$config->skin)
		{
			$config->skin = 'default';
			$template_path = sprintf("%sskins/%s/", $this->module_path, $config->skin);
		}

		$this->setTemplatePath($template_path);
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

		$config = $this->getConfig();
		Context::set('auth2fa_config', $config);

		$oAuth2FAModel = getModel('auth2fa');
		$domain = parse_url(getFullUrl());

//		if (!$oAuth2FAModel->checkUserConfig($member_srl))
//		{
//			$oAuth2FAModel->insertNewConfig($member_srl);
//		}
//		$userconfig = $oAuth2FAModel->getUserConfig($member_srl);
//		$userconfig->qrcode = $oAuth2FAModel->generateQRCode($domain['host'] . " - " . Context::get('logged_info')->user_id, $userconfig->otp_id);
		
		$userconfigs = $oAuth2FAModel->getMemberConfigs($member_srl);
		Context::set("user_configs", $userconfigs);
		$this->setTemplateFile('user_config');
	}
}
