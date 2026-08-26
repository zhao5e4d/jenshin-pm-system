<?php
declare(strict_types=1);
class jxdashboard extends control
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('jxcore')->ensureSchema();
        $this->app->loadLang('jxcore');
        $this->app->loadLang('jxregistration');
        $this->app->loadLang('jxmarketaccess');
        $this->app->loadLang('jxadmission');
    }

    public function index(string $view = 'overview')
    {
        $this->locate($this->createLink('jxdashboard', $view == 'index' ? 'overview' : $view));
    }

    public function overview(string $bizType = '', string $dept = '', string $status = '')
    {
        $this->renderBoard('overview', $bizType, $dept, $status);
    }

    public function dept(string $bizType = '', string $dept = '', string $status = '')
    {
        $this->renderBoard('dept', $bizType, $dept, $status);
    }

    public function portfolio(string $bizType = '', string $dept = '', string $status = '')
    {
        $this->renderBoard('portfolio', $bizType, $dept, $status);
    }

    public function meeting(string $bizType = '', string $dept = '', string $status = '')
    {
        $this->renderBoard('meeting', $bizType, $dept, $status);
    }

    protected function renderBoard(string $viewName, string $bizType, string $dept, string $status)
    {
        $filters = array('bizType' => $bizType, 'dept' => $dept, 'status' => $status);
        $board   = $this->jxcore->getDashboard($filters);
        $this->view->title     = $this->lang->jxdashboard->{$viewName};
        $this->view->viewName  = $viewName;
        $this->view->board     = $board;
        $this->view->filters   = $filters;
        $this->view->depts     = $this->jxcore->getDeptNamePairs();
        $this->view->bizTypes  = $this->lang->jxdashboard->bizTypeList;
        $this->display('jxdashboard', 'board');
    }
}
