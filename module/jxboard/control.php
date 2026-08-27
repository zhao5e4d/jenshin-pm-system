<?php
declare(strict_types=1);
class jxboard extends control
{
    public function __construct()
    {
        parent::__construct();
        $this->app->loadLang('project');
        $this->app->loadLang('task');
    }

    public function index(string $view = 'overview')
    {
        $this->locate($this->createLink('jxboard', $view == 'index' ? 'overview' : $view));
    }

    public function overview(string $dept = '', int $product = 0, string $status = '')
    {
        $this->renderBoard('overview', $dept, $product, $status);
    }

    public function dept(string $dept = '', int $product = 0, string $status = '')
    {
        $this->renderBoard('dept', $dept, $product, $status);
    }

    public function portfolio(string $dept = '', int $product = 0, string $status = '')
    {
        $this->renderBoard('portfolio', $dept, $product, $status);
    }

    public function meeting(string $dept = '', int $product = 0, string $status = '')
    {
        $this->renderBoard('meeting', $dept, $product, $status);
    }

    protected function renderBoard(string $viewName, string $dept, int $product, string $status)
    {
        $filters = array('dept' => $dept, 'product' => $product, 'status' => $status);
        $board   = $this->jxboard->getBoard($filters);

        $statusItems = array('' => $this->lang->jxboard->all);
        foreach($this->lang->project->statusList as $key => $label)
        {
            if($key === '' || $key === 'delay') continue;
            $statusItems[$key] = $label;
        }

        foreach(array('overview', 'dept', 'portfolio', 'meeting') as $method)
        {
            $this->lang->jxboard->menu->{$method}['link'] = "{$this->lang->jxboard->{$method}}|jxboard|{$method}|dept={$dept}&product={$product}&status={$status}";
        }

        $this->view->title       = $this->lang->jxboard->{$viewName};
        $this->view->viewName    = $viewName;
        $this->view->board       = $board;
        $this->view->filters     = $filters;
        $this->view->depts       = $this->jxboard->getDeptPairs();
        $this->view->products    = $this->jxboard->getProductPairs();
        $this->view->statusItems = $statusItems;
        $this->display('jxboard', 'board');
    }
}
