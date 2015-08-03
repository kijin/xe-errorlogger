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
class ErrorLoggerAdminView extends ErrorLogger
{
	/**
	 * 모듈 설정 화면을 표시하는 메소드.
	 */
	public function dispErrorLoggerAdminConfig()
	{
		// 현재 설정을 불러온다.
		Context::set('elconfig', $this->getConfig());
		Context::get('elconfig');
		
		// 템플릿을 지정한다.
		$this->setTemplatePath($this->module_path.'tpl');
		$this->setTemplateFile('config');
	}
	
	/**
	 * 기록한 에러 목록을 표시하는 메소드.
	 */
	public function dispErrorLoggerAdminList()
	{
		// 현재 설정을 불러온다.
		Context::set('elconfig', $this->getConfig());
		
		// 에러 목록을 불러온다.
		$obj = new stdClass();
		$elcount = executeQuery('errorlogger.countErrorLog', $obj);
		$elcount = $elcount->toBool() ? $elcount->data->count : 0;
		$obj->page = $page = Context::get('page') ? Context::get('page') : 1;
		$ellog = executeQuery('errorlogger.getErrorLog', $obj);
		$ellog = $ellog->toBool() ? $ellog->data : array();
		Context::set('elcount', $elcount);
		Context::set('ellog', $ellog);
		
		// 페이징을 처리한다.
		$paging = new Object();
		$paging->total_count = $elcount;
		$paging->total_page = max(1, ceil($elcount / 20));
		$paging->page = $page;
		$paging->page_navigation = new PageHandler($paging->total_count, $paging->total_page, $page, 10);
		Context::set('paging', $paging);
		Context::set('page', $page);
		
		// 템플릿을 지정한다.
		$this->setTemplatePath($this->module_path.'tpl');
		$this->setTemplateFile('list');
	}
}
