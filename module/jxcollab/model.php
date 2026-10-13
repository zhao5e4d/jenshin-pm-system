<?php
declare(strict_types=1);
/**
 * 任务协同：任务下的过程管理。一个任务可以开多张协同单，每张单由若干协同人反馈，
 * 全员给出最终反馈后自动关闭并计算结论。
 */
class jxcollabModel extends model
{
    /**
     * 建表。任务详情等页面不走 jx 模块构造函数，查询前先确保表存在。
     *
     * @access public
     * @return bool
     */
    public function ensureSchema(): bool
    {
        static $ready = null;
        if($ready !== null) return $ready;

        $ready = (bool)$this->loadModel('jxcore')->ensureSchema();
        return $ready;
    }

    /**
     * 获取协同单，带协同人。
     *
     * @param  int          $collabID
     * @access public
     * @return object|false
     */
    public function getByID(int $collabID): object|false
    {
        if(!$this->ensureSchema()) return false;

        $collab = $this->dao->select('*')->from(TABLE_JX_COLLAB)
            ->where('id')->eq($collabID)
            ->andWhere('deleted')->eq('0')
            ->fetch();
        if(!$collab) return false;

        $collab->members = $this->getMembers($collabID);
        $collab->files   = $this->loadModel('file')->getByObject('jxcollab', $collabID);
        $collab->taskName = (string)$this->dao->select('name')->from(TABLE_TASK)->where('id')->eq((int)$collab->task)->fetch('name');
        return $collab;
    }

    /**
     * 获取协同人列表。
     *
     * @param  int    $collabID
     * @access public
     * @return array
     */
    public function getMembers(int $collabID): array
    {
        /* content / extra 是 text 字段，fetchAll 默认会把 text 排除，这里必须显式列出。 */
        $members = $this->dao->select('id, collab, account, attitude, content, extra, settled, todo, feedbackDate, readDate')->from(TABLE_JX_COLLABMEMBER)
            ->where('collab')->eq($collabID)
            ->orderBy('id')
            ->fetchAll('id');
        $fileGroups = $members ? $this->loadModel('file')->groupByObjectID('jxcollabfeedback', array_keys($members)) : array();
        foreach($members as $member)
        {
            $member->extra = $this->decodeExtra($member->extra);
            $member->files = zget($fileGroups, $member->id, array());
        }
        return $members;
    }

    /**
     * 获取某个任务下的协同单，含子任务时由调用方自行合并。
     *
     * @param  int    $taskID
     * @access public
     * @return array
     */
    public function getListByTask(int $taskID): array
    {
        if(!$this->ensureSchema() || $taskID <= 0) return array();

        $collabs = $this->dao->select('id, project, execution, task, type, title, `desc`, deadline, status, result, openedBy, openedDate, closedBy, closedDate')->from(TABLE_JX_COLLAB)
            ->where('task')->eq($taskID)
            ->andWhere('deleted')->eq('0')
            ->orderBy('id_desc')
            ->fetchAll('id');
        if(!$collabs) return array();

        $members = $this->dao->select('id, collab, account, attitude, content, extra, settled, todo, feedbackDate')->from(TABLE_JX_COLLABMEMBER)
            ->where('collab')->in(array_keys($collabs))
            ->orderBy('id')
            ->fetchGroup('collab', 'id');

        $memberIDs = array();
        foreach($members as $collabMembers) foreach($collabMembers as $member) $memberIDs[] = (int)$member->id;
        $collabFiles = $this->loadModel('file')->groupByObjectID('jxcollab', array_keys($collabs));
        $memberFiles = $memberIDs ? $this->file->groupByObjectID('jxcollabfeedback', $memberIDs) : array();

        foreach($collabs as $collab)
        {
            $collab->files   = zget($collabFiles, $collab->id, array());
            $collab->members = zget($members, $collab->id, array());
            foreach($collab->members as $member)
            {
                $member->extra = $this->decodeExtra($member->extra);
                $member->files = zget($memberFiles, $member->id, array());
            }
        }
        return $collabs;
    }

    /**
     * 按任务统计协同单：总数、进行中、已反馈人数 / 总人数。列表页打标用。
     *
     * @param  array  $taskIdList
     * @access public
     * @return array
     */
    public function getSummaryByTasks(array $taskIdList): array
    {
        $taskIdList = array_filter(array_map('intval', $taskIdList));
        if(!$taskIdList || !$this->ensureSchema()) return array();

        $rows = $this->dao->select('t1.task, t1.id, t1.status, count(t2.id) as total, sum(t2.settled) as settled')
            ->from(TABLE_JX_COLLAB)->alias('t1')
            ->leftJoin(TABLE_JX_COLLABMEMBER)->alias('t2')->on('t1.id = t2.collab')
            ->where('t1.task')->in($taskIdList)
            ->andWhere('t1.deleted')->eq('0')
            ->groupBy('t1.id')
            ->fetchAll('id');

        $summary = array();
        foreach($rows as $row)
        {
            $taskID = (int)$row->task;
            if(!isset($summary[$taskID])) $summary[$taskID] = (object)array('total' => 0, 'doing' => 0, 'members' => 0, 'settled' => 0);
            $summary[$taskID]->total ++;
            if($row->status == 'doing') $summary[$taskID]->doing ++;
            $summary[$taskID]->members += (int)$row->total;
            $summary[$taskID]->settled += (int)$row->settled;
        }
        return $summary;
    }

    /**
     * 获取任务下未关闭的协同单，关闭任务时提示用。
     *
     * @param  int    $taskID
     * @access public
     * @return array
     */
    public function getUnfinishedByTask(int $taskID): array
    {
        if(!$this->ensureSchema() || $taskID <= 0) return array();

        return $this->dao->select('id, task, type, title, deadline, status, result, openedBy')->from(TABLE_JX_COLLAB)
            ->where('task')->eq($taskID)
            ->andWhere('deleted')->eq('0')
            ->andWhere('(status')->eq('doing')
            ->orWhere('result')->eq('fail')
            ->markRight(1)
            ->orderBy('id')
            ->fetchAll('id');
    }

    /**
     * 我还没给出最终反馈的协同单，工作台「协同事项」和待办用。
     *
     * @param  string $account
     * @param  int    $limit
     * @access public
     * @return array
     */
    public function getMyPendingList(string $account = '', int $limit = 0): array
    {
        if(!$this->ensureSchema()) return array();
        if(empty($account)) $account = (string)$this->app->user->account;
        if(empty($account)) return array();

        $collabs = $this->getMyPendingQuery($account, 't1.id, t1.project, t1.task, t1.type, t1.title, t1.deadline, t1.status, t1.openedBy, t2.attitude, t2.settled, t2.readDate, t3.name as taskName, t4.name as projectName')
            ->orderBy('t1.deadline,t1.id_desc')
            ->beginIF($limit > 0)->limit($limit)->fi()
            ->fetchAll('id');

        foreach($collabs as $collab)
        {
            $collab->typeName = zget($this->lang->jxcollab->typeList, $collab->type, $collab->type);
            $collab->isUnread = empty($collab->readDate) || helper::isZeroDate((string)$collab->readDate);
        }
        return $collabs;
    }

    /**
     * 工作台数量统计与列表共用筛选条件，不受展示条数限制。
     */
    public function getMyPendingCount(string $account = ''): int
    {
        if(!$this->ensureSchema()) return 0;
        if(empty($account)) $account = (string)$this->app->user->account;
        if(empty($account)) return 0;
        return (int)$this->getMyPendingQuery($account, 'COUNT(DISTINCT t1.id) AS total')->fetch('total');
    }

    protected function getMyPendingQuery(string $account, string $fields): dao
    {
        return $this->dao->select($fields)
            ->from(TABLE_JX_COLLAB)->alias('t1')
            ->leftJoin(TABLE_JX_COLLABMEMBER)->alias('t2')->on('t1.id = t2.collab')
            ->leftJoin(TABLE_TASK)->alias('t3')->on('t1.task = t3.id')
            ->leftJoin(TABLE_PROJECT)->alias('t4')->on('t1.project = t4.id')
            ->where('t1.deleted')->eq('0')
            ->andWhere('t1.status')->eq('doing')
            ->andWhere('t2.account')->eq($account)
            ->andWhere('t2.settled')->eq('0');
    }

    /**
     * 仅标记当前登录协同人已读，重复查看不覆盖首次阅读时间。
     */
    public function markRead(int $collabID): bool
    {
        if(!$this->ensureSchema()) return false;
        $account = (string)$this->app->user->account;
        if($account === '') return false;
        $throwError = $this->app->throwError;
        $this->app->throwError = true;
        try
        {
            $member = $this->dao->select('id, readDate')->from(TABLE_JX_COLLABMEMBER)
                ->where('collab')->eq($collabID)->andWhere('account')->eq($account)->fetch();
            if(!$member) return false;
            if(!empty($member->readDate) && !helper::isZeroDate((string)$member->readDate)) return true;

            $this->dao->update(TABLE_JX_COLLABMEMBER)->set('readDate')->eq(helper::now())
                ->where('id')->eq((int)$member->id)->andWhere('account')->eq($account)
                ->andWhere('(readDate IS NULL OR readDate')->eq('0000-00-00 00:00:00')->markRight(1)->exec();
            return !dao::isError();
        }
        catch(Throwable $e)
        {
            return false;
        }
        finally
        {
            $this->app->throwError = $throwError;
        }
    }

    /**
     * 发起协同单，并给每位协同人发站内消息。
     *
     * @param  object     $task
     * @param  object     $data      表单数据
     * @param  array      $accounts  协同人账号
     * @access public
     * @return int|false
     */
    public function create(object $task, object $data, array $accounts): int|false
    {
        if(!$this->ensureSchema()) return false;

        $collab = new stdclass();
        $collab->project      = (int)$task->project;
        $collab->execution    = (int)$task->execution;
        $collab->task         = (int)$task->id;
        $collab->type         = $data->type;
        $collab->title        = $data->title;
        $collab->desc         = (string)$data->desc;
        $collab->deadline     = empty($data->deadline) ? null : $data->deadline;
        $collab->status       = 'doing';
        $collab->result       = '';
        $collab->openedBy     = $this->app->user->account;
        $collab->openedDate   = helper::now();
        $collab->closedBy     = '';
        $collab->closedDate   = null;
        $collab->closedReason = '';
        $collab->deleted      = '0';

        $this->dao->insert(TABLE_JX_COLLAB)->data($collab)->exec();
        if(dao::isError()) return false;

        $collabID = (int)$this->dao->lastInsertID();
        $collab->id = $collabID;
        $this->loadModel('file')->saveUpload('jxcollab', $collabID);
        $this->addMembers($collab, $task, $accounts);

        $this->loadModel('action')->create('jxcollab', $collabID, 'opened');
        $this->action->create('task', (int)$task->id, 'jxcollabopened', '', $collab->title);
        return $collabID;
    }

    /**
     * 追加协同人并发送站内消息，已存在的账号跳过。
     *
     * @param  object $collab
     * @param  object $task
     * @param  array  $accounts
     * @access public
     * @return void
     */
    public function addMembers(object $collab, object $task, array $accounts): void
    {
        $existed = $this->dao->select('account')->from(TABLE_JX_COLLABMEMBER)->where('collab')->eq((int)$collab->id)->fetchPairs('account', 'account');
        foreach($accounts as $account)
        {
            $account = trim((string)$account);
            if($account === '' || isset($existed[$account])) continue;

            $member = new stdclass();
            $member->collab       = (int)$collab->id;
            $member->account      = $account;
            $member->attitude     = '';
            $member->content      = '';
            $member->extra        = '';
            $member->settled      = '0';
            $member->todo         = 0;
            $member->feedbackDate = null;
            $this->dao->insert(TABLE_JX_COLLABMEMBER)->data($member)->exec();
            if(dao::isError()) continue;

            $this->sendNotice($collab, $task, $account);
            $existed[$account] = $account;
        }
    }

    /**
     * 移除协同人，连带删除其待办。
     *
     * @param  int    $collabID
     * @param  array  $accounts 保留的账号
     * @access public
     * @return void
     */
    public function removeMembers(int $collabID, array $accounts): void
    {
        $members = $this->dao->select('*')->from(TABLE_JX_COLLABMEMBER)->where('collab')->eq($collabID)->fetchAll('id');
        foreach($members as $member)
        {
            if(in_array($member->account, $accounts, true)) continue;
            if($member->todo) $this->dao->delete()->from(TABLE_TODO)->where('id')->eq((int)$member->todo)->exec();
            $this->dao->delete()->from(TABLE_JX_COLLABMEMBER)->where('id')->eq((int)$member->id)->exec();
        }
    }

    /**
     * 保存某位协同人的反馈，并判断是否可以自动关闭。
     *
     * @param  object $collab
     * @param  string $account
     * @param  object $data
     * @access public
     * @return bool
     */
    public function saveFeedback(object $collab, string $account, object $data): bool
    {
        $member = $this->dao->select('*')->from(TABLE_JX_COLLABMEMBER)
            ->where('collab')->eq((int)$collab->id)
            ->andWhere('account')->eq($account)
            ->fetch();
        if(!$member) return false;

        $member->files = $this->loadModel('file')->getByObject('jxcollabfeedback', (int)$member->id);
        $typeConfig = $this->getTypeConfig($collab->type);
        $attitude   = (string)$data->attitude;
        if(!empty($typeConfig['hideAttitude'])) $attitude = (string)key($typeConfig['attitudes']);
        if(!isset($typeConfig['attitudes'][$attitude])) return false;

        $extra = array();
        foreach(array_keys($typeConfig['fields']) as $field) $extra[$field] = isset($data->$field) ? (string)$data->$field : '';

        $this->dao->update(TABLE_JX_COLLABMEMBER)
            ->set('attitude')->eq($attitude)
            ->set('content')->eq((string)$data->content)
            ->set('extra')->eq(json_encode($extra, JSON_UNESCAPED_UNICODE))
            ->set('settled')->eq(!empty($typeConfig['attitudes'][$attitude]['settled']) ? 1 : 0)
            ->set('feedbackDate')->eq(helper::now())
            ->where('id')->eq((int)$member->id)
            ->exec();
        if(dao::isError()) return false;

        $this->loadModel('file')->processFileDiffsForObject('jxcollabfeedback', $member, $data);
        if(!empty($typeConfig['attitudes'][$attitude]['settled']) && $member->todo) $this->finishTodo((int)$member->todo);

        $this->loadModel('action')->create('jxcollab', (int)$collab->id, 'jxcollabfeedback', (string)$data->content, zget($this->lang->jxcollab->attitudeList, $attitude, $attitude));
        $this->closeIfSettled($collab);
        return true;
    }

    /**
     * 协同人全部给出最终反馈时自动关闭并算出结论。
     *
     * @param  object $collab
     * @access public
     * @return bool   是否触发了自动关闭
     */
    public function closeIfSettled(object $collab): bool
    {
        if($collab->status != 'doing') return false;

        $members = $this->dao->select('attitude, settled')->from(TABLE_JX_COLLABMEMBER)->where('collab')->eq((int)$collab->id)->fetchAll();
        if(empty($members)) return false;
        foreach($members as $member) if(empty($member->settled)) return false;

        $this->dao->update(TABLE_JX_COLLAB)
            ->set('status')->eq('closed')
            ->set('result')->eq($this->computeResult($collab->type, $members))
            ->set('closedBy')->eq((string)$this->app->user->account)
            ->set('closedDate')->eq(helper::now())
            ->set('closedReason')->eq('auto')
            ->where('id')->eq((int)$collab->id)
            ->exec();

        $this->closeTodos((int)$collab->id);
        $this->loadModel('action')->create('jxcollab', (int)$collab->id, 'closed');
        $this->action->create('task', (int)$collab->task, 'jxcollabclosed', '', $collab->title);
        return true;
    }

    /**
     * 取消协同单，未反馈的待办一起关掉。
     *
     * @param  object $collab
     * @access public
     * @return bool
     */
    public function cancel(object $collab): bool
    {
        $this->dao->update(TABLE_JX_COLLAB)
            ->set('status')->eq('canceled')
            ->set('closedBy')->eq((string)$this->app->user->account)
            ->set('closedDate')->eq(helper::now())
            ->set('closedReason')->eq('manual')
            ->where('id')->eq((int)$collab->id)
            ->exec();
        if(dao::isError()) return false;

        $this->closeTodos((int)$collab->id);
        $this->loadModel('action')->create('jxcollab', (int)$collab->id, 'canceled');
        $this->action->create('task', (int)$collab->task, 'jxcollabcanceled', '', $collab->title);
        return true;
    }

    /**
     * 结论：有一个负面表态就算未通过，意见征询类只记已完成。
     *
     * @param  string $type
     * @param  array  $members
     * @access public
     * @return string
     */
    public function computeResult(string $type, array $members): string
    {
        $typeConfig = $this->getTypeConfig($type);
        foreach($members as $member)
        {
            $polarity = $typeConfig['attitudes'][$member->attitude]['polarity'] ?? 'neutral';
            if($polarity == 'negative') return 'fail';
        }
        return !empty($typeConfig['hideAttitude']) ? 'done' : 'pass';
    }

    /**
     * 协同类型配置，类型不存在时退回核验确认。
     *
     * @param  string $type
     * @access public
     * @return array
     */
    public function getTypeConfig(string $type): array
    {
        $types  = $this->config->jxcollab->types;
        $config = isset($types[$type]) ? $types[$type] : $types['verify'];
        if(!isset($config['fields']))          $config['fields'] = array();
        if(!isset($config['contentRequired'])) $config['contentRequired'] = false;
        return $config;
    }

    /**
     * 某个表态是否要求填写反馈内容。
     *
     * @param  string $type
     * @param  string $attitude
     * @access public
     * @return bool
     */
    public function isContentRequired(string $type, string $attitude): bool
    {
        $required = $this->getTypeConfig($type)['contentRequired'];
        if(is_array($required)) return in_array($attitude, $required, true);
        return (bool)$required;
    }

    /**
     * 能否维护协同单：发起人、任务指派人 / 创建人、项目负责人和管理员。
     *
     * @param  object      $collab
     * @param  object|null $task
     * @access public
     * @return bool
     */
    public function canManage(object $collab, ?object $task = null): bool
    {
        $account = (string)$this->app->user->account;
        if(empty($account)) return false;
        if(!empty($this->app->user->admin)) return true;
        if($collab->openedBy == $account) return true;

        if(empty($task)) $task = $this->dao->select('id, assignedTo, openedBy, project')->from(TABLE_TASK)->where('id')->eq((int)$collab->task)->fetch();
        if(empty($task)) return false;
        if($task->assignedTo == $account || $task->openedBy == $account) return true;

        $pm = (string)$this->dao->select('PM')->from(TABLE_PROJECT)->where('id')->eq((int)$task->project)->fetch('PM');
        return $pm !== '' && $pm == $account;
    }

    /**
     * 能否发起协同：任务指派人 / 创建人、项目负责人和管理员。
     *
     * @param  object $task
     * @access public
     * @return bool
     */
    public function canCreate(object $task): bool
    {
        $account = (string)$this->app->user->account;
        if(empty($account)) return false;
        if(!empty($this->app->user->admin)) return true;
        if(common::hasPriv('jxcollab', 'create')) return true;
        if(isset($task->assignedTo) && $task->assignedTo == $account) return true;
        if(isset($task->openedBy) && $task->openedBy == $account) return true;

        $pm = (string)$this->dao->select('PM')->from(TABLE_PROJECT)->where('id')->eq((int)$task->project)->fetch('PM');
        return $pm !== '' && $pm == $account;
    }

    /**
     * 可选协同人：阶段团队 + 项目团队。
     *
     * @param  object $task
     * @access public
     * @return array
     */
    public function getCandidateUsers(object $task): array
    {
        $users = $this->loadModel('user')->getTeamMemberPairs((int)$task->execution, 'execution');
        if((int)$task->project && (int)$task->project != (int)$task->execution)
        {
            $users += $this->user->getTeamMemberPairs((int)$task->project, 'project');
        }
        if(empty($users)) $users = $this->user->getPairs('noclosed,nodeleted,noletter');
        unset($users['']);
        return $users;
    }

    /**
     * 详情页操作按钮：任务已关闭 / 已取消时不再发起协同。
     *
     * @param  object $task
     * @param  string $action
     * @access public
     * @return bool
     */
    public function isClickable(object $task, string $action): bool
    {
        $action = strtolower($action);
        if($action != 'collab') return true;
        if(!empty($task->deleted)) return false;
        if(isset($task->status) && in_array($task->status, array('closed', 'cancel'))) return false;
        return $this->canCreate($task);
    }

    /**
     * 给协同人发一条站内消息，点开就能进协同单填反馈。
     * 直接写入 zt_notify，不走后台消息设置开关。
     *
     * @param  object $collab
     * @param  object $task
     * @param  string $account
     * @access protected
     * @return void
     */
    protected function sendNotice(object $collab, object $task, string $account): void
    {
        $actor = (string)$this->app->user->account;
        if($account === '' || $account === $actor) return;

        $this->app->loadConfig('mail');
        $sysURL = zget($this->config->mail, 'domain', common::getSysURL());

        /* 发起协同是在弹窗里提交的，去掉 onlybody 后链接才能打开完整页面。 */
        $isonlybody = isInModal();
        if($isonlybody) unset($_GET['onlybody']);

        /* #app=my 让消息中心在工作台里打开协同单，否则未知模块会被丢掉。 */
        $url = helper::createLink('jxcollab', 'view', "collabID={$collab->id}") . '#app=my';
        if($isonlybody) $_GET['onlybody'] = 'yes';

        $actorName = trim((string)($this->app->user->realname ?? ''));
        if($actorName === '') $actorName = $actor;
        $typeName = zget($this->lang->jxcollab->typeList, $collab->type, $collab->type);
        $link     = (strpos($url, $sysURL) === 0 ? '' : $sysURL) . $url;
        $data     = sprintf($this->lang->jxcollab->noticeText, $actorName, (string)$task->name, $typeName)
            . html::a($link, "[#{$collab->id}::{$collab->title}]");

        $notify = new stdclass();
        $notify->objectType  = 'message';
        $notify->objectID    = 0;
        $notify->action      = 0;
        $notify->toList      = ",{$account},";
        $notify->data        = $data;
        $notify->status      = 'wait';
        $notify->createdBy   = $actor;
        $notify->createdDate = helper::now();
        $this->dao->insert(TABLE_NOTIFY)->data($notify)->exec();
        /* 消息写失败不影响协同单本身，清掉错误以免挡住后续协同人。 */
        dao::$errors = array();
    }

    /**
     * 协同人给出最终反馈，待办置为已完成。
     *
     * @param  int    $todoID
     * @access protected
     * @return void
     */
    protected function finishTodo(int $todoID): void
    {
        $this->dao->update(TABLE_TODO)
            ->set('status')->eq('done')
            ->set('finishedBy')->eq((string)$this->app->user->account)
            ->set('finishedDate')->eq(helper::now())
            ->where('id')->eq($todoID)
            ->andWhere('status')->ne('done')
            ->exec();
    }

    /**
     * 协同单关闭 / 取消后，剩下的待办一并关掉。
     *
     * @param  int    $collabID
     * @access protected
     * @return void
     */
    protected function closeTodos(int $collabID): void
    {
        $todoIdList = $this->dao->select('todo')->from(TABLE_JX_COLLABMEMBER)
            ->where('collab')->eq($collabID)
            ->andWhere('todo')->ne(0)
            ->fetchPairs('todo', 'todo');
        if(empty($todoIdList)) return;

        $this->dao->update(TABLE_TODO)
            ->set('status')->eq('closed')
            ->set('closedBy')->eq((string)$this->app->user->account)
            ->set('closedDate')->eq(helper::now())
            ->where('id')->in($todoIdList)
            ->andWhere('status')->in('wait,doing')
            ->exec();
    }

    /**
     * extra 字段存 JSON，读出来统一成数组。
     *
     * @param  string|null $extra
     * @access protected
     * @return array
     */
    protected function decodeExtra(?string $extra): array
    {
        if(empty($extra)) return array();
        $decoded = json_decode($extra, true);
        return is_array($decoded) ? $decoded : array();
    }
}
