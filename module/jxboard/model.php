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

    public function getBoard(array $filters = array()): object
    {
        $filters = array(
            'dept'    => (string)($filters['dept'] ?? ''),
            'product' => (int)($filters['product'] ?? 0),
            'status'  => (string)($filters['status'] ?? '')
        );
        $hasFilter = $filters['dept'] !== '' || $filters['product'] > 0 || $filters['status'] !== '';
        $this->app->loadLang('jxboard');
        $this->app->loadLang('project');

        $today      = date('Y-m-d');
        $warnDays   = (int)($this->config->jxboard->warnDays ?? 14);
        $redOverdue = (int)($this->config->jxboard->redOverdueTasks ?? 3);
        $topN       = (int)($this->config->jxboard->topN ?? 10);
        $warnDay    = date('Y-m-d', strtotime("+{$warnDays} days"));
        $unassigned = $this->lang->jxboard->unassigned;
        $noProduct  = $this->lang->jxboard->noProduct;

        $data = $this->emptyBoard($filters);
        $data->visibleTotal  = $this->countVisibleProjects();
        $data->filteredEmpty = false;

        $projects = $this->fetchProjects($filters);
        $data->total = count($projects);
        if($data->total === 0)
        {
            $data->isEmpty       = true;
            $data->filteredEmpty = $hasFilter && $data->visibleTotal > 0;
            return $data;
        }

        $projectIds = array();
        foreach($projects as $project) $projectIds[] = (int)$project->id;

        $taskByProject = $this->fetchTaskStats($projectIds);
        $overdueByProject = $this->fetchOverdueCounts($projectIds, $today);
        $productsByProject = $this->fetchProductsByProject($projectIds);

        $funnel = array('wait' => 0, 'doing' => 0, 'done' => 0, 'closed' => 0);
        foreach($projects as $project)
        {
            $stat = $taskByProject[(int)$project->id] ?? $this->emptyTaskStat();
            $overdueCount = (int)($overdueByProject[(int)$project->id] ?? 0);
            $this->decorateProject($project, $stat, $overdueCount, $productsByProject, $today, $warnDay, $redOverdue, $unassigned, $noProduct);

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
                $data->byDept[$deptName] = array('count' => 0, 'budget' => 0, 'estimate' => 0, 'consumed' => 0, 'overdue' => 0, 'red' => 0);
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

        $data->taskFunnel    = $funnel;
        $data->overdueTasks  = $this->fetchOverdueTasks($projectIds, $today, $topN);
        $data->dueExecutions = $this->fetchDueExecutions($projectIds, $today, $warnDay, $topN);
        $data->isEmpty       = false;
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
        $data->byStatus          = array('wait' => 0, 'doing' => 0, 'suspended' => 0, 'closed' => 0);
        $data->byDept            = array();
        $data->byProduct         = array();
        $data->taskFunnel        = array('wait' => 0, 'doing' => 0, 'done' => 0, 'closed' => 0);
        $data->overdueTasks      = array();
        $data->dueExecutions     = array();
        $data->riskProjects      = array();
        $data->projects          = array();
        return $data;
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
            if(count($list) >= $topN) break;
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
            if(count($list) >= $topN) break;
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
