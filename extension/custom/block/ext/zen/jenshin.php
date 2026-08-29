<?php
/**
 * Override welcome block: drop QA metrics, show medical-matter alerts.
 *
 * @access protected
 * @return void
 */
protected function printWelcomeBlock(): void
{
    $time = date('H:i');
    $welcomeType = '19:00';
    foreach($this->lang->block->welcomeList as $type => $name) $welcomeType = $time >= $type ? $type : $welcomeType;

    $usageDays = '';
    $dateUsed  = $this->loadModel('admin')->genDateUsed();
    if(!empty($dateUsed->year))  $usageDays .= $dateUsed->year  . ' ' . $this->lang->year  . ' ';
    if(!empty($dateUsed->month)) $usageDays .= $dateUsed->month . ' ' . $this->lang->month . ' ';
    if(!empty($dateUsed->day))   $usageDays .= $dateUsed->day   . ' ' . $this->lang->day   . ' ';
    if(!$usageDays) $usageDays = "0 {$this->lang->day}";
    if(strpos($this->app->getClientLang(), 'zh') !== false) $usageDays = str_replace(' ', '', $usageDays);

    $yesterday = strtotime('-1 day');
    $year  = date('Y', $yesterday);
    $month = date('m', $yesterday);
    $day   = date('d', $yesterday);

    $finishTask      = 0;
    $finishTaskGroup = $this->loadModel('metric')->getResultByCodeWithArray('count_of_daily_finished_task_in_user', array('user' => $this->app->user->account, 'year' => $year, 'month' => $month, 'day' => $day), 'cron', null, $this->config->vision);
    if(!empty($finishTaskGroup))
    {
        foreach($finishTaskGroup as $finishTaskData)
        {
            if($finishTaskData['user'] == $this->app->user->account && $year == $finishTaskData['year'] && $month == $finishTaskData['month'] && $day == $finishTaskData['day'])
            {
                $finishTask = $finishTaskData['value'];
                break;
            }
        }
    }

    $honorary = $finishTask ? zget($this->lang->block->honorary, 'task', '') : '';

    $alerts = $this->loadModel('jxcore')->getWelcomeAlerts($this->app->user->account);
    $jxDashModule  = !empty($this->config->jenshin->enableLegacyBizMenus) ? 'jxdashboard' : 'jxboard';
    $dashboardLink = common::hasPriv($jxDashModule, 'overview') ? helper::createLink($jxDashModule, 'overview') : '';
    $todoLink = '';
    if(common::hasPriv('my', 'calendar') && $this->config->vision != 'lite') $todoLink = helper::createLink('my', 'calendar');
    if(!$todoLink && common::hasPriv('my', 'todo') && $this->config->vision != 'lite') $todoLink = helper::createLink('my', 'todo');
    $workLink = '';
    if(common::hasPriv('my', 'work') && $this->config->vision != 'lite') $workLink = helper::createLink('my', 'work', 'mode=task&browseType=assignedTo');
    if(common::hasPriv('my', 'contribute') && $this->config->vision == 'lite') $workLink = helper::createLink('my', 'contribute', 'mode=task&browseType=assignedTo');

    $assignToMe = array();
    $assignToMe['task']         = array('number' => (int)zget($alerts, 'task', 0),          'href' => $todoLink);
    $assignToMe['pendingStage'] = array('number' => (int)zget($alerts, 'pendingStage', 0),  'href' => $workLink);
    $assignToMe['overdue']      = array('number' => (int)zget($alerts, 'overdue', 0),       'href' => $dashboardLink);
    $assignToMe['blocker']      = array('number' => (int)zget($alerts, 'blocker', 0),       'href' => $dashboardLink);

    $reviewList = $this->loadModel('my')->getReviewingList('all');
    $reviewByMe = array();
    $reviewByMe['reviewByMe'] = array(
        'number' => count($reviewList),
        'href'   => common::hasPriv('my', 'audit') && $this->config->vision != 'lite' ? helper::createLink('my', 'audit') : ''
    );

    $isEn = $this->app->getClientLang() == 'en';
    $yesterdaySummary = '';
    if($finishTask)
    {
        $comma = $isEn ? ' ' : $this->lang->comma;
        $yesterdaySummary .= sprintf($this->lang->block->summary->finishTask, $finishTask) . $comma;
    }
    else
    {
        $yesterdaySummary .= $this->lang->block->summary->noWork;
    }

    $alertCount = (int)zget($alerts, 'overdue', 0) + (int)zget($alerts, 'blocker', 0);
    if($alertCount && !empty($this->lang->block->summary->currentAlert)) $yesterdaySummary .= sprintf($this->lang->block->summary->currentAlert, $alertCount);

    if($isEn) $yesterdaySummary = $yesterdaySummary . ' ' . $this->lang->block->summary->yesterday;
    else $yesterdaySummary = $this->lang->block->summary->yesterday . $yesterdaySummary;

    $welcomeSummary = sprintf($this->lang->block->summary->welcome, $usageDays, $yesterdaySummary);

    $this->view->todaySummary   = date(DT_DATE3, time()) . ' ' . $this->lang->datepicker->dayNames[date('w', time())];
    $this->view->welcomeType    = $welcomeType;
    $this->view->usageDays      = $usageDays;
    $this->view->finishTask     = $finishTask;
    $this->view->fixBug         = 0;
    $this->view->honorary       = $honorary;
    $this->view->assignToMe     = $assignToMe;
    $this->view->reviewByMe     = $reviewByMe;
    $this->view->welcomeSummary = $welcomeSummary;
}

/**
 * 产品总览：用任务/项目度量替换 Bug、发布指标。
 *
 * @param  object $block
 * @param  array  $params
 * @access protected
 * @return void
 */
protected function printProductOverviewBlock(object $block, array $params = array()): void
{
    if($block->width == 1) $this->printShortProductOverview();
    if($block->width == 3) $this->printLongProductOverview($params);
    $this->jxReplaceProductOverviewMetrics((int)$block->width, $params);
}

/**
 * 产品月度推进：第三张图改为任务新增/完成。
 *
 * @access protected
 * @return void
 */
protected function printMonthlyProgressBlock()
{
    $years  = array();
    $months = array();
    $dates  = array();
    for($i = 5; $i >= 0; $i --)
    {
        $years[]  = date('Y',   strtotime("first day of -{$i} month"));
        $months[] = date('m',   strtotime("first day of -{$i} month"));
        $dates[]  = date('Y-m', strtotime("first day of -{$i} month"));
    }

    $this->loadModel('metric');
    $monthFinishedScale = $this->metric->getResultByCodeWithArray('scale_of_monthly_finished_story', array('year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $monthCreatedStory  = $this->metric->getResultByCodeWithArray('count_of_monthly_created_story',  array('year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $monthFinishedStory = $this->metric->getResultByCodeWithArray('count_of_monthly_finished_story', array('year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $monthCreatedTask   = $this->metric->getResultByCode('count_of_monthly_created_task',  array('year' => join(',', $years), 'month' => join(',', $months)), 'realtime');
    $monthFinishedTask  = $this->metric->getResultByCode('count_of_monthly_finished_task', array('year' => join(',', $years), 'month' => join(',', $months)), 'realtime');
    if(!is_array($monthCreatedTask))  $monthCreatedTask  = array();
    if(!is_array($monthFinishedTask)) $monthFinishedTask = array();

    foreach($dates as $date)
    {
        $doneStoryEstimate[$date] = 0;
        $doneStoryCount[$date]    = 0;
        $createStoryCount[$date]  = 0;
        $fixedBugCount[$date]     = 0;
        $createBugCount[$date]    = 0;

        if(!empty($monthFinishedScale))
        {
            foreach($monthFinishedScale as $scale)
            {
                if($date == "{$scale['year']}-{$scale['month']}") $doneStoryEstimate[$date] = $scale['value'];
            }
        }

        if(!empty($monthCreatedStory))
        {
            foreach($monthCreatedStory as $story)
            {
                if($date == "{$story['year']}-{$story['month']}") $doneStoryCount[$date] = $story['value'];
            }
        }

        if(!empty($monthFinishedStory))
        {
            foreach($monthFinishedStory as $story)
            {
                if($date == "{$story['year']}-{$story['month']}") $createStoryCount[$date] = $story['value'];
            }
        }

        if(!empty($monthCreatedTask))
        {
            foreach($monthCreatedTask as $task)
            {
                $task = (array)$task;
                if($date == "{$task['year']}-{$task['month']}") $createBugCount[$date] = zget($task, 'value', 0);
            }
        }

        if(!empty($monthFinishedTask))
        {
            foreach($monthFinishedTask as $task)
            {
                $task = (array)$task;
                if($date == "{$task['year']}-{$task['month']}") $fixedBugCount[$date] = zget($task, 'value', 0);
            }
        }
    }

    $this->view->doneStoryEstimate = $doneStoryEstimate;
    $this->view->doneStoryCount    = $doneStoryCount;
    $this->view->createStoryCount  = $createStoryCount;
    $this->view->fixedBugCount     = $fixedBugCount;
    $this->view->createBugCount    = $createBugCount;
}

/**
 * 读取实时度量值。
 *
 * @param  string $code
 * @param  array  $options
 * @param  string $year
 * @access protected
 * @return int
 */
protected function jxMetricValue(string $code, array $options = array(), string $year = ''): int
{
    $rows = $this->loadModel('metric')->getResultByCode($code, $options, 'realtime');
    if(empty($rows) || !is_array($rows)) return 0;

    if($year !== '')
    {
        foreach($rows as $row)
        {
            $row = (array)$row;
            if((string)zget($row, 'year', '') === $year) return (int)zget($row, 'value', 0);
        }
        return 0;
    }

    $first = (array)reset($rows);
    return (int)zget($first, 'value', 0);
}

/**
 * 替换产品总览中的 Bug / 发布数字。
 *
 * @param  int   $width
 * @param  array $params
 * @access protected
 * @return void
 */
protected function jxReplaceProductOverviewMetrics(int $width, array $params = array()): void
{
    $data = $this->view->data ?? null;
    if(!is_object($data)) return;

    if($width == 1)
    {
        $data->releaseCount   = $this->jxMetricValue('count_of_annual_finished_project', array('year' => date('Y')));
        $data->milestoneCount = $this->jxMetricValue('count_of_unfinished_task');
        return;
    }

    $year = isset($params['year']) ? (string)(int)$params['year'] : date('Y');
    $data->activeBugCount = $this->jxMetricValue('count_of_unfinished_task');
    $data->finishedReleaseCount['year'] = $this->jxMetricValue('count_of_annual_finished_project', array(), $year);
}
