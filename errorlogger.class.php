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
class ErrorLogger extends ModuleObject
{
	/**
	 * 모듈 설정을 불러오는 메소드.
	 */
	public function getConfig()
	{
		// DB에서 모듈 설정을 불러온다.
		$config = getModel('module')->getModuleConfig('errorlogger');
		if (!is_object($config))
		{
			$config = new stdClass();
		}
		if (!isset($config->error_types))
		{
			$config->error_types = E_ERROR;
		}
		
		return $config;
	}
	
	/**
	 * 트리거를 등록한다.
	 */
	public function registerTriggers()
	{
		$oModuleModel = getModel('module');
		if(!$oModuleModel->getTrigger('moduleHandler.init', 'errorlogger', 'model', 'triggerSetErrorHandlers', 'before'))
		{
			$oModuleController = getController('module');
			$oModuleController->insertTrigger('moduleHandler.init', 'errorlogger', 'model', 'triggerSetErrorHandlers', 'before');
		}
	}
	
	/**
	 * 설치 메소드.
	 */
	public function moduleInstall()
	{
		$this->registerTriggers();
		return new Object();
	}
	
	/**
	 * 업데이트 확인 메소드.
	 */
	public function checkUpdate()
	{
		$oModuleModel = getModel('module');
		if(!$oModuleModel->getTrigger('moduleHandler.init', 'errorlogger', 'model', 'triggerSetErrorHandlers', 'before'))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 업데이트 메소드.
	 */
	public function moduleUpdate()
	{
		$this->registerTriggers();
		return new Object(0, 'success_updated');
	}
	
	/**
	 * 캐시 재생성 메소드.
	 */
	public function recompileCache()
	{
		$oAdminController = getAdminController('errorlogger');
		$oAdminController->procErrorLoggerAdminClearList(1);
	}
}
