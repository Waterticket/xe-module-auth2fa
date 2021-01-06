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
			return $this->createObject(-1, "로그인이 필요합니다");
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
		
		$userconfigs = $oAuth2FAModel->getMemberConfigs($member_srl);
		Context::set("user_configs", $userconfigs);
		$this->setTemplateFile('user_config');
	}
	
	public function dispAuth2faAuthyConfig()
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

		$logged_info = Context::get('logged_info');
		$user_data = new stdClass();
		$user_data->email_address = $logged_info->email_address;
		$user_data->phone_number = $logged_info->phone_number;
		$user_data->phone_country = $logged_info->phone_country;
		Context::set('user_data', $user_data);
		
		
		$this->setTemplateFile('user_authy_new');
	}
	
	public function dispAuth2faGoogleOTPConfig()
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
		$domain = parse_url(getFullUrl());

		$oAuth2FAModel = getModel('auth2fa');
		if (!$oAuth2FAModel->checkMemberConfig($member_srl, "GGL"))
		{
			$obj = new stdClass();
			$obj->member_srl = $member_srl;
			$obj->type = "GGL";
			$oAuth2FAModel->insertNewConfig($obj);
		}
		
		$userconfig = $oAuth2FAModel->getMemberConfig($member_srl, "GGL");
		$userconfig->qrcode = $oAuth2FAModel->generateQRCode($domain['host'] . " - " . Context::get('logged_info')->user_id, $userconfig->otp_id);
		Context::set('auth2fa_config', $config);
		Context::set('user_config', $userconfig);

		$this->setTemplateFile('user_googleotp_config');
	}
	
	public function dispAuth2faVerify()
	{
		if (!Context::get("is_logged"))
		{
			return $this->createObject(-1, "로그인해주세요");
		}

		$config = $this->getConfig();
		$logged_info = Context::get('logged_info');
		$oAuth2FAModel = getModel('auth2fa');
		$user_config = $oAuth2FAModel->getPrimaryMemberConfig($logged_info->member_srl);
		
		Context::set('auth2fa_config', $config);

		if($user_config->type == "GGL")
		{
			$this->setTemplateFile('verify_google');
		}
		else if($user_config->type == "ATY")
		{
			$this->setTemplateFile('verify_authy');
		}
	}
	
	public function dispAuth2faAuthyVerifyApp()
	{
		if (!Context::get("is_logged"))
		{
			return $this->createObject(-1, "로그인해주세요");
		}

		$config = $this->getConfig();
		Context::set('auth2fa_config', $config);

		$this->setTemplateFile('user_authy_verify');
	}
}
