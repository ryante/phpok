<?php

/**
 * 簡訊訊息
 * @param templateId 模板ID
 * @param msgType 0表示簡訊, 1表示彩信, 預設值為0
 * @param phone 收信人手機號,多個手機號用逗號,分隔, 號碼最多不能超過100
 * @param vars 替換變數的json串
 * @param 簽名, 合法性驗證
 * @author xjm
 *
 */
class SmsMsg implements JsonSerializable {
	private $template_id;
	private $msg_type;
	/**
	 *
	 * @var array
	 */
	private $phone;
	/**
	 *
	 * @var map
	 */
	private $vars;
	private $signature;
	private $timestamp;
	public function setTemplateId($template_id) {
		$this->template_id = $template_id;
	}
	public function getTemplateId() {
		return $this->template_id;
	}
	public function setMsgType($msgType) {
		$this->msg_type = $msgType;
	}
	public function getMsgType() {
		return $this->msg_type;
	}
	/* phone array(1,2,3) */
	public function addPhoneList($phone) {
		if (! is_array ( $phone )) {
			array_push ( $this->phone, $phone );
		} else {
			foreach ( $phone as $key => $value ) {
				$this->phone [] = $value;
			}
		}
	}
	public function getPhoneList() {
		return $this->phone;
	}
	public function addVars($key, $value) {
		$this->vars [$key] = $value;
	}
	public function addMapVars($map) {
		foreach ( $map as $key => $value ) {
			$this->vars [$key] = $value;
		}
	}
	public function getVars() {
		return $this->vars;
	}
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}
	public function getTimestamp() {
		return $this->timestamp;
	}
	public function setSignature($signature) {
		$this->setSignature ( $signature );
	}
	public function getSignature() {
		return $this->signature;
	}
	public function jsonSerialize() {
		return array_filter ( [ 
				'templateId' => $this->getTemplateId (),
				'msgType' => $this->getMsgType (),
				'phone' => $this->getPhoneList (),
				'vars' => $this->getVars (),
				'signature' => $this->getSignature () 
		] );
	}
}

/**
 * 語音訊息
 *
 * @param
 *        	phone 收信人手機號
 * @param
 *        	code 驗證碼
 * @param
 *        	signature 簽名, 合法性驗證
 * @param
 *        	timestamp 時間戳
 * @author xjm
 *        
 */
class VoiceMsg implements JsonSerializable {
	private $phone;
	private $code;
	private $signature;
	private $timestamp;
	public function setPhone($phone) {
		$this->phone = $phone;
	}
	public function getPhone() {
		return $this->phone;
	}
	public function setCode($code) {
		$this->code = $code;
	}
	public function getCode() {
		return $this->code;
	}
	public function setSignature($signature) {
		$this->signature = $signature;
	}
	public function getSignature() {
		return $this->signature;
	}
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}
	public function getTimestamp() {
		return $this->timestamp;
	}
	public function jsonSerialize() {
		return array_filter ( [ 
				'phone' => $this->getPhone (),
				'code' => $this->getCode (),
				'signature' => $this->getSignature (),
				'timestamp' => $this->getTimestamp () 
		] );
	}
}
class MsgType {
	const SMS = 0;
	
	/**
	 * 1 彩信
	 */
	const MMS = 1;
	
	/**
	 * 2國際簡訊
	 */
	const INTERNAT_SMS = 2;
	
	/**
	 * 3語音
	 */
	const VOICE = 3;
}
