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
class ErrorLoggerAdminController extends ErrorLogger
{
	/**
	 * 모듈 설정을 저장하는 메소드.
	 */
	public function procErrorLoggerAdminInsertConfig()
	{
		// 기존 설정을 가져온다.
		$config = $this->getConfig();
		
		// 새로 저장하려는 설정을 가져온다.
		$request_args = Context::getRequestVars();
		$config->error_types = 0;
		if ($request_args->error_type_fatal === 'Y')
		{
			$config->error_types = $config->error_types | E_ERROR;
		}
		if ($request_args->error_type_warning === 'Y')
		{
			$config->error_types = $config->error_types | E_WARNING;
		}
		if ($request_args->error_type_notice === 'Y')
		{
			$config->error_types = $config->error_types | E_NOTICE;
		}
		
		// 새 모듈 설정을 저장한다.
		$oModuleController = getController('module');
		$output = $oModuleController->insertModuleConfig('errorlogger', $config);
		if ($output->toBool())
		{
			$this->setMessage('success_registed');
		}
		else
		{
			return $output;
		}
		
		if (Context::get('success_return_url'))
		{
			$this->setRedirectUrl(Context::get('success_return_url'));
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispErrorloggerAdminConfig'));
		}
	}
	
	/**
	 * 일정 기간 이상 지난 에러 기록을 삭제하는 메소드.
	 */
	public function procErrorLoggerAdminClearList($threshold = null)
	{
		// 정리 설정을 가져온다.
		if ($threshold === null)
		{
			$request_vars = Context::getRequestVars();
			$threshold = intval($request_vars->clear_threshold);
		}
		
		// 정리한다.
		if ($threshold >= 0)
		{
			$obj = new stdClass();
			$obj->threshold = date('YmdHis', time() - ($threshold * 86400) + zgap());
			$output = executeQuery('errorlogger.deleteErrorLog', $obj);
		}
		
		// 목록 페이지로 돌려보낸다.
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispErrorloggerAdminList'));
		return;
	}
}
