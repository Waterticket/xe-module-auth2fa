<?php

/**
 * 2FA 인증 모듈
 * 
 * Copyright (c) Waterticket
 * 
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class Auth2faController extends Auth2fa
{
	function triggerAddMemberMenu()
	{
		$logged_info = Context::get('logged_info');
		if(!Context::get('is_logged')) return $this->createObject();

		$oMemberController = getController('member');
		$oMemberController->addMemberMenu('dispAuth2faUserConfig', "2FA 설정");
		if($logged_info->is_admin== 'Y')
		{
			$target_srl = Context::get('target_srl');

			$url = getUrl('','act','dispAuth2faUserConfig','member_srl',$target_srl);
			$oMemberController->addMemberPopupMenu($url, '유저 2FA 설정', '');
		}
		
		return $this->createObject();
	}

	function triggerHijackLogin(&$obj) {
		if(!Context::get("is_logged") || $obj->act === "dispMemberLogout") {
			unset($_SESSION['auth2fa_passed']);
			return;
		}

		$oAuth2FAModel = getModel('auth2fa');
		$userconfig = $oAuth2FAModel->getPrimaryMemberConfig(Context::get('logged_info')->member_srl);
		if($userconfig->use === "Y") {
			$allowedact = array("dispAuth2faVerify","procAuth2faVerify","procMemberLogin","dispMemberLogout");
			if(!in_array($obj->act, $allowedact) && !$_SESSION['auth2fa_passed'])
			{
				$_SESSION['beforeaddress'] = getNotEncodedUrl();
				header("Location: " . getNotEncodedUrl('act','dispAuth2faVerify'));
				Context::close();
				die();
			}
		}
	}
	
	function procAuth2faUserConfig()
	{
		
	}
	
	function procAuth2faVerify()
	{
		if(!Context::get("is_logged")) return $this->createObject(-1,"로그인하지 않았습니다.");
		if($_SESSION['auth2fa_passed']) return $this->createObject(-1,"이미 인증했습니다.");

		$vars = Context::getRequestVars();
		$otpnumber = $vars->otp_num;

		// change 111 111 to 111111
		$otpnumber = explode(" ",$otpnumber);
		$otpnumber = implode("",$otpnumber);

		$member_srl = Context::get('logged_info')->member_srl;

		$oAuth2FAModel = getModel('auth2fa');
		$config = $oAuth2FAModel->getMemberConfig($member_srl, $vars->type);

		if($oAuth2FAModel->checkOTPNumber($member_srl, $otpnumber, "GGL"))
		{
			if(!$oAuth2FAModel->checkUsedNumber($member_srl, $otpnumber))
			{
				return $this->createObject(-1,"이미 인증에 사용된 OTP 번호입니다. 다른 번호를 사용해주세요.");
			}
			else
			{
				$oAuth2FAModel->insertAuthlog($member_srl,"GGL",$otpnumber,"Y");
				$_SESSION['auth2fa_passed'] = TRUE;
				$this->setRedirectUrl($_SESSION['beforeaddress']);
			}
		}
		else
		{
			$oAuth2FAModel->insertAuthlog($member_srl,"GGL",$otpnumber,"N");
			$this->setMessage("잘못된 OTP 번호입니다");
			$this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispAuth2faVerify'));
		}
	}
	
	function procAuth2faAuthyConfig()
	{
		$vars = Context::getRequestVars();
		$oAuth2FAModel = getModel('auth2fa');
		$member_srl = Context::get('logged_info')->member_srl;
		
		$output = $oAuth2FAModel->authyRegisterUser($member_srl ,$vars->authy_email, $vars->authy_phone_number, $vars->authy_phone_country);
		if(!$output->toBool()) return $output;

		$this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispAuth2faAuthyVerifyApp'));
	}
}
