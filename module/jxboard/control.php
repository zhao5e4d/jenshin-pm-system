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

    public function overview(string $dept = '', int $product = 0, string $status = '', string $health = '', string $period = 'month', string $begin = '', string $end = '', string $focus = '')
    {
        $this->renderBoard('overview', $dept, $product, $status, $health, $period, $begin, $end, $focus);
    }

    public function dept(string $dept = '', int $product = 0, string $status = '', string $health = '', string $period = 'month', string $begin = '', string $end = '', string $focus = '')
    {
        $this->renderBoard('dept', $dept, $product, $status, $health, $period, $begin, $end, $focus);
    }

    public function portfolio(string $dept = '', int $product = 0, string $status = '', string $health = '', string $period = 'month', string $begin = '', string $end = '', string $focus = '')
    {
        $this->renderBoard('portfolio', $dept, $product, $status, $health, $period, $begin, $end, $focus);
    }

    public function meeting(string $dept = '', int $product = 0, string $status = '', string $health = '', string $period = 'month', string $begin = '', string $end = '', string $focus = '', string $export = '')
    {
        if($export !== 'csv')
        {
            $exportParam = isset($_GET['export']) ? (string)$_GET['export'] : '';
            if($exportParam === '' && !empty($this->app->rawParams['export'])) $exportParam = (string)$this->app->rawParams['export'];
            $export = $exportParam;
        }
        if($export === 'csv')
        {
            $this->exportMeetingCsv($dept, $product, $status, $health, $period, $begin, $end, $focus);
            return;
        }
        $this->renderBoard('meeting', $dept, $product, $status, $health, $period, $begin, $end, $focus);
    }

    protected function filterQuery(array $filters): string
    {
        return sprintf(
            'dept=%s&product=%s&status=%s&health=%s&period=%s&begin=%s&end=%s&focus=%s',
            $filters['dept'],
            (int)$filters['product'],
            $filters['status'],
            $filters['health'],
            $filters['period'],
            $this->jxboard->compactDate((string)$filters['begin']),
            $this->jxboard->compactDate((string)$filters['end']),
            $filters['focus']
        );
    }

    protected function renderBoard(string $viewName, string $dept, int $product, string $status, string $health = '', string $period = 'month', string $begin = '', string $end = '', string $focus = '')
    {
        $filters = $this->jxboard->normalizeFilters(array(
            'dept'    => $dept,
            'product' => $product,
            'status'  => $status,
            'health'  => $health,
            'period'  => $period,
            'begin'   => $begin,
            'end'     => $end,
            'focus'   => $focus
        ));
        $board = $this->jxboard->getBoard($filters);
        $query = $this->filterQuery($filters);

        $statusItems = array('' => $this->lang->jxboard->all);
        foreach($this->lang->project->statusList as $key => $label)
        {
            if($key === '' || $key === 'delay') continue;
            $statusItems[$key] = $label;
        }

        $healthItems = array('' => $this->lang->jxboard->all) + $this->lang->jxboard->healthList;
        $periodItems = array(
            'week'     => $this->lang->jxboard->periodWeek,
            'month'    => $this->lang->jxboard->periodMonth,
            'halfyear' => $this->lang->jxboard->periodHalfyear,
            'custom'   => $this->lang->jxboard->periodCustom
        );

        foreach(array('overview', 'dept', 'portfolio', 'meeting') as $method)
        {
            $this->lang->jxboard->menu->{$method}['link'] = "{$this->lang->jxboard->{$method}}|jxboard|{$method}|{$query}";
        }

        $this->view->title       = $this->lang->jxboard->{$viewName};
        $this->view->viewName    = $viewName;
        $this->view->board       = $board;
        $this->view->filters     = $filters;
        $this->view->depts       = $this->jxboard->getDeptPairs();
        $this->view->products    = $this->jxboard->getProductPairs();
        $this->view->statusItems = $statusItems;
        $this->view->healthItems = $healthItems;
        $this->view->periodItems = $periodItems;
        $this->display('jxboard', 'board');
    }

    protected function exportMeetingCsv(string $dept, int $product, string $status, string $health, string $period, string $begin, string $end, string $focus): void
    {
        $filters = $this->jxboard->normalizeFilters(array(
            'dept'      => $dept,
            'product'   => $product,
            'status'    => $status,
            'health'    => $health,
            'period'    => $period,
            'begin'     => $begin,
            'end'       => $end,
            'focus'     => $focus,
            'expandAll' => true
        ));
        $board = $this->jxboard->getBoard($filters);
        $lang  = $this->lang->jxboard;
        $lines = array();

        $lines[] = $lang->overdueTasks;
        $lines[] = $this->csvLine(array($lang->colName, $lang->colProject, $lang->colAssignee, $lang->colDeadline, $lang->colOverdueDays));
        foreach($board->overdueTasks as $item)
        {
            $lines[] = $this->csvLine(array($item->name, $item->projectName, $item->assignedName, $item->deadline, $item->overdueDays));
        }
        $lines[] = '';

        $lines[] = $lang->dueExecutions;
        $lines[] = $this->csvLine(array($lang->colExecution, $lang->colProject, $lang->colEnd, $lang->colDaysLeft));
        foreach($board->dueExecutions as $item)
        {
            $lines[] = $this->csvLine(array($item->name, $item->projectName, $item->end, $item->daysLeft));
        }
        $lines[] = '';

        $lines[] = $lang->riskProjects;
        $lines[] = $this->csvLine(array($lang->colProject, $lang->colProduct, $lang->colPM, $lang->colDept, $lang->colHealth, $lang->colBlocker, $lang->colEnd));
        foreach($board->riskProjects as $item)
        {
            $healthLabel = $lang->healthList[$item->health] ?? $item->health;
            $lines[] = $this->csvLine(array($item->name, $item->productNames, $item->pmName, $item->deptName, $healthLabel, $item->blocker, $item->end));
        }
        $lines[] = '';

        $lines[] = $lang->certs;
        $lines[] = $this->csvLine(array($lang->colProduct, $lang->colCertNo, $lang->colValidTo, $lang->colDaysLeft));
        foreach($board->certExpiring as $item)
        {
            $lines[] = $this->csvLine(array($item->productName, $item->certNo, $item->certValidTo, $item->daysLeft));
        }

        $csv = "\xEF\xBB\xBF" . implode("\r\n", $lines);
        $name = 'jxboard-meeting-' . date('Ymd') . '.csv';
        while(ob_get_level()) ob_end_clean();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Cache-Control: no-store');
        echo $csv;
        exit;
    }

    protected function csvLine(array $cols): string
    {
        $out = array();
        foreach($cols as $col)
        {
            $out[] = '"' . str_replace('"', '""', (string)$col) . '"';
        }
        return implode(',', $out);
    }
}
