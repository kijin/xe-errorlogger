<?php

/**
 * 에러 기록 모듈
 * 
 * Copyright (c) 2015, Kijin Sung <kijin@kijinsung.com>
 * 
 * 이 프로그램은 자유 소프트웨어입니다. 소프트웨어의 피양도자는 자유 소프트웨어
 * 재단이 공표한 GNU 일반 공중 사용 허가서 2판 또는 그 이후 판을 임의로
 * 선택해서, 그 규정에 따라 프로그램을 개작하거나 재배포할 수 있습니다.
 *
 * 이 프로그램은 유용하게 사용될 수 있으리라는 희망에서 배포되고 있지만,
 * 특정한 목적에 맞는 적합성 여부나 판매용으로 사용할 수 있으리라는
 * 묵시적인 보증을 포함한 어떠한 형태의 보증도 제공하지 않습니다.
 * 보다 자세한 사항에 대해서는 GNU 일반 공중 사용 허가서를 참고하시기 바랍니다.
 *
 * GNU 일반 공중 사용 허가서는 이 프로그램과 함께 제공됩니다.
 * 만약, 이 문서가 누락되어 있다면 자유 소프트웨어 재단으로 문의하시기 바랍니다.
 */
class ErrorLoggerModel extends ErrorLogger
{
	/**
	 * 모듈 설정을 여러 번 불러오지 않도록 캐싱해 두는 변수.
	 */
	protected static $config = null;
	protected static $reqid = null;
	
	/**
	 * 생성자.
	 */
	public function __construct()
	{
		if (self::$config === null)
		{
			self::$config = $this->getConfig();
		}
		if (self::$reqid === null)
		{
			$random_seed = $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REQUEST_URI'] . mt_rand() . mt_rand();
			self::$reqid = date('YmdHis', time() + zgap()) . '-' . substr(base64_encode(sha1($random_seed, true)), 0, 25);
		}
	}
	
	/**
	 * 에러 핸들러를 등록한다.
	 */
	public function triggerSetErrorHandlers()
	{
		if (self::$config->error_types)
		{
			set_error_handler(array($this, 'errorHandler'), intval(self::$config->error_types));
		}
		if (self::$config->error_types & E_ERROR)
		{
			register_shutdown_function(array($this, 'shutdownHandler'));
		}
	}
	
	/**
	 * 에러를 DB에 기록하는 메소드.
	 */
	public function logErrorToDB($error_type, $error_file, $error_line, $error_message)
	{
		$obj = new stdClass();
		$obj->error_request_id = self::$reqid;
		$obj->error_type = self::getErrorType($error_type);
		$obj->error_ipaddress = strval($_SERVER['REMOTE_ADDR']);
		$obj->error_module = strval(Context::get('module'));
		$obj->error_act = strval(Context::get('act'));
		$obj->error_file = strval($error_file);
		$obj->error_line = intval($error_line);
		$obj->error_message = trim($error_message);
		executeQuery('errorlogger.insertErrorLog', $obj);
	}
	
	/**
	 * 더미 에러 핸들러.
	 */
	public function dummyHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		
	}
	
	/**
	 * 일반 에러 핸들러.
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		// 기록할 필요가 없는 에러는 무시한다.
		if (($errno & self::$config->error_types) == 0 || error_reporting() == 0)
		{
			return false;
		}
		
		// 에러를 기록한다.
		set_error_handler(array($this, 'dummyHandler'), ~0);
		$this->logErrorToDB($errno, $errfile, $errline, $errstr);
		restore_error_handler();
		
		// 기본 에러 핸들러의 작동을 방해하지 않도록 false를 반환한다.
		return false;
	}
	
	/**
	 * 치명적인 오류로 실행이 종료되는 경우를 기록하는 메소드.
	 */
	public function shutdownHandler()
	{
		// 정상 종료되는 경우는 무시한다.
		$errinfo = error_get_last();
		if ($errinfo === null || ($errinfo['type'] != 1 && $errinfo['type'] != 4))
		{
			return false;
		}
		
		// 에러를 기록한다.
		set_error_handler(array($this, 'dummyHandler'), ~0);
		$this->logErrorToDB($errinfo['type'], $errinfo['file'], $errinfo['line'], $errinfo['message']);
		restore_error_handler();
		
		// 백지현상이 발생하지 않도록 간단한 메시지를 출력한다.
		while (ob_get_level()) ob_end_clean();
		if (Context::getInstance()->request_method === 'JSON')
		{
			echo json_encode(array(
				'error' => -1,
				'message' => 'Fatal Error: ' . $errinfo['message'],
				'message_type' => '',
			));
		}
		else
		{
			include dirname(__FILE__) . '/tpl/error.html';
		}
	}
	
	/**
	 * 에러 번호를 에러 종류로 변환하는 메소드.
	 */
	public static function getErrorType($errno)
	{
		switch ($errno)
		{
			case E_ERROR: return 'Fatal Error';
			case E_WARNING: return 'Warning';
			case E_NOTICE: return 'Notice';
			case E_CORE_ERROR: return 'Core Error';
			case E_CORE_WARNING: return 'Core Warning';
			case E_COMPILE_ERROR: return 'Compile Error';
			case E_COMPILE_WARNING: return 'Compile Warning';
			case E_USER_ERROR: return 'User Error';
			case E_USER_WARNING: return 'User Warning';
			case E_USER_NOTICE: return 'User Notice';
			case E_STRICT: return 'Strict Standards';
			case E_PARSE: return 'Parse Error';
			case E_DEPRECATED: return 'Deprecated';
			case E_USER_DEPRECATED: return 'User Deprecated';
			case E_RECOVERABLE_ERROR: return 'Catchable Fatal Error';
			default: return 'Error';
		}
	}
}
