<?php
require_once(_XE_PATH_.'modules/auth2fa/libs/GoogleOTP/GoogleAuthenticator.php');
require_once(_XE_PATH_.'modules/auth2fa/libs/Authy/AuthyApi.php');
require_once(_XE_PATH_.'modules/auth2fa/libs/Authy/AuthyFormatException.php');
require_once(_XE_PATH_.'modules/auth2fa/libs/Authy/AuthyResponse.php');
require_once(_XE_PATH_.'modules/auth2fa/libs/Authy/AuthyToken.php');
require_once(_XE_PATH_.'modules/auth2fa/libs/Authy/AuthyUser.php');

/**
 * 2FA 인증 모듈
 * 
 * Copyright (c) Waterticket
 * 
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class Auth2faModel extends Auth2fa
{
	/**
	 * @brief 새로운 유저 설정을 추가합니다
	 * @param $obj
	 * @return BaseObject|object
	 * @throws Exception
	 */
	function insertNewConfig($obj)
	{
		$config = $this->getConfig();
		if($this->checkUserConfig($obj->member_srl, $obj->type)) return $this->createObject(-1, 'already_data_exist'); // 해당 타입의 데이터가 있으면 종료
		
		$conf = new stdClass();
		$conf->member_srl = $obj->member_srl;
		$conf->time = time();
		
		switch($obj->type)
		{
			case "GGL": // GoogleOTP
				$ga = new PHPGangsta_GoogleAuthenticator();
				$conf->otp_id = $ga->createSecret();
				$conf->use = "N";
				break;
				
			case "ATY": // Authy
				$authy_api = new Authy\AuthyApi($config->authy_api_key);
				$user = $authy_api->registerUser($obj->email, $obj->cellphone, $obj->country_code);
				if($user->ok()) {
					$conf->otp_id = $user->id();
					$conf->use = "N";
				} else {
					$error_str = "";
					foreach($user->errors() as $field => $message) {
						$error_str .= $field." ".$message;
					}
					return $this->createObject(-1, $error_str);
				}
				break;
				
			case "EML": // Email
				$conf->otp_id = "";
				$conf->use = "N";
				break;
				
			case "SMS": // SMS
				$conf->otp_id = "";
				$conf->use = "N";
				break;
		}
		
		$output = executeQuery('auth2fa.insertMemberConfig', $conf);
		return $output; //->toBool();
	}

	/**
	 * @brief 특정 타입의 유저 설정이 있는지 확인
	 * @param $member_srl
	 * @param $type
	 * @return bool
	 */
	function checkUserConfig($member_srl, $type)
	{
		$output = $this->getMemberConfig($member_srl, $type);
		return ($output !== false);
	}

	/**
	 * @brief 특정 타입의 유저 설정을 가져옵니다
	 * @param $member_srl
	 * @param $type
	 * @return MemberConfig|false
	 */
	function getMemberConfig($member_srl, $type)
	{
		$conf = new stdClass();
		$conf->member_srl = $member_srl;
		$conf->type = $type;
		$output = executeQuery('auth2fa.getMemberConfigbyMemberSrlAndType', $conf);

		if(!$output->toBool()) return false;
		else if(!isset($output->data->otp_id) || empty($output->data->otp_id)) return false;
		else return $output->data;
	}


	/**
	 * @brief 유저의 모든 유저 설정을 가져옵니다
	 * @param $member_srl
	 * @return Array|false
	 */
	function getMemberConfigs($member_srl)
	{
		//srl로 회원 조회
		$cond = new stdClass();
		$cond->member_srl = $member_srl;
		$output = executeQuery('auth2fa.getMemberConfigsbyMemberSrl', $cond);
		if(!$output->toBool()) return FALSE;
		else return $output->data;
	}
}
