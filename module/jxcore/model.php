<?php
declare(strict_types=1);
/**
 * Shared engine for Jenshin medical project management.
 */
class jxcoreModel extends model
{
    /**
     * Ensure schema exists. Safe to call on every request that needs jx tables.
     *
     * @access public
     * @return bool
     */
    public function ensureSchema(): bool
    {
        static $done = false;
        if($done) return true;

        try
        {
            if(!$this->dbh->tableExist(TABLE_JX_SCHEMA)) $this->runInstallSql();
        }
        catch(Throwable $e)
        {
            try { $this->runInstallSql(); } catch(Throwable $ignored) { return false; }
        }
        $this->enableExecutionOnLinkedProjects();
        $this->syncArchiveLinesToProduct();
        $done = true;
        return true;
    }

    /**
     * Enable execution list for medical matter projects created as no-sprint kanban.
     *
     * @access protected
     * @return void
     */
    protected function enableExecutionOnLinkedProjects(): void
    {
        try
        {
            if(!$this->dbh->tableExist(TABLE_JX_SCHEMA) || !$this->dbh->tableExist(TABLE_JX_PROJECT)) return;
            $applied = $this->dao->select('id')->from(TABLE_JX_SCHEMA)->where('code')->eq('enable-execution')->fetch();
            if($applied) return;

            $projectIDs = $this->dao->select('project')->from(TABLE_JX_PROJECT)->where('project')->ne(0)->fetchPairs('project', 'project');
            if($projectIDs)
            {
                $this->dao->update(TABLE_PROJECT)
                    ->set('model')->eq('scrum')
                    ->set('multiple')->eq('1')
                    ->where('id')->in($projectIDs)
                    ->andWhere('type')->eq('project')
                    ->exec();
                $this->dao->update(TABLE_PROJECT)
                    ->set('multiple')->eq('1')
                    ->set('type')->eq('sprint')
                    ->where('project')->in($projectIDs)
                    ->andWhere('type')->in('kanban,sprint')
                    ->exec();
            }

            $row = new stdclass();
            $row->code        = 'enable-execution';
            $row->version     = '1.0.1';
            $row->appliedDate = helper::now();
            $this->dao->insert(TABLE_JX_SCHEMA)->data($row)->exec();
        }
        catch(Throwable $e) { }
    }

    /**
     * Copy medical-archive line names onto original product lines so the list column 所属产品线 has values.
     *
     * @access protected
     * @return void
     */
    protected function syncArchiveLinesToProduct(): void
    {
        try
        {
            if(!$this->dbh->tableExist(TABLE_JX_SCHEMA) || !$this->dbh->tableExist(TABLE_JX_PRODUCT)) return;
            $applied = $this->dao->select('id')->from(TABLE_JX_SCHEMA)->where('code')->eq('sync-product-line')->fetch();
            if($applied) return;

            $rows = $this->dao->select('product, line')->from(TABLE_JX_PRODUCT)->where('line')->ne('')->fetchAll();
            $lineIDs = array();
            foreach($rows as $row)
            {
                $name = trim((string)$row->line);
                if($name === '') continue;
                if(!isset($lineIDs[$name]))
                {
                    $existed = (int)$this->dao->select('id')->from(TABLE_MODULE)
                        ->where('type')->eq('line')
                        ->andWhere('deleted')->eq('0')
                        ->andWhere('name')->eq($name)
                        ->fetch('id');
                    if($existed)
                    {
                        $lineIDs[$name] = $existed;
                    }
                    else
                    {
                        $line = new stdclass();
                        $line->type   = 'line';
                        $line->parent = 0;
                        $line->grade  = 1;
                        $line->name   = $name;
                        $line->root   = 0;
                        $this->dao->insert(TABLE_MODULE)->data($line)->exec();
                        $id = (int)$this->dao->lastInsertID();
                        $this->dao->update(TABLE_MODULE)->set('path')->eq(",{$id},")->set('`order`')->eq($id)->where('id')->eq($id)->exec();
                        $lineIDs[$name] = $id;
                    }
                }

                $this->dao->update(TABLE_PRODUCT)
                    ->set('line')->eq($lineIDs[$name])
                    ->where('id')->eq((int)$row->product)
                    ->andWhere('line')->eq(0)
                    ->exec();
            }

            $mark = new stdclass();
            $mark->code        = 'sync-product-line';
            $mark->version     = '1.0.2';
            $mark->appliedDate = helper::now();
            $this->dao->insert(TABLE_JX_SCHEMA)->data($mark)->exec();
        }
        catch(Throwable $e) { }
    }

    /**
     * Execute install SQL file.
     *
     * @access public
     * @return void
     */
    public function runInstallSql(): void
    {
        $file = $this->app->getBasePath() . 'db/jenshin/install.sql';
        if(!is_file($file)) return;
        $raw = preg_replace('/^\s*--.*$/m', '', (string)file_get_contents($file));
        foreach(array_filter(array_map('trim', explode(';', $raw))) as $sql)
        {
            if($sql === '') continue;
            try { $this->dbh->rawQuery($sql); } catch(Throwable $e) { }
        }
    }

    public function getProductPairs(): array
    {
        return $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('deleted')->eq('0')->orderBy('id_desc')->fetchPairs();
    }

    public function getUserPairs(): array
    {
        return $this->loadModel('user')->getPairs('noclosed,nodeleted,noletter');
    }

    public function getDeptPairs(): array
    {
        $depts = $this->loadModel('dept')->getOptionMenu();
        $pairs = array();
        foreach($depts as $id => $name)
        {
            $pairs[$name] = trim(str_replace('/', '', (string)$name));
            if(is_numeric($id) && (int)$id > 0) $pairs[(string)$id] = trim((string)$name);
        }
        $named = $this->dao->select('id,name')->from(TABLE_DEPT)->fetchPairs();
        return $named ?: $depts;
    }

    public function getDeptNamePairs(): array
    {
        $list = $this->dao->select('name')->from(TABLE_DEPT)->orderBy('`order`')->fetchAll();
        $pairs = array();
        foreach($list as $dept)
        {
            if($dept->name === '') continue;
            $pairs[$dept->name] = $dept->name;
        }
        return $pairs;
    }

    public function getProductArchive(int $productID): ?object
    {
        $this->ensureSchema();
        $product = $this->dao->select('*')->from(TABLE_PRODUCT)->where('id')->eq($productID)->fetch();
        if(!$product) return null;
        $extra = $this->dao->select('*')->from(TABLE_JX_PRODUCT)->where('product')->eq($productID)->fetch();
        if($extra)
        {
            foreach($extra as $key => $value)
            {
                if($key == 'id' || $key == 'product' || $key == 'line') continue;
                $product->$key = $value;
            }
            $product->archiveID = $extra->id;
        }
        else
        {
            $product->model = $product->code;
            $product->category = '';
            $product->certNo = '';
            $product->certValidTo = '';
            $product->specs = '';
            $product->udi = '';
            $product->manufacturer = '';
            $product->patents = '';
            $product->tenderCode = '';
            $product->archiveID = 0;
        }
        return $product;
    }

    public function saveProductArchive(int $productID, object $data): int
    {
        $this->ensureSchema();
        $row = new stdclass();
        $fields = array('model', 'category', 'line', 'certNo', 'certValidTo', 'specs', 'udi', 'manufacturer', 'patents', 'tenderCode');
        foreach($fields as $field) $row->$field = isset($data->$field) ? $data->$field : '';
        if(empty($row->certValidTo)) $row->certValidTo = null;
        $exist = $this->dao->select('id')->from(TABLE_JX_PRODUCT)->where('product')->eq($productID)->fetch();
        if($exist)
        {
            $this->dao->update(TABLE_JX_PRODUCT)->data($row)->where('id')->eq($exist->id)->exec();
            return (int)$exist->id;
        }
        $row->product = $productID;
        $this->dao->insert(TABLE_JX_PRODUCT)->data($row)->exec();
        return (int)$this->dao->lastInsertID();
    }

    /**
     * Persist medical archive fields from the current request without touching zt_product columns.
     */
    public function saveProductArchiveFromPost(int $productID): int
    {
        $map = array(
            'jxModel'        => 'model',
            'jxCategory'     => 'category',
            'jxLine'         => 'line',
            'jxCertNo'       => 'certNo',
            'jxCertValidTo'  => 'certValidTo',
            'jxSpecs'        => 'specs',
            'jxUdi'          => 'udi',
            'jxManufacturer' => 'manufacturer',
            'jxPatents'      => 'patents',
            'jxTenderCode'   => 'tenderCode',
            'model'          => 'model',
            'category'       => 'category',
            'certNo'         => 'certNo',
            'certValidTo'    => 'certValidTo',
            'specs'          => 'specs',
            'udi'            => 'udi',
            'manufacturer'   => 'manufacturer',
            'patents'        => 'patents',
            'tenderCode'     => 'tenderCode'
        );

        $existing = $this->getProductArchive($productID);
        $extra    = $this->dao->select('*')->from(TABLE_JX_PRODUCT)->where('product')->eq($productID)->fetch();
        $data     = new stdclass();
        foreach(array('model', 'category', 'line', 'certNo', 'certValidTo', 'specs', 'udi', 'manufacturer', 'patents', 'tenderCode') as $field)
        {
            if($field === 'line')
            {
                $data->line = ($extra && isset($extra->line)) ? $extra->line : '';
                continue;
            }
            $data->$field = ($existing && isset($existing->$field)) ? $existing->$field : '';
        }
        foreach($map as $postKey => $field)
        {
            $value = $this->post->$postKey;
            if($value !== false && $value !== null) $data->$field = $value;
        }

        $productLine = $this->dao->select('line')->from(TABLE_PRODUCT)->where('id')->eq($productID)->fetch('line');
        if(!empty($productLine))
        {
            $lineName = $this->dao->select('name')->from(TABLE_MODULE)
                ->where('id')->eq((int)$productLine)
                ->andWhere('type')->eq('line')
                ->fetch('name');
            if($lineName) $data->line = $lineName;
        }

        return $this->saveProductArchive($productID, $data);
    }

    public function getProjectExtra(int $projectID): ?object
    {
        $this->ensureSchema();
        return $this->dao->select('*')->from(TABLE_JX_PROJECT)->where('project')->eq($projectID)->fetch() ?: null;
    }

    public function saveProjectExtra(int $projectID, object $data): int
    {
        $this->ensureSchema();
        $row = new stdclass();
        foreach(array('bizType', 'bizID', 'code', 'leadDept', 'supportDepts', 'goal', 'health', 'progress', 'blocker') as $field)
        {
            if(isset($data->$field)) $row->$field = $data->$field;
        }
        if(isset($row->supportDepts) && is_array($row->supportDepts)) $row->supportDepts = implode(',', $row->supportDepts);
        $exist = $this->dao->select('id')->from(TABLE_JX_PROJECT)->where('project')->eq($projectID)->fetch();
        if($exist)
        {
            $this->dao->update(TABLE_JX_PROJECT)->data($row)->where('id')->eq($exist->id)->exec();
            return (int)$exist->id;
        }
        $row->project = $projectID;
        if(empty($row->health)) $row->health = 'green';
        $this->dao->insert(TABLE_JX_PROJECT)->data($row)->exec();
        return (int)$this->dao->lastInsertID();
    }

    public function getTemplateByCode(string $code): ?object
    {
        $this->ensureSchema();
        $template = $this->dao->select('*')->from(TABLE_JX_TEMPLATE)->where('code')->eq($code)->andWhere('deleted')->eq(0)->fetch();
        if(!$template) return null;
        $template->stages = $this->dao->select('*')->from(TABLE_JX_TEMPLATESTAGE)->where('template')->eq($template->id)->orderBy('order')->fetchAll('id');
        foreach($template->stages as $stage)
        {
            $stage->checks = $this->dao->select('*')->from(TABLE_JX_TEMPLATECHECK)->where('stage')->eq($stage->id)->orderBy('order')->fetchAll();
        }
        return $template;
    }

    /**
     * Create a ZenTao project + default execution + stage gates + tasks from a medical matter.
     *
     * @param  object $matter
     * @param  string $bizType
     * @param  int    $bizID
     * @param  string $templateCode
     * @access public
     * @return int
     */
    public function createLinkedProject(object $matter, string $bizType, int $bizID, string $templateCode): int
    {
        $account = !empty($this->app->user->account) ? $this->app->user->account : 'admin';
        $now     = helper::now();
        $begin   = !empty($matter->begin) ? $matter->begin : date('Y-m-d');
        $end     = !empty($matter->end) ? $matter->end : date('Y-m-d', strtotime('+180 days'));
        $days    = max(1, (int)((strtotime($end) - strtotime($begin)) / 86400));

        $project = new stdclass();
        $project->type          = 'project';
        $project->model         = 'scrum';
        $project->multiple      = 1;
        $project->hasProduct    = empty($matter->product) ? 0 : 1;
        $project->name          = $matter->name;
        $project->code          = $matter->code ?? '';
        $project->begin         = $begin;
        $project->end           = $end;
        $project->days          = $days;
        $project->status        = 'wait';
        $project->acl           = 'open';
        $project->PM            = $matter->owner ?? $account;
        $project->openedBy      = $account;
        $project->openedDate    = $now;
        $project->openedVersion = $this->config->version;
        $project->parent        = 0;
        $project->grade         = 1;
        $project->path          = '';
        $project->vision        = 'rnd';
        $project->storyType     = 'story';
        $project->auth          = 'extend';
        $project->budget        = isset($matter->budget) ? (float)$matter->budget : 0;
        $project->budgetUnit    = 'CNY';
        $project->desc          = $matter->desc ?? '';
        $project->team          = $matter->name;
        $project->pri           = 1;

        $this->dao->insert(TABLE_PROJECT)->data($project)->exec();
        $projectID = (int)$this->dao->lastInsertID();
        $this->dao->update(TABLE_PROJECT)
            ->set('path')->eq(",{$projectID},")
            ->set('`order`')->eq($projectID * 5)
            ->where('id')->eq($projectID)->exec();

        $execution = clone $project;
        $execution->type     = 'sprint';
        $execution->project  = $projectID;
        $execution->parent   = $projectID;
        $execution->grade    = 1;
        $execution->multiple = 1;
        $execution->acl      = 'open';
        unset($execution->model);
        $this->dao->insert(TABLE_PROJECT)->data($execution)->exec();
        $executionID = (int)$this->dao->lastInsertID();
        $this->dao->update(TABLE_PROJECT)
            ->set('path')->eq(",{$projectID},{$executionID},")
            ->where('id')->eq($executionID)->exec();

        if(!empty($matter->product))
        {
            foreach(array($projectID, $executionID) as $objectID)
            {
                $link = new stdclass();
                $link->project = $objectID;
                $link->product = (int)$matter->product;
                $link->branch  = 0;
                $link->plan    = '';
                $this->dao->replace(TABLE_PROJECTPRODUCT)->data($link)->exec();
            }
        }

        $team = new stdclass();
        $team->root    = $projectID;
        $team->type    = 'project';
        $team->account = $project->PM ?: $account;
        $team->role    = 'PM';
        $team->join    = date('Y-m-d');
        $team->days    = $days;
        $team->hours   = 8;
        $this->dao->replace(TABLE_TEAM)->data($team)->exec();
        $team->root = $executionID;
        $team->type = 'execution';
        $this->dao->replace(TABLE_TEAM)->data($team)->exec();

        $extra = new stdclass();
        $extra->bizType      = $bizType;
        $extra->bizID        = $bizID;
        $extra->code         = $matter->code ?? '';
        $extra->leadDept     = $matter->leadDept ?? '';
        $extra->supportDepts = is_array($matter->supportDepts ?? '') ? implode(',', $matter->supportDepts) : ($matter->supportDepts ?? '');
        $extra->goal         = $matter->desc ?? '';
        $extra->health       = 'green';
        $this->saveProjectExtra($projectID, $extra);

        $this->instantiateTemplate($projectID, $executionID, $templateCode, $begin, $end, $project->PM ?: $account);
        $this->refreshProgress($projectID);

        try { $this->loadModel('action')->create('project', $projectID, 'opened'); } catch(Throwable $e) { }

        return $projectID;
    }

    public function instantiateTemplate(int $projectID, int $executionID, string $templateCode, string $begin, string $end, string $owner): void
    {
        $template = $this->getTemplateByCode($templateCode);
        if(!$template || empty($template->stages)) return;
        $count = count($template->stages);
        $span  = max(1, (int)((strtotime($end) - strtotime($begin)) / 86400));
        $step  = max(1, (int)floor($span / $count));
        $i     = 0;
        foreach($template->stages as $tplStage)
        {
            $stageBegin = date('Y-m-d', strtotime($begin) + $i * $step * 86400);
            $stageEnd   = date('Y-m-d', strtotime($begin) + (($i + 1) * $step - 1) * 86400);
            if($i == $count - 1) $stageEnd = $end;

            $task = new stdclass();
            $task->project    = $projectID;
            $task->execution  = $executionID;
            $task->name       = $tplStage->name;
            $task->type       = 'misc';
            $task->pri        = 2;
            $task->status     = $i == 0 ? 'doing' : 'wait';
            $task->estStarted = $stageBegin;
            $task->deadline   = $stageEnd;
            $task->openedBy   = $owner;
            $task->openedDate = helper::now();
            $task->assignedTo = $owner;
            $task->left       = 8;
            $task->estimate   = 8;
            $task->desc       = $tplStage->deliverable;
            $this->dao->insert(TABLE_TASK)->data($task)->exec();
            $taskID = (int)$this->dao->lastInsertID();
            $this->dao->update(TABLE_TASK)->set('path')->eq(",{$taskID},")->where('id')->eq($taskID)->exec();

            $stage = new stdclass();
            $stage->project       = $projectID;
            $stage->templateStage = $tplStage->id;
            $stage->name          = $tplStage->name;
            $stage->order         = $tplStage->order;
            $stage->status        = $i == 0 ? 'doing' : 'wait';
            $stage->needApprove   = $tplStage->needApprove;
            $stage->deliverable   = $tplStage->deliverable;
            $stage->task          = $taskID;
            $stage->begin         = $stageBegin;
            $stage->end           = $stageEnd;
            $this->dao->insert(TABLE_JX_STAGE)->data($stage)->exec();
            $stageID = (int)$this->dao->lastInsertID();

            if(!empty($tplStage->checks))
            {
                foreach($tplStage->checks as $tplCheck)
                {
                    $check = new stdclass();
                    $check->stage    = $stageID;
                    $check->name     = $tplCheck->name;
                    $check->required = $tplCheck->required;
                    $check->order    = $tplCheck->order;
                    $this->dao->insert(TABLE_JX_CHECK)->data($check)->exec();
                }
            }
            $i++;
        }
    }

    public function getStages(int $projectID): array
    {
        $this->ensureSchema();
        $stages = $this->dao->select('*')->from(TABLE_JX_STAGE)->where('project')->eq($projectID)->orderBy('order')->fetchAll('id');
        if(empty($stages)) return array();
        $checks = $this->dao->select('*')->from(TABLE_JX_CHECK)->where('stage')->in(array_keys($stages))->orderBy('order')->fetchGroup('stage');
        foreach($stages as $id => $stage) $stage->checks = $checks[$id] ?? array();
        return $stages;
    }

    public function toggleCheck(int $checkID, int $done): bool
    {
        $account = $this->app->user->account ?? '';
        $this->dao->update(TABLE_JX_CHECK)
            ->set('done')->eq($done)
            ->set('doneBy')->eq($done ? $account : '')
            ->set('doneDate')->eq($done ? helper::now() : null)
            ->where('id')->eq($checkID)->exec();
        $check = $this->dao->select('*')->from(TABLE_JX_CHECK)->where('id')->eq($checkID)->fetch();
        if($check) $this->refreshProgress((int)$this->dao->select('project')->from(TABLE_JX_STAGE)->where('id')->eq($check->stage)->fetch('project'));
        return !dao::isError();
    }

    public function submitStage(int $stageID, string $comment = ''): bool
    {
        $stage = $this->dao->select('*')->from(TABLE_JX_STAGE)->where('id')->eq($stageID)->fetch();
        if(!$stage) return false;
        $required = $this->dao->select('*')->from(TABLE_JX_CHECK)->where('stage')->eq($stageID)->andWhere('required')->eq(1)->andWhere('done')->eq(0)->fetch();
        if($required)
        {
            dao::$errors['checks'] = '请先完成全部必填检查项。';
            return false;
        }
        $account = $this->app->user->account ?? '';
        $status  = $stage->needApprove ? 'submitted' : 'done';
        $this->dao->update(TABLE_JX_STAGE)
            ->set('status')->eq($status)
            ->set('submittedBy')->eq($account)
            ->set('submittedDate')->eq(helper::now())
            ->set('comment')->eq($comment)
            ->where('id')->eq($stageID)->exec();
        $this->writeApproval('jxstage', $stageID, $stageID, 'submit', 'pass', $comment);
        if($status == 'done') $this->openNextStage((int)$stage->project, (int)$stage->order);
        $this->refreshProgress((int)$stage->project);
        return true;
    }

    public function approveStage(int $stageID, string $result, string $comment = ''): bool
    {
        $stage = $this->dao->select('*')->from(TABLE_JX_STAGE)->where('id')->eq($stageID)->fetch();
        if(!$stage) return false;
        $account = $this->app->user->account ?? '';
        $status  = $result == 'pass' ? 'done' : 'rejected';
        $this->dao->update(TABLE_JX_STAGE)
            ->set('status')->eq($status)
            ->set('approvedBy')->eq($account)
            ->set('approvedDate')->eq(helper::now())
            ->set('comment')->eq($comment)
            ->where('id')->eq($stageID)->exec();
        $this->writeApproval('jxstage', $stageID, $stageID, 'approve', $result, $comment);
        if($status == 'done') $this->openNextStage((int)$stage->project, (int)$stage->order);
        $this->refreshProgress((int)$stage->project);
        return true;
    }

    protected function openNextStage(int $projectID, int $currentOrder): void
    {
        $next = $this->dao->select('*')->from(TABLE_JX_STAGE)
            ->where('project')->eq($projectID)
            ->andWhere('`order`')->gt($currentOrder)
            ->orderBy('order')
            ->fetch();
        if($next && $next->status == 'wait')
        {
            $this->dao->update(TABLE_JX_STAGE)->set('status')->eq('doing')->where('id')->eq($next->id)->exec();
            if($next->task) $this->dao->update(TABLE_TASK)->set('status')->eq('doing')->where('id')->eq($next->task)->exec();
        }
    }

    protected function writeApproval(string $objectType, int $objectID, int $stageID, string $action, string $result, string $comment): void
    {
        $row = new stdclass();
        $row->objectType  = $objectType;
        $row->objectID    = $objectID;
        $row->stage       = $stageID;
        $row->action      = $action;
        $row->actor       = $this->app->user->account ?? '';
        $row->result      = $result;
        $row->comment     = $comment;
        $row->createdDate = helper::now();
        $this->dao->insert(TABLE_JX_APPROVAL)->data($row)->exec();
    }

    public function refreshProgress(int $projectID): void
    {
        $stages = $this->dao->select('*')->from(TABLE_JX_STAGE)->where('project')->eq($projectID)->fetchAll();
        $total  = count($stages);
        $done   = 0;
        $today  = date('Y-m-d');
        $health = 'green';
        $blocker = '';
        foreach($stages as $stage)
        {
            if(in_array($stage->status, array('done', 'approved'))) $done++;
            if($stage->status == 'rejected') { $health = 'red'; $blocker = $stage->name . '被驳回'; }
            if(!in_array($stage->status, array('done', 'approved')) && $stage->end && $stage->end < $today)
            {
                $health = 'red';
                $blocker = $blocker ?: ($stage->name . '逾期');
            }
            elseif($health != 'red' && !in_array($stage->status, array('done', 'approved')) && $stage->end && $stage->end <= date('Y-m-d', strtotime('+7 days')))
            {
                $health = 'yellow';
            }
        }
        $progress = $total ? round($done * 100 / $total, 2) : 0;
        $this->dao->update(TABLE_JX_PROJECT)
            ->set('progress')->eq($progress)
            ->set('health')->eq($health)
            ->set('blocker')->eq($blocker)
            ->where('project')->eq($projectID)->exec();
        $this->dao->update(TABLE_PROJECT)->set('progress')->eq($progress)->where('id')->eq($projectID)->exec();
        if($progress >= 100) $this->dao->update(TABLE_PROJECT)->set('status')->eq('closed')->where('id')->eq($projectID)->andWhere('status')->ne('closed')->exec();
        elseif($done > 0) $this->dao->update(TABLE_PROJECT)->set('status')->eq('doing')->where('id')->eq($projectID)->andWhere('status')->eq('wait')->exec();
    }

    public function getCosts(int $projectID): array
    {
        $this->ensureSchema();
        return $this->dao->select('*')->from(TABLE_JX_COST)->where('project')->eq($projectID)->andWhere('deleted')->eq(0)->orderBy('occurDate_desc,id_desc')->fetchAll();
    }

    public function addCost(object $cost): int
    {
        $cost->createdBy   = $this->app->user->account ?? '';
        $cost->createdDate = helper::now();
        $this->dao->insert(TABLE_JX_COST)->data($cost)->exec();
        return (int)$this->dao->lastInsertID();
    }

    public function costSummary(int $projectID): object
    {
        $budget = (float)$this->dao->select('budget')->from(TABLE_PROJECT)->where('id')->eq($projectID)->fetch('budget');
        $actual = (float)$this->dao->select('SUM(amount) AS amount')->from(TABLE_JX_COST)->where('project')->eq($projectID)->andWhere('deleted')->eq(0)->fetch('amount');
        $sum = new stdclass();
        $sum->budget = $budget;
        $sum->actual = $actual;
        $sum->delta  = $budget - $actual;
        return $sum;
    }

    public function syncMatterStatus(string $table, int $id, int $projectID): void
    {
        $extra = $this->getProjectExtra($projectID);
        $status = 'doing';
        if($extra)
        {
            if((float)$extra->progress >= 100) $status = 'done';
            elseif($extra->health == 'red') $status = 'blocked';
            elseif((float)$extra->progress > 0) $status = 'doing';
            else $status = 'wait';
        }
        $this->dao->update($table)->set('status')->eq($status)->where('id')->eq($id)->exec();
    }

    public function appendDisplayFields(array $rows, string $bizType = ''): array
    {
        $products = $this->getProductPairs();
        $users    = $this->dao->select('account,realname')->from(TABLE_USER)->where('deleted')->eq(0)->fetchPairs();
        foreach($rows as $row)
        {
            $row->productName = $products[$row->product ?? 0] ?? '';
            $row->ownerName   = $users[$row->owner ?? ''] ?? ($row->owner ?? '');
            $row->progress    = 0;
            $row->health      = 'green';
            if(!empty($row->project))
            {
                $extra = $this->getProjectExtra((int)$row->project);
                if($extra)
                {
                    $row->progress = $extra->progress;
                    $row->health   = $extra->health;
                    $row->blocker  = $extra->blocker;
                }
            }
        }
        return $rows;
    }

    public function getDashboard(array $filters = array()): object
    {
        $this->ensureSchema();
        $data = new stdclass();
        $data->filters = $filters;

        $extras = $this->dao->select('t1.*, t2.name, t2.status AS projectStatus, t2.begin, t2.end, t2.budget, t2.PM, t2.deleted')
            ->from(TABLE_JX_PROJECT)->alias('t1')
            ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
            ->where('t1.deleted')->eq(0)
            ->andWhere('t2.deleted')->eq(0)
            ->beginIF(!empty($filters['bizType']))->andWhere('t1.bizType')->eq($filters['bizType'])->fi()
            ->beginIF(!empty($filters['dept']))->andWhere('t1.leadDept')->eq($filters['dept'])->fi()
            ->beginIF(!empty($filters['status']))->andWhere('t2.status')->eq($filters['status'])->fi()
            ->fetchAll();

        $today = date('Y-m-d');
        $data->total     = count($extras);
        $data->byHealth  = array('green' => 0, 'yellow' => 0, 'red' => 0);
        $data->byBiz     = array();
        $data->byStatus  = array();
        $data->byDept    = array();
        $data->overdue   = array();
        $data->budget    = 0;
        $data->actual    = 0;
        $data->projects  = $extras;

        $projectIDs = array();
        foreach($extras as $row)
        {
            $projectIDs[] = $row->project;
            $health = $row->health ?: 'green';
            if(!isset($data->byHealth[$health])) $data->byHealth[$health] = 0;
            $data->byHealth[$health]++;
            $biz = $row->bizType ?: 'other';
            $data->byBiz[$biz] = ($data->byBiz[$biz] ?? 0) + 1;
            $st = $row->projectStatus ?: 'wait';
            $data->byStatus[$st] = ($data->byStatus[$st] ?? 0) + 1;
            $dept = $row->leadDept ?: '未分配';
            if(!isset($data->byDept[$dept])) $data->byDept[$dept] = array('count' => 0, 'budget' => 0, 'actual' => 0, 'red' => 0);
            $data->byDept[$dept]['count']++;
            $data->byDept[$dept]['budget'] += (float)$row->budget;
            if($health == 'red') $data->byDept[$dept]['red']++;
            $data->budget += (float)$row->budget;
            if($row->end && $row->end < $today && !in_array($row->projectStatus, array('closed', 'done'))) $data->overdue[] = $row;
        }

        if($projectIDs)
        {
            $costs = $this->dao->select('project, SUM(amount) AS amount')->from(TABLE_JX_COST)
                ->where('deleted')->eq(0)->andWhere('project')->in($projectIDs)->groupBy('project')->fetchPairs();
            foreach($extras as $row)
            {
                $row->actual = (float)($costs[$row->project] ?? 0);
                $data->actual += $row->actual;
                $dept = $row->leadDept ?: '未分配';
                $data->byDept[$dept]['actual'] += $row->actual;
            }
        }
        $data->delta = $data->budget - $data->actual;

        $data->certExpiring = $this->dao->select('t1.*, t2.name')
            ->from(TABLE_JX_PRODUCT)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product = t2.id')
            ->where('t1.deleted')->eq(0)
            ->andWhere('t1.certValidTo')->ne('')
            ->andWhere('t1.certValidTo')->le(date('Y-m-d', strtotime('+90 days')))
            ->andWhere('t1.certValidTo')->ge($today)
            ->fetchAll();

        $data->windows = $this->dao->select('*')->from(TABLE_JX_MARKETACCESS)
            ->where('deleted')->eq(0)
            ->andWhere('windowEnd')->ne('')
            ->andWhere('windowEnd')->le(date('Y-m-d', strtotime('+14 days')))
            ->andWhere('windowEnd')->ge($today)
            ->fetchAll();

        $data->funnel = array();
        $admits = $this->dao->select('status, COUNT(*) AS total')->from(TABLE_JX_ADMISSION)->where('deleted')->eq(0)->groupBy('status')->fetchPairs();
        $data->funnel = $admits;

        $data->blockers = array();
        foreach($extras as $row)
        {
            if($row->health == 'red' || !empty($row->blocker)) $data->blockers[] = $row;
        }

        $data->stages = array();
        if($projectIDs)
        {
            $data->stages = $this->dao->select('name, status, COUNT(*) AS total')->from(TABLE_JX_STAGE)
                ->where('project')->in($projectIDs)->groupBy('name,status')->fetchAll();
        }

        return $data;
    }
}
