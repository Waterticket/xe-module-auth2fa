<?php
require_once(_XE_PATH_.'modules/auth2fa/libs/GoogleOTP/GoogleAuthenticator.php');

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
		if($this->checkMemberConfig($obj->member_srl, $obj->type)) return $this->createObject(-1, 'already_data_exist'); // 해당 타입의 데이터가 있으면 종료
		
		$conf = new stdClass();
		$conf->member_srl = $obj->member_srl;
		$conf->type = $obj->type;
		$conf->time = time();
		
		switch($obj->type)
		{
			case "GGL": // GoogleOTP
				$ga = new PHPGangsta_GoogleAuthenticator();
				$conf->otp_id = $ga->createSecret();
				$conf->use = "N";
				break;
				
			case "ATY": // Authy
				$this->authyCreateUser($obj->email, $obj->phone_number, $obj->country_code);
//				$authy_api = new Authy\AuthyApi($config->authy_api_key);
//				$user = $authy_api->registerUser($obj->email, $obj->phone_number, $obj->country_code);
//				if($user->ok()) {
//					$conf->otp_id = $user->id();
//					$conf->use = "N";
//				} else {
//					$error_str = "";
//					foreach($user->errors() as $field => $message) {
//						$error_str .= $field." ".$message;
//					}
//					return $this->createObject(-1, $error_str);
//				}
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
	function checkMemberConfig($member_srl, $type)
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

	/**
	 * @brief QR코드를 생성합니다
	 * @param $member_srl
	 * @param $key
	 * @return string
	 */
	function generateQRCode($member_srl, $key)
	{
		$ga = new PHPGangsta_GoogleAuthenticator();
		return $ga->getQRCodeGoogleUrl($member_srl, $key);
	}

	/**
	 * @brief 최우선 설정을 가져옵니다
	 * @param $member_srl
	 * @return MemberConfig|false
	 */
	function getPrimaryMemberConfig($member_srl)
	{
		$cond = new stdClass();
		$cond->member_srl = $member_srl;
		$cond->use = 'Y';
		$output = executeQuery('auth2fa.getPrimaryMemberConfigByMemberSrl', $cond);
		
		if(!$output->toBool()) return false;
		else if(!isset($output->data->otp_id) || empty($output->data->otp_id)) return false;
		else if(is_array($output->data)) return false;
		else return $output->data;
	}

	/**
	 * @brief OTP 번호를 체크합니다
	 * @param $member_srl
	 * @param $number
	 * @param $type
	 * @return bool
	 */
	function checkOTPNumber($member_srl,$number,$type)
	{
		$config = $this->getMemberConfig($member_srl, $type);
		$ga = new PHPGangsta_GoogleAuthenticator();
		return $ga->verifyCode($config->otp_id, $number, 2);
	}

	function checkUsedNumber($member_srl,$code) {
		// 5분전 입력한 인증코드 이후만 조회함
		$cond = new stdClass();
		$cond->member_srl = $member_srl;
		$cond->code = $code;
		$cond->issuccess = "Y";
		$cond->time = time() - 300;

		$output = executeQueryArray('auth2fa.getAuthLogByTime', $cond);
		if(!isset($output->data[0])) return TRUE;
		else return FALSE;
	}

	function insertAuthlog($member_srl,$type,$code,$issuccess) {
		if(!$this->checkMemberConfig($member_srl, $type)) return FALSE;

		$cond = new stdClass();
		$cond->member_srl = $member_srl;
		$cond->type = $type;
		$cond->code = $code;
		$cond->issuccess = $issuccess;
		$cond->time = time();
		$output = executeQuery('auth2fa.insertAuthLog', $cond);
		return $output;
	}
	
	function authyCreateUser($email, $phone_number, $country_code)
	{
		$config = $this->getConfig();
		$authy_base_url = $config->authy_base_url ?: "https://api.authy.com";
		$authy_api_key = $config->authy_api_key ?: '';
		$url = "/protected/json/users/new";

		$data = array(
			'user' => [
				"email"                     => $email,
				"cellphone"                 => $phone_number,
				"country_code"              => $country_code,
			],
			"send_install_link_via_sms" => true,
		);

		$header = array(
			'X-Authy-API-Key: '.$authy_api_key,
		);

		$json = $this->cUrlPost($authy_base_url.$url, $data, $header);
		die(print_r($json, true));
	}
	
	function authySendSMS()
	{
		
	}
	
	function authySendPush()
	{
		
	}
	
	function cUrlPost($url, $data = array(), $header = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}
	
	function authyRegisterUser($member_srl, $email, $phone_number, $country_code)
	{
		$obj = new stdClass();
		$obj->member_srl = $member_srl;
		$obj->type = "ATY";
		$obj->email = $email;
		$obj->phone_number = $phone_number;
		$obj->country_code = $country_code;
		return $this->insertNewConfig($obj);
	}
	
	function sendAuthMail()
	{
		
	}
}
