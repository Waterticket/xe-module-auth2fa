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
//		if(!Context::get("is_logged") || $obj->act === "dispMemberLogout") {
//			unset($_SESSION['auth2fa_passed']);
//			return;
//		}
//
//		$oAuth2FAModel = getModel('auth2fa');
//		$userconfig = $oAuth2FAModel->getUserConfig(Context::get('logged_info')->member_srl);
//		if($userconfig->use === "Y") {
//			$allowedact = array("dispGoogleotpInputotp","procGoogleotpInputotp","procMemberLogin","dispMemberLogout");
//			if(!in_array($obj->act,$allowedact) && !$_SESSION['auth2fa_passed'])
//			{
//				$_SESSION['beforeaddress'] = getNotEncodedUrl();
//				header("Location: " . getNotEncodedUrl('act','dispGoogleotpInputotp'));
//				Context::close();
//				die();
//			}
//		}
	}
}
