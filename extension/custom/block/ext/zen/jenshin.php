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
 * 单个产品月度推进：第三张图改为该产品任务新增/完成，不读 Bug 度量。
 *
 * @access protected
 * @return void
 */
protected function printSingleMonthlyProgressBlock(): void
{
    $productID = (int)$this->session->product;

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
    $monthStroyScaleGroup     = $this->metric->getResultByCodeWithArray('scale_of_monthly_finished_story_in_product',  array('product' => $productID, 'year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $monthCreatedStroyGroup   = $this->metric->getResultByCodeWithArray('count_of_monthly_created_story_in_product',   array('product' => $productID, 'year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $monthFinishedStoryGroup  = $this->metric->getResultByCodeWithArray('count_of_monthly_finished_story_in_product',  array('product' => $productID, 'year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $monthCreatedReleaseGroup = $this->metric->getResultByCodeWithArray('count_of_monthly_created_release_in_product', array('product' => $productID, 'year' => join(',', $years), 'month' => join(',', $months)), 'cron');

    $productIds      = $productID ? array($productID) : array();
    $createdMonthly  = $this->jxCountTasksByProductMonthly($productIds, 'created', $dates);
    $finishedMonthly = $this->jxCountTasksByProductMonthly($productIds, 'finished', $dates);

    $doneStoryEstimate = array();
    $doneStoryCount    = array();
    $createStoryCount  = array();
    $fixedBugCount     = array();
    $createBugCount    = array();
    $releaseCount      = array();
    foreach($dates as $date)
    {
        $doneStoryEstimate[$date] = 0;
        $doneStoryCount[$date]    = 0;
        $createStoryCount[$date]  = 0;
        $fixedBugCount[$date]     = (int)($finishedMonthly[$productID][$date] ?? 0);
        $createBugCount[$date]    = (int)($createdMonthly[$productID][$date] ?? 0);
        $releaseCount[$date]      = 0;

        if(!empty($monthStroyScaleGroup))
        {
            foreach($monthStroyScaleGroup as $data)
            {
                if($date == "{$data['year']}-{$data['month']}") $doneStoryEstimate[$date] = $data['value'];
            }
        }
        if(!empty($monthCreatedStroyGroup))
        {
            foreach($monthCreatedStroyGroup as $data)
            {
                if($date == "{$data['year']}-{$data['month']}") $createStoryCount[$date] = $data['value'];
            }
        }
        if(!empty($monthFinishedStoryGroup))
        {
            foreach($monthFinishedStoryGroup as $data)
            {
                if($date == "{$data['year']}-{$data['month']}") $doneStoryCount[$date] = $data['value'];
            }
        }
        if(!empty($monthCreatedReleaseGroup))
        {
            foreach($monthCreatedReleaseGroup as $data)
            {
                if($date == "{$data['year']}-{$data['month']}") $releaseCount[$date] = $data['value'];
            }
        }
    }

    $this->view->months            = array();
    $this->view->doneStoryEstimate = $doneStoryEstimate;
    $this->view->doneStoryCount    = $doneStoryCount;
    $this->view->createStoryCount  = $createStoryCount;
    $this->view->fixedBugCount     = $fixedBugCount;
    $this->view->createBugCount    = $createBugCount;
    $this->view->releaseCount      = $releaseCount;
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

    $productIds      = array_keys($this->loadModel('product')->getPairs());
    $unfinishedTotal = (int)array_sum($this->jxCountTasksByProduct($productIds, 'unfinished'));

    if($width == 1)
    {
        $data->releaseCount   = $this->jxMetricValue('count_of_annual_finished_project', array('year' => date('Y')));
        $data->milestoneCount = $unfinishedTotal;
        return;
    }

    $year = isset($params['year']) ? (string)(int)$params['year'] : date('Y');
    $data->activeBugCount = $unfinishedTotal;
    $data->finishedReleaseCount['year'] = $this->jxMetricValue('count_of_annual_finished_project', array(), $year);
}

/**
 * 团队成就：用「新增任务」替换测试用例指标。
 *
 * @access protected
 * @return void
 */
protected function printTeamAchievementBlock()
{
    $years  = array();
    $months = array();
    for($i = 0; $i <= 1; $i ++)
    {
        $years[]  = date('Y', strtotime("-{$i} day"));
        $months[] = date('m', strtotime("-{$i} day"));
    }

    $this->loadModel('metric');
    $finishedTaskGroup = $this->metric->getResultByCodeWithArray('count_of_daily_finished_task', array('year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $createdStoryGroup = $this->metric->getResultByCodeWithArray('count_of_daily_created_story', array('year' => join(',', $years), 'month' => join(',', $months)), 'cron');
    $consumedGroup     = $this->metric->getResultByCodeWithArray('hour_of_daily_effort',         array('year' => join(',', $years), 'month' => join(',', $months)), 'cron');

    $today     = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $finishedTasks  = $this->jxDailyMetricOn($finishedTaskGroup, $today);
    $yesterdayTasks = $this->jxDailyMetricOn($finishedTaskGroup, $yesterday);

    $createdStories   = $this->jxDailyMetricOn($createdStoryGroup, $today);
    $yesterdayStories = $this->jxDailyMetricOn($createdStoryGroup, $yesterday);

    $createdTasks          = $this->jxCountTasksOpenedOn($today);
    $yesterdayCreatedTasks = $this->jxCountTasksOpenedOn($yesterday);

    $consumedHours  = $this->jxDailyMetricOn($consumedGroup, $today);
    $yesterdayHours = $this->jxDailyMetricOn($consumedGroup, $yesterday);

    $this->view->finishedTasks         = $finishedTasks;
    $this->view->yesterdayTasks        = $yesterdayTasks;
    $this->view->createdStories        = $createdStories;
    $this->view->yesterdayStories      = $yesterdayStories;
    $this->view->createdTasks          = $createdTasks;
    $this->view->yesterdayCreatedTasks = $yesterdayCreatedTasks;
    $this->view->runCases              = $createdTasks;
    $this->view->yesterdayCases        = $yesterdayCreatedTasks;
    $this->view->consumedHours         = $consumedHours;
    $this->view->yesterdayHours        = $yesterdayHours;
}

/**
 * 项目列表：补上逾期任务数。
 *
 * @param  object $block
 * @access protected
 * @return void
 */
protected function printProjectBlock(object $block): void
{
    $this->app->loadLang('execution');
    $this->app->loadLang('task');
    $count   = isset($block->params->count)   ? $block->params->count   : 15;
    $type    = isset($block->params->type)    ? $block->params->type    : 'all';
    $orderBy = isset($block->params->orderBy) ? $block->params->orderBy : 'id_desc';

    $projects = $this->loadModel('project')->getOverviewList($type, 0, $orderBy, $count);
    $this->jxAttachOverdueTasks($projects);
    $this->view->projects = $projects;
    $this->view->users    = $this->loadModel('user')->getPairs('noletter', '', 0, array_unique(array_column($projects, 'PM')));
}

/**
 * 从按日度量结果中取某一天的值。
 *
 * @param  mixed  $group
 * @param  string $day
 * @access protected
 * @return int
 */
protected function jxDailyMetricOn($group, string $day): int
{
    if(empty($group) || !is_array($group)) return 0;
    foreach($group as $data)
    {
        $data = (array)$data;
        if("{$data['year']}-{$data['month']}-{$data['day']}" === $day) return (int)zget($data, 'value', 0);
    }
    return 0;
}

/**
 * 统计某日新建的任务数（与度量 getTasks 口径一致）。
 *
 * @param  string $day
 * @access protected
 * @return int
 */
protected function jxCountTasksOpenedOn(string $day): int
{
    $nextDay = date('Y-m-d', strtotime($day . ' +1 day'));
    $vision  = $this->config->vision;
    return (int)$this->dao->select('COUNT(t1.id) AS value')->from(TABLE_TASK)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.execution = t2.id')
        ->leftJoin(TABLE_PROJECT)->alias('t3')->on('t2.project = t3.id')
        ->where('t2.type')->in('sprint,kanban,stage')
        ->andWhere('t1.deleted')->eq('0')
        ->andWhere('t2.deleted')->eq('0')
        ->andWhere('t3.deleted')->eq('0')
        ->andWhere('t1.openedDate')->ge($day . ' 00:00:00')
        ->andWhere('t1.openedDate')->lt($nextDay . ' 00:00:00')
        ->andWhere("t1.vision LIKE '%{$vision}%'", true)
        ->orWhere('t1.vision IS NULL')->markRight(1)
        ->fetch('value');
}

/**
 * 产品年度工作量：第三列改为各产品今年完成任务数。
 *
 * @access protected
 * @return void
 */
protected function printAnnualWorkloadBlock()
{
    $products      = $this->loadModel('product')->getPairs();
    $productIdList = array_keys($products);

    $this->loadModel('metric');
    $finishEstimateGroup = $this->metric->getResultByCodeWithArray('scale_of_annual_finished_story_in_product', array('product' => join(',', $productIdList), 'year' => date('Y')), 'cron');
    $doneStoryGroup      = $this->metric->getResultByCodeWithArray('count_of_annual_finished_story_in_product', array('product' => join(',', $productIdList), 'year' => date('Y')), 'cron');
    $finishedTaskGroup   = $this->jxCountTasksByProduct($productIdList, 'finishedYear');

    if(!empty($finishEstimateGroup)) $finishEstimateGroup = array_column($finishEstimateGroup, null, 'product');
    if(!empty($doneStoryGroup))      $doneStoryGroup      = array_column($doneStoryGroup,      null, 'product');

    $doneStoryEstimate = array();
    $doneStoryCount    = array();
    $resolvedBugCount  = array();
    foreach($products as $productID => $productName)
    {
        $doneStoryEstimate[$productID] = isset($finishEstimateGroup[$productID]['value']) ? $finishEstimateGroup[$productID]['value'] : 0;
        $doneStoryCount[$productID]    = isset($doneStoryGroup[$productID]['value'])      ? $doneStoryGroup[$productID]['value']      : 0;
        $resolvedBugCount[$productID]  = (int)($finishedTaskGroup[$productID] ?? 0);
    }

    arsort($doneStoryEstimate);
    arsort($doneStoryCount);
    arsort($resolvedBugCount);

    $this->view->products          = $products;
    $this->view->doneStoryEstimate = $doneStoryEstimate;
    $this->view->doneStoryCount    = $doneStoryCount;
    $this->view->resolvedBugCount  = $resolvedBugCount;
    $this->view->maxStoryEstimate  = !empty($doneStoryEstimate) ? max($doneStoryEstimate) : 0;
    $this->view->maxStoryCount     = !empty($doneStoryCount)    ? max($doneStoryCount)    : 0;
    $this->view->maxBugCount       = !empty($resolvedBugCount)  ? max($resolvedBugCount)  : 0;
}

/**
 * 未关闭产品列表：激活Bug列改为未完成任务数。
 *
 * @param  object $block
 * @access protected
 * @return void
 */
protected function printProductListBlock(object $block): void
{
    $this->app->loadClass('pager', true);
    $count = isset($block->params->count) ? (int)$block->params->count : 0;
    $type  = isset($block->params->type) ? $block->params->type : '';
    $pager = pager::init(0, $count, 1);

    $products     = $this->loadModel('product')->getList(0, $type);
    $productStats = $this->product->getStats(array_keys($products), 'order_desc', $this->viewType != 'json' ? $pager : '');
    $this->jxAttachUnfinishedTasks($productStats);

    $this->view->productStats = $productStats;
    $this->view->users        = $this->loadModel('user')->getPairs('noletter');
    $this->view->avatarList   = $this->user->getAvatarPairs();
}

/**
 * 按产品统计任务数（走项目-产品关联，口径对齐度量 getTasks）。
 *
 * @param  array  $productIdList
 * @param  string $kind          unfinished|finished|finishedYear
 * @access protected
 * @return array
 */
protected function jxCountTasksByProduct(array $productIdList, string $kind): array
{
    if(empty($productIdList)) return array();

    $vision = $this->config->vision;
    $stmt   = $this->dao->select('t4.product AS product, COUNT(DISTINCT t1.id) AS value')->from(TABLE_TASK)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.execution = t2.id')
        ->leftJoin(TABLE_PROJECT)->alias('t3')->on('t2.project = t3.id')
        ->leftJoin(TABLE_PROJECTPRODUCT)->alias('t4')->on('t3.id = t4.project')
        ->where('t2.type')->in('sprint,kanban,stage')
        ->andWhere('t1.deleted')->eq('0')
        ->andWhere('t2.deleted')->eq('0')
        ->andWhere('t3.deleted')->eq('0')
        ->andWhere('t1.isParent')->eq('0')
        ->andWhere('t4.product')->in($productIdList);

    if($kind === 'finishedYear' || $kind === 'finished')
    {
        if($kind === 'finishedYear')
        {
            $year = date('Y');
            $stmt->andWhere('t1.finishedDate')->ge("{$year}-01-01")
                ->andWhere('t1.finishedDate')->lt(((int)$year + 1) . '-01-01');
        }
        $stmt->andWhere('t1.status', true)->eq('done')
            ->orWhere('t1.status')->eq('closed')->andWhere('t1.closedReason')->eq('done')->markRight(1);
    }
    else
    {
        $stmt->andWhere('t1.status')->notin('done,closed,cancel');
    }

    return $stmt->andWhere("t1.vision LIKE '%{$vision}%'", true)
        ->orWhere('t1.vision IS NULL')->markRight(1)
        ->groupBy('t4.product')
        ->fetchPairs('product', 'value');
}

/**
 * 把未完成任务数写入产品列表的原 Bug 列。
 *
 * @param  array $productStats
 * @access protected
 * @return void
 */
protected function jxAttachUnfinishedTasks(array $productStats): void
{
    if(empty($productStats)) return;

    $counts = $this->jxCountTasksByProduct(array_keys($productStats), 'unfinished');
    foreach($productStats as $productID => $product)
    {
        $product->unresolvedBugs = (int)($counts[$productID] ?? 0);
    }
}

/**
 * 产品 Bug 统计：改为按产品统计任务完成率与近 6 个月新增/完成，不读 Bug 表。
 *
 * @param  object $block
 * @param  array  $params
 * @access protected
 * @return void
 */
protected function printBugStatisticBlock(object $block, array $params = array())
{
    $status    = isset($block->params->type)  ? $block->params->type  : '';
    $count     = isset($block->params->count) ? $block->params->count : '';
    $products  = $this->loadModel('product')->getOrderedProducts($status, (int)$count, 0, 'all');
    $productID = !empty($params['active']) ? (int)$params['active'] : (int)key($products);
    $this->jxFillTaskStatisticView($productID, $products);
}

/**
 * 单个产品 Bug 统计：同样改为任务口径。
 *
 * @param  object $block
 * @access protected
 * @return void
 */
protected function printSingleBugStatisticBlock(object $block)
{
    $productID = (int)$this->session->product;
    $this->jxFillTaskStatisticView($productID);
}

/**
 * 组装任务统计区块视图（复用原 Bug 统计变量名，避免改模板结构）。
 *
 * @param  int   $productID
 * @param  array $products
 * @access protected
 * @return void
 */
protected function jxFillTaskStatisticView(int $productID, array $products = array()): void
{
    $months = array();
    $dates  = array();
    for($i = 5; $i >= 0; $i --)
    {
        $months[] = date('m',   strtotime("first day of -{$i} month"));
        $dates[]  = date('Y-m', strtotime("first day of -{$i} month"));
    }

    $productIds  = $productID ? array($productID) : array();
    $finished    = $this->jxCountTasksByProduct($productIds, 'finished');
    $unfinished  = $this->jxCountTasksByProduct($productIds, 'unfinished');
    $closedBugs  = (int)($finished[$productID] ?? 0);
    $unresolved  = (int)($unfinished[$productID] ?? 0);
    $totalBugs   = $closedBugs + $unresolved;
    $resolvedRate = $totalBugs ? round($closedBugs / $totalBugs * 100, 1) : 0;

    $createdMonthly  = $this->jxCountTasksByProductMonthly($productIds, 'created', $dates);
    $finishedMonthly = $this->jxCountTasksByProductMonthly($productIds, 'finished', $dates);

    $activateBugs = array();
    $resolveBugs  = array();
    $closeBugs    = array();
    foreach($dates as $date)
    {
        $activateBugs[$date] = (int)($createdMonthly[$productID][$date] ?? 0);
        $resolveBugs[$date]  = 0;
        $closeBugs[$date]    = (int)($finishedMonthly[$productID][$date] ?? 0);
    }

    $this->view->months         = $months;
    $this->view->products       = $products;
    $this->view->productID      = $productID;
    $this->view->totalBugs      = $totalBugs;
    $this->view->closedBugs     = $closedBugs;
    $this->view->unresovledBugs = $unresolved;
    $this->view->resolvedRate   = $resolvedRate;
    $this->view->activateBugs   = $activateBugs;
    $this->view->resolveBugs    = $resolveBugs;
    $this->view->closeBugs      = $closeBugs;
}

/**
 * 按产品、月份统计任务新增或完成数。
 *
 * @param  array  $productIdList
 * @param  string $kind          created|finished
 * @param  array  $dates         Y-m
 * @access protected
 * @return array
 */
protected function jxCountTasksByProductMonthly(array $productIdList, string $kind, array $dates): array
{
    if(empty($productIdList) || empty($dates)) return array();

    $first     = reset($dates);
    $last      = end($dates);
    $begin     = $first . '-01';
    $end       = date('Y-m-d', strtotime($last . '-01 +1 month'));
    $dateField = $kind === 'finished' ? 't1.finishedDate' : 't1.openedDate';
    $vision    = $this->config->vision;

    $stmt = $this->dao->select("t4.product AS product, {$dateField} AS happenDate, t1.id AS id")->from(TABLE_TASK)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.execution = t2.id')
        ->leftJoin(TABLE_PROJECT)->alias('t3')->on('t2.project = t3.id')
        ->leftJoin(TABLE_PROJECTPRODUCT)->alias('t4')->on('t3.id = t4.project')
        ->where('t2.type')->in('sprint,kanban,stage')
        ->andWhere('t1.deleted')->eq('0')
        ->andWhere('t2.deleted')->eq('0')
        ->andWhere('t3.deleted')->eq('0')
        ->andWhere('t1.isParent')->eq('0')
        ->andWhere('t4.product')->in($productIdList)
        ->andWhere($dateField)->ge($begin)
        ->andWhere($dateField)->lt($end);

    if($kind === 'finished')
    {
        $stmt->andWhere('t1.status', true)->eq('done')
            ->orWhere('t1.status')->eq('closed')->andWhere('t1.closedReason')->eq('done')->markRight(1);
    }

    $rows = $stmt->andWhere("t1.vision LIKE '%{$vision}%'", true)
        ->orWhere('t1.vision IS NULL')->markRight(1)
        ->fetchAll();

    $result = array();
    $seen   = array();
    foreach($rows as $row)
    {
        $row = (array)$row;
        $id  = (int)zget($row, 'id', 0);
        if(!$id || isset($seen[$id])) continue;
        $seen[$id] = true;

        $product = (int)zget($row, 'product', 0);
        $ym      = substr((string)zget($row, 'happenDate', ''), 0, 7);
        if(!$product || $ym === '') continue;
        if(!isset($result[$product][$ym])) $result[$product][$ym] = 0;
        $result[$product][$ym] ++;
    }
    return $result;
}
