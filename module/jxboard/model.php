<?php
declare(strict_types=1);
class jxboardModel extends model
{
    public function getDeptPairs(): array
    {
        return $this->dao->select('id, name')->from(TABLE_DEPT)
            ->where('name')->ne('')
            ->orderBy('`order`')
            ->fetchPairs('id', 'name');
    }

    public function getProductPairs(): array
    {
        return $this->dao->select('id, name')->from(TABLE_PRODUCT)
            ->where('deleted')->eq('0')
            ->andWhere('vision')->eq($this->config->vision)
            ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->products)->fi()
            ->orderBy('order_asc')
            ->fetchPairs('id', 'name');
    }

    public function normalizeFilters(array $filters = array()): array
    {
        $health = (string)($filters['health'] ?? '');
        $period = (string)($filters['period'] ?? ($this->config->jxboard->defaultPeriod ?? 'month'));
        $focus  = (string)($filters['focus'] ?? '');
        if(!in_array($health, array('green', 'yellow', 'red'), true)) $health = '';
        if(!in_array($period, array('week', 'month', 'halfyear', 'custom'), true)) $period = 'month';
        if(!in_array($focus, array('overdue', 'due', 'risk', 'certs'), true)) $focus = '';

        return array(
            'dept'      => (string)($filters['dept'] ?? ''),
            'product'   => (int)($filters['product'] ?? 0),
            'status'    => (string)($filters['status'] ?? ''),
            'health'    => $health,
            'period'    => $period,
            'begin'     => $this->validDate((string)($filters['begin'] ?? '')),
            'end'       => $this->validDate((string)($filters['end'] ?? '')),
            'focus'     => $focus,
            'expandAll' => !empty($filters['expandAll'])
        );
    }

    public function getBoard(array $filters = array()): object
    {
        $filters = $this->normalizeFilters($filters);
        $hasFilter = $filters['dept'] !== '' || $filters['product'] > 0 || $filters['status'] !== '' || $filters['health'] !== '';
        $this->app->loadLang('jxboard');
        $this->app->loadLang('project');

        $today      = date('Y-m-d');
        $warnDays   = (int)($this->config->jxboard->warnDays ?? 14);
        $redOverdue = (int)($this->config->jxboard->redOverdueTasks ?? 3);
        $topN       = (int)($this->config->jxboard->topN ?? 10);
        $warnDay    = date('Y-m-d', strtotime("+{$warnDays} days"));
        $unassigned = $this->lang->jxboard->unassigned;
        $noProduct  = $this->lang->jxboard->noProduct;
        $range      = $this->resolvePeriod($filters, $today);

        $data = $this->emptyBoard($filters);
        $data->visibleTotal  = $this->countVisibleProjects();
        $data->filteredEmpty = false;
        $data->periodRange   = $range;
        $data->listLimit     = $topN;
        $data->trends        = $this->emptyTrends($range);
        $data->certExpiring  = array();
        $data->certCount     = 0;

        $projects = $this->fetchProjects($filters);
        if(!$projects)
        {
            $data->isEmpty       = true;
            $data->filteredEmpty = $hasFilter && $data->visibleTotal > 0;
            $allCerts            = $this->fetchDueCerts($filters, $today);
            $data->certCount     = count($allCerts);
            $data->certExpiring  = $this->sliceList($allCerts, $filters, 'certs', $topN);
            return $data;
        }

        $projectIds = array();
        foreach($projects as $project) $projectIds[] = (int)$project->id;

        $taskByProject = $this->fetchTaskStats($projectIds);
        $overdueByProject = $this->fetchOverdueCounts($projectIds, $today);
        $productsByProject = $this->fetchProductsByProject($projectIds);

        $decorated = array();
        foreach($projects as $project)
        {
            $stat = $taskByProject[(int)$project->id] ?? $this->emptyTaskStat();
            $overdueCount = (int)($overdueByProject[(int)$project->id] ?? 0);
            $this->decorateProject($project, $stat, $overdueCount, $productsByProject, $today, $warnDay, $redOverdue, $unassigned, $noProduct);
            $decorated[] = $project;
        }

        if($filters['health'] !== '')
        {
            $decorated = array_values(array_filter($decorated, function($project) use ($filters)
            {
                return $project->health === $filters['health'];
            }));
        }

        $data->total = count($decorated);
        if($data->total === 0)
        {
            $data->isEmpty       = true;
            $data->filteredEmpty = $hasFilter && $data->visibleTotal > 0;
            $allCerts            = $this->fetchDueCerts($filters, $today);
            $data->certCount     = count($allCerts);
            $data->certExpiring  = $this->sliceList($allCerts, $filters, 'certs', $topN);
            return $data;
        }

        $projectIds = array();
        $funnel = array('wait' => 0, 'doing' => 0, 'done' => 0, 'closed' => 0);
        foreach($decorated as $project)
        {
            $projectIds[] = (int)$project->id;
            $stat = $taskByProject[(int)$project->id] ?? $this->emptyTaskStat();

            $data->budget   += (float)$project->budget;
            $data->estimate += $project->estimate;
            $data->consumed += $project->consumed;
            $data->left     += $project->remain;
            $data->overdueTaskCount += $project->overdueTasks;
            $data->byHealth[$project->health] = ($data->byHealth[$project->health] ?? 0) + 1;

            $statusKey = in_array($project->status, array('wait', 'doing', 'suspended', 'closed'), true) ? $project->status : 'wait';
            $data->byStatus[$statusKey] = ($data->byStatus[$statusKey] ?? 0) + 1;

            $deptName = $project->deptName;
            if(!isset($data->byDept[$deptName]))
            {
                $data->byDept[$deptName] = array(
                    'id' => (int)$project->deptID,
                    'name' => $deptName,
                    'count' => 0,
                    'budget' => 0,
                    'estimate' => 0,
                    'consumed' => 0,
                    'overdue' => 0,
                    'red' => 0
                );
            }
            $data->byDept[$deptName]['count']++;
            $data->byDept[$deptName]['budget']    += (float)$project->budget;
            $data->byDept[$deptName]['estimate']  += $project->estimate;
            $data->byDept[$deptName]['consumed']  += $project->consumed;
            $data->byDept[$deptName]['overdue']   += $project->overdueTasks;
            if($project->health == 'red') $data->byDept[$deptName]['red']++;
            if((int)$project->deptID === 0) $data->hasUnassignedDept = true;

            $productKeys = $project->productIDs ?: array(0);
            $productNames = $project->productNameList ?: array($noProduct);
            foreach($productKeys as $index => $productID)
            {
                $productID = (int)$productID;
                $name = $productNames[$index] ?? $noProduct;
                if(!isset($data->byProduct[$productID]))
                {
                    $data->byProduct[$productID] = array('id' => $productID, 'name' => $name, 'count' => 0, 'budget' => 0, 'red' => 0);
                }
                $data->byProduct[$productID]['count']++;
                $data->byProduct[$productID]['budget'] += (float)$project->budget;
                if($project->health == 'red') $data->byProduct[$productID]['red']++;
            }

            $funnel['wait']   += $stat['wait'];
            $funnel['doing']  += $stat['doing'] + $stat['pause'];
            $funnel['done']   += $stat['done'];
            $funnel['closed'] += $stat['closed'];

            if($project->health != 'green' || $project->blocker !== '') $data->riskProjects[] = $project;
            $data->projects[] = $project;
        }

        $allOverdue = $this->fetchOverdueTasks($projectIds, $today, 0);
        $allDue     = $this->fetchDueExecutions($projectIds, $today, $warnDay, 0);
        $allCerts   = $this->fetchDueCerts($filters, $today);

        $data->taskFunnel         = $funnel;
        $data->overdueTaskCount   = count($allOverdue);
        $data->dueExecutionCount  = count($allDue);
        $data->riskCount          = count($data->riskProjects);
        $data->certCount          = count($allCerts);
        $data->overdueTasks       = $this->sliceList($allOverdue, $filters, 'overdue', $topN);
        $data->dueExecutions      = $this->sliceList($allDue, $filters, 'due', $topN);
        $data->riskProjectsView   = $this->sliceList($data->riskProjects, $filters, 'risk', $topN);
        $data->certExpiring       = $this->sliceList($allCerts, $filters, 'certs', $topN);
        $data->trends             = $this->fetchTrends($projectIds, $range);
        $data->isEmpty            = false;
        return $data;
    }

    protected function emptyBoard(array $filters): object
    {
        $data = new stdclass();
        $data->filters           = $filters;
        $data->total             = 0;
        $data->visibleTotal      = 0;
        $data->isEmpty           = true;
        $data->filteredEmpty     = false;
        $data->hasUnassignedDept = false;
        $data->byHealth          = array('green' => 0, 'yellow' => 0, 'red' => 0);
        $data->budget            = 0.0;
        $data->estimate          = 0.0;
        $data->consumed          = 0.0;
        $data->left              = 0.0;
        $data->overdueTaskCount  = 0;
        $data->dueExecutionCount = 0;
        $data->riskCount         = 0;
        $data->certCount         = 0;
        $data->listLimit         = (int)($this->config->jxboard->topN ?? 10);
        $data->byStatus          = array('wait' => 0, 'doing' => 0, 'suspended' => 0, 'closed' => 0);
        $data->byDept            = array();
        $data->byProduct         = array();
        $data->taskFunnel        = array('wait' => 0, 'doing' => 0, 'done' => 0, 'closed' => 0);
        $data->overdueTasks      = array();
        $data->dueExecutions     = array();
        $data->riskProjects      = array();
        $data->riskProjectsView  = array();
        $data->certExpiring      = array();
        $data->projects          = array();
        $data->periodRange       = array('period' => 'month', 'begin' => '', 'end' => '', 'grain' => 'week');
        $data->trends            = $this->emptyTrends($data->periodRange);
        return $data;
    }

    protected function emptyTrends(array $range): object
    {
        $trends = new stdclass();
        $trends->begin   = $range['begin'] ?? '';
        $trends->end     = $range['end'] ?? '';
        $trends->grain   = $range['grain'] ?? 'week';
        $trends->period  = $range['period'] ?? 'month';
        $trends->buckets = array();
        $trends->hasData = false;
        return $trends;
    }

    protected function sliceList(array $list, array $filters, string $focusKey, int $topN): array
    {
        if(!empty($filters['expandAll']) || ($filters['focus'] ?? '') === $focusKey || $topN <= 0) return array_values($list);
        return array_slice(array_values($list), 0, $topN);
    }

    public function compactDate(string $value): string
    {
        $value = $this->validDate($value);
        return $value === '' ? '' : str_replace('-', '', $value);
    }

    protected function validDate(string $value): string
    {
        $value = trim($value);
        if($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') return '';
        if(preg_match('/^\d{8}$/', $value)) $value = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
        $time = strtotime($value);
        if($time === false) return '';
        return date('Y-m-d', $time);
    }

    protected function resolvePeriod(array $filters, string $today): array
    {
        $period = $filters['period'] ?? 'month';
        if($period === 'week')
        {
            $dow   = (int)date('N', strtotime($today));
            $begin = date('Y-m-d', strtotime($today . ' -' . ($dow - 1) . ' days'));
            return array('period' => $period, 'begin' => $begin, 'end' => $today, 'grain' => 'day');
        }
        if($period === 'halfyear')
        {
            $begin = date('Y-m-01', strtotime($today . ' -5 months'));
            return array('period' => $period, 'begin' => $begin, 'end' => $today, 'grain' => 'month');
        }
        if($period === 'custom')
        {
            $begin = $filters['begin'] !== '' ? $filters['begin'] : date('Y-m-d', strtotime($today . ' -29 days'));
            $end   = $filters['end'] !== '' ? $filters['end'] : $today;
            if($begin > $end)
            {
                $swap  = $begin;
                $begin = $end;
                $end   = $swap;
            }
            $days  = (int)round((strtotime($end) - strtotime($begin)) / 86400);
            $grain = $days <= 14 ? 'day' : ($days <= 90 ? 'week' : 'month');
            return array('period' => $period, 'begin' => $begin, 'end' => $end, 'grain' => $grain);
        }

        $begin = date('Y-m-01', strtotime($today));
        return array('period' => 'month', 'begin' => $begin, 'end' => $today, 'grain' => 'week');
    }

    protected function buildBuckets(string $begin, string $end, string $grain): array
    {
        $buckets = array();
        if($grain === 'day')
        {
            for($time = strtotime($begin); $time <= strtotime($end); $time += 86400)
            {
                $key = date('Y-m-d', $time);
                $buckets[$key] = array('key' => $key, 'label' => date('m-d', $time), 'tasks' => 0, 'hours' => 0.0);
            }
            return $buckets;
        }

        if($grain === 'week')
        {
            $dow         = (int)date('N', strtotime($begin));
            $startMonday = strtotime($begin . ' -' . ($dow - 1) . ' days');
            for($time = $startMonday; $time <= strtotime($end); $time += 7 * 86400)
            {
                $key     = date('o-\WW', $time);
                $weekEnd = min(strtotime($end), $time + 6 * 86400);
                $buckets[$key] = array(
                    'key'   => $key,
                    'label' => date('m/d', $time) . '-' . date('m/d', $weekEnd),
                    'tasks' => 0,
                    'hours' => 0.0
                );
            }
            return $buckets;
        }

        $month = date('Y-m-01', strtotime($begin));
        $last  = date('Y-m-01', strtotime($end));
        for($time = strtotime($month); $time <= strtotime($last); $time = strtotime('+1 month', $time))
        {
            $key = date('Y-m', $time);
            $buckets[$key] = array('key' => $key, 'label' => $key, 'tasks' => 0, 'hours' => 0.0);
        }
        return $buckets;
    }

    protected function bucketKey(string $date, string $grain): string
    {
        $date = substr($date, 0, 10);
        if($grain === 'day') return $date;
        if($grain === 'week') return date('o-\WW', strtotime($date));
        return substr($date, 0, 7);
    }

    protected function fetchTrends(array $projectIds, array $range): object
    {
        $trends = $this->emptyTrends($range);
        $begin  = $range['begin'];
        $end    = $range['end'];
        $grain  = $range['grain'];
        if(!$projectIds || $begin === '' || $end === '') return $trends;

        $buckets = $this->buildBuckets($begin, $end, $grain);
        $endNext = date('Y-m-d', strtotime($end . ' +1 day'));

        $taskRows = $this->dao->select("DATE(finishedDate) AS finishedDay, COUNT(id) AS `count`, SUM(consumed) AS consumed")
            ->from(TABLE_TASK)
            ->where('deleted')->eq('0')
            ->andWhere('isParent')->eq('0')
            ->andWhere('project')->in($projectIds)
            ->andWhere('status')->in('done,closed')
            ->andWhere('finishedDate')->ge($begin)
            ->andWhere('finishedDate')->lt($endNext)
            ->groupBy('finishedDay')
            ->fetchAll();

        foreach($taskRows as $row)
        {
            $day = substr((string)$row->finishedDay, 0, 10);
            $key = $this->bucketKey($day, $grain);
            if(!isset($buckets[$key])) continue;
            $buckets[$key]['tasks'] += (int)$row->count;
        }

        $effortRows = $this->dao->select('`date` AS effortDay, SUM(consumed) AS consumed')
            ->from(TABLE_EFFORT)
            ->where('deleted')->eq('0')
            ->andWhere('project')->in($projectIds)
            ->andWhere('`date`')->ge($begin)
            ->andWhere('`date`')->le($end)
            ->beginIF(!empty($this->config->vision))->andWhere('vision')->eq($this->config->vision)->fi()
            ->groupBy('effortDay')
            ->fetchAll();

        $usedEffort = false;
        foreach($effortRows as $row)
        {
            $day = substr((string)$row->effortDay, 0, 10);
            $key = $this->bucketKey($day, $grain);
            if(!isset($buckets[$key])) continue;
            $hours = (float)$row->consumed;
            if($hours <= 0) continue;
            $buckets[$key]['hours'] += $hours;
            $usedEffort = true;
        }

        if(!$usedEffort)
        {
            foreach($taskRows as $row)
            {
                $day = substr((string)$row->finishedDay, 0, 10);
                $key = $this->bucketKey($day, $grain);
                if(!isset($buckets[$key])) continue;
                $buckets[$key]['hours'] += (float)$row->consumed;
            }
        }

        $hasData = false;
        foreach($buckets as $bucket)
        {
            if($bucket['tasks'] > 0 || $bucket['hours'] > 0) $hasData = true;
        }

        $trends->buckets = array_values($buckets);
        $trends->hasData = $hasData;
        return $trends;
    }

    protected function fetchDueCerts(array $filters, string $today): array
    {
        if(!defined('TABLE_JX_PRODUCT')) return array();

        $certDays = (int)($this->config->jenshin->certWarnDays ?? 90);
        $certFrom = date('Y-m-d', strtotime('-30 days'));
        $certTo   = date('Y-m-d', strtotime("+{$certDays} days"));

        $rows = $this->dao->select('t1.product, t1.certNo, t1.certValidTo, t2.name AS productName')
            ->from(TABLE_JX_PRODUCT)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product = t2.id')
            ->where('t1.deleted')->eq(0)
            ->andWhere('t2.deleted')->eq('0')
            ->andWhere('t2.vision')->eq($this->config->vision)
            ->andWhere('t1.certValidTo')->notNull()
            ->andWhere('t1.certValidTo')->ge($certFrom)
            ->andWhere('t1.certValidTo')->le($certTo)
            ->beginIF($filters['product'] > 0)->andWhere('t1.product')->eq($filters['product'])->fi()
            ->beginIF(!$this->app->user->admin)->andWhere('t1.product')->in($this->app->user->view->products)->fi()
            ->orderBy('t1.certValidTo')
            ->fetchAll();

        $list = array();
        foreach($rows as $row)
        {
            $row->daysLeft = (int)round((strtotime((string)$row->certValidTo) - strtotime($today)) / 86400);
            $list[] = $row;
        }
        return $list;
    }

    protected function countVisibleProjects(): int
    {
        return (int)$this->dao->select('COUNT(id) AS total')->from(TABLE_PROJECT)
            ->where('type')->eq('project')
            ->andWhere('deleted')->eq('0')
            ->andWhere('vision')->eq($this->config->vision)
            ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->projects)->fi()
            ->fetch('total');
    }

    protected function fetchProjects(array $filters): array
    {
        $linkedIds = array();
        if($filters['product'] > 0)
        {
            $linkedIds = $this->dao->select('project')->from(TABLE_PROJECTPRODUCT)
                ->where('product')->eq($filters['product'])
                ->fetchPairs('project', 'project');
            if(!$linkedIds) return array();
        }

        $deptUnassigned = ($filters['dept'] === '0' || $filters['dept'] === 'unassigned');

        return $this->dao->select('t1.id, t1.name, t1.status, t1.begin, t1.end, t1.budget, t1.budgetUnit, t1.PM, t1.progress, t1.model, t2.realname AS pmName, t2.dept AS deptID, t3.name AS deptName')
            ->from(TABLE_PROJECT)->alias('t1')
            ->leftJoin(TABLE_USER)->alias('t2')->on('t1.PM = t2.account')
            ->leftJoin(TABLE_DEPT)->alias('t3')->on('t2.dept = t3.id')
            ->where('t1.type')->eq('project')
            ->andWhere('t1.deleted')->eq('0')
            ->andWhere('t1.vision')->eq($this->config->vision)
            ->beginIF(!$this->app->user->admin)->andWhere('t1.id')->in($this->app->user->view->projects)->fi()
            ->beginIF($filters['product'] > 0)->andWhere('t1.id')->in($linkedIds)->fi()
            ->beginIF($filters['status'] !== '')->andWhere('t1.status')->eq($filters['status'])->fi()
            ->beginIF($deptUnassigned)->andWhere('(t2.dept IS NULL OR t2.dept = 0)')->fi()
            ->beginIF($filters['dept'] !== '' && !$deptUnassigned)->andWhere('t2.dept')->eq((int)$filters['dept'])->fi()
            ->orderBy('t1.end_asc, t1.id_desc')
            ->fetchAll('id');
    }

    protected function emptyTaskStat(): array
    {
        return array(
            'wait' => 0, 'doing' => 0, 'done' => 0, 'pause' => 0, 'cancel' => 0, 'closed' => 0,
            'estimate' => 0.0, 'consumed' => 0.0, 'remain' => 0.0, 'total' => 0
        );
    }

    protected function fetchTaskStats(array $projectIds): array
    {
        $stats = array();
        if(!$projectIds) return $stats;

        $rows = $this->dao->select('project, status, COUNT(id) AS `count`, SUM(estimate) AS estimate, SUM(consumed) AS consumed, SUM(`left`) AS remain')
            ->from(TABLE_TASK)
            ->where('deleted')->eq('0')
            ->andWhere('isParent')->eq('0')
            ->andWhere('project')->in($projectIds)
            ->groupBy('project, status')
            ->fetchAll();

        foreach($rows as $row)
        {
            $projectID = (int)$row->project;
            if(!isset($stats[$projectID])) $stats[$projectID] = $this->emptyTaskStat();
            $status = (string)$row->status;
            $count  = (int)$row->count;
            if(isset($stats[$projectID][$status])) $stats[$projectID][$status] += $count;
            $stats[$projectID]['total']    += $count;
            $stats[$projectID]['estimate'] += (float)$row->estimate;
            $stats[$projectID]['consumed'] += (float)$row->consumed;
            $stats[$projectID]['remain']   += (float)$row->remain;
        }
        return $stats;
    }

    protected function fetchOverdueCounts(array $projectIds, string $today): array
    {
        if(!$projectIds) return array();
        return $this->dao->select('project, COUNT(id) AS `count`')->from(TABLE_TASK)
            ->where('deleted')->eq('0')
            ->andWhere('isParent')->eq('0')
            ->andWhere('project')->in($projectIds)
            ->andWhere('deadline')->ne('0000-00-00')
            ->andWhere('deadline')->lt($today)
            ->andWhere('status')->in('wait,doing,pause')
            ->groupBy('project')
            ->fetchPairs('project', 'count');
    }

    protected function fetchProductsByProject(array $projectIds): array
    {
        $map = array();
        if(!$projectIds) return $map;

        $rows = $this->dao->select('t1.project, t1.product, t2.name')
            ->from(TABLE_PROJECTPRODUCT)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product = t2.id')
            ->where('t1.project')->in($projectIds)
            ->andWhere('t2.deleted')->eq('0')
            ->fetchAll();

        foreach($rows as $row)
        {
            $projectID = (int)$row->project;
            if(!isset($map[$projectID])) $map[$projectID] = array('ids' => array(), 'names' => array());
            $map[$projectID]['ids'][]   = (int)$row->product;
            $map[$projectID]['names'][] = (string)$row->name;
        }
        return $map;
    }

    protected function fetchOverdueTasks(array $projectIds, string $today, int $topN): array
    {
        if(!$projectIds) return array();

        $rows = $this->dao->select('t1.id, t1.name, t1.project, t1.execution, t1.deadline, t1.status, t1.assignedTo, t1.pri, t2.name AS projectName, t3.realname AS assignedName')
            ->from(TABLE_TASK)->alias('t1')
            ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
            ->leftJoin(TABLE_USER)->alias('t3')->on('t1.assignedTo = t3.account')
            ->where('t1.deleted')->eq('0')
            ->andWhere('t1.isParent')->eq('0')
            ->andWhere('t1.project')->in($projectIds)
            ->andWhere('t1.deadline')->ne('0000-00-00')
            ->andWhere('t1.deadline')->lt($today)
            ->andWhere('t1.status')->in('wait,doing,pause')
            ->orderBy('t1.deadline_asc, t1.id_asc')
            ->fetchAll();

        $list = array();
        foreach($rows as $row)
        {
            $row->overdueDays   = max(0, (int)round((strtotime($today) - strtotime((string)$row->deadline)) / 86400));
            $row->assignedName  = $row->assignedName ?: $row->assignedTo;
            $list[] = $row;
            if($topN > 0 && count($list) >= $topN) break;
        }
        return $list;
    }

    protected function fetchDueExecutions(array $projectIds, string $today, string $warnDay, int $topN): array
    {
        if(!$projectIds) return array();

        $rows = $this->dao->select('t1.id, t1.name, t1.project, t1.begin, t1.end, t1.status, t2.name AS projectName')
            ->from(TABLE_EXECUTION)->alias('t1')
            ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
            ->where('t1.type')->in('sprint,stage,kanban')
            ->andWhere('t1.deleted')->eq('0')
            ->andWhere('t1.project')->in($projectIds)
            ->andWhere('t1.status')->notin('closed,done')
            ->andWhere('t1.end')->ne('0000-00-00')
            ->andWhere('t1.end')->le($warnDay)
            ->orderBy('t1.end_asc, t1.id_asc')
            ->fetchAll();

        $list = array();
        foreach($rows as $row)
        {
            $row->daysLeft = (int)round((strtotime((string)$row->end) - strtotime($today)) / 86400);
            $list[] = $row;
            if($topN > 0 && count($list) >= $topN) break;
        }
        return $list;
    }

    protected function decorateProject(object $project, array $stat, int $overdueCount, array $productsByProject, string $today, string $warnDay, int $redOverdue, string $unassigned, string $noProduct): void
    {
        $projectID = (int)$project->id;
        $products  = $productsByProject[$projectID] ?? array('ids' => array(), 'names' => array());

        $project->deptID          = (int)($project->deptID ?? 0);
        $project->deptName        = trim((string)($project->deptName ?? '')) !== '' ? (string)$project->deptName : $unassigned;
        $project->pmName          = trim((string)($project->pmName ?? '')) !== '' ? (string)$project->pmName : (string)$project->PM;
        $project->productIDs      = $products['ids'];
        $project->productNameList = $products['names'] ?: array($noProduct);
        $project->productNames    = implode('、', $project->productNameList);

        $project->taskWait   = (int)$stat['wait'];
        $project->taskDoing  = (int)$stat['doing'] + (int)$stat['pause'];
        $project->taskDone   = (int)$stat['done'] + (int)$stat['closed'];
        $project->taskTotal  = (int)$stat['total'];
        $project->estimate   = round((float)$stat['estimate'], 1);
        $project->consumed   = round((float)$stat['consumed'], 1);
        $project->remain     = round((float)$stat['remain'], 1);
        $project->overdueTasks = $overdueCount;

        $active = $project->taskTotal - (int)$stat['cancel'];
        $project->progress = $active > 0 ? round($project->taskDone * 100 / $active, 1) : round((float)$project->progress, 1);

        $closed    = in_array($project->status, array('closed', 'done'), true);
        $hasEnd    = !empty($project->end) && $project->end !== '0000-00-00';
        $project->delay = ($hasEnd && !$closed && $project->end < $today)
            ? max(0, (int)round((strtotime($today) - strtotime((string)$project->end)) / 86400))
            : 0;

        $health  = 'green';
        $blocker = '';
        if(!$closed && $project->delay > 0)
        {
            $health  = 'red';
            $blocker = sprintf($this->lang->jxboard->overdueDays, $project->delay);
        }
        if($overdueCount >= $redOverdue)
        {
            $health  = 'red';
            $blocker = $blocker !== '' ? $blocker : sprintf('%s 个任务逾期', $overdueCount);
        }
        if($health != 'red')
        {
            if($project->status == 'suspended')
            {
                $health  = 'yellow';
                $blocker = $this->lang->project->statusList['suspended'] ?? '已挂起';
            }
            elseif($overdueCount >= 1)
            {
                $health  = 'yellow';
                $blocker = sprintf('%s 个任务逾期', $overdueCount);
            }
            elseif(!$closed && $hasEnd && $project->end <= $warnDay)
            {
                $health  = 'yellow';
                $blocker = $this->lang->jxboard->dueSoon;
            }
        }
        elseif($overdueCount > 0 && $blocker !== '' && strpos($blocker, '任务逾期') === false)
        {
            $blocker .= ' · ' . sprintf('%s 个任务逾期', $overdueCount);
        }

        $project->health  = $health;
        $project->blocker = $blocker;
    }
}
