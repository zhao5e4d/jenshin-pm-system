<?php
declare(strict_types=1);
/**
 * 任务协同：在任务下发起协同单、协同人反馈、查看结论。
 */
class jxcollab extends control
{
    public function __construct(string $moduleName = '', string $methodName = '', string $appName = '')
    {
        parent::__construct($moduleName, $methodName, $appName);
        $this->jxcollab->ensureSchema();
        $this->app->loadLang('task');
    }

    /**
     * 在任务下发起协同单。
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function create(int $taskID)
    {
        $task = $this->loadModel('task')->fetchByID($taskID);
        if(empty($task)) return $this->send(array('result' => 'fail', 'message' => $this->lang->task->notFound));
        if(!$this->jxcollab->canCreate($task)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->cannotManage));

        if($_POST)
        {
            $data     = form::data($this->config->jxcollab->form->create)->get();
            $accounts = array_filter(array_unique((array)$data->members));
            if(empty($accounts)) return $this->send(array('result' => 'fail', 'message' => array('members[]' => $this->lang->jxcollab->noMember)));

            $collabID = $this->jxcollab->create($task, $data, $accounts);
            if(!$collabID) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => true, 'load' => true));
        }

        $this->view->title = $this->lang->jxcollab->create;
        $this->view->task  = $task;
        $this->view->users = $this->jxcollab->getCandidateUsers($task);
        $this->view->collab = null;
        $this->display();
    }

    /**
     * 编辑协同单：改事项、说明、截止日期和协同人。
     *
     * @param  int    $collabID
     * @access public
     * @return void
     */
    public function edit(int $collabID)
    {
        $collab = $this->jxcollab->getByID($collabID);
        if(empty($collab)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->notFound));

        $task = $this->loadModel('task')->fetchByID((int)$collab->task);
        if(!$this->jxcollab->canManage($collab, $task ?: null)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->cannotManage));
        if($collab->status != 'doing') return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->closedAlready));

        if($_POST)
        {
            $data     = form::data($this->config->jxcollab->form->edit)->get();
            $accounts = array_values(array_filter(array_unique((array)$data->members)));
            if(empty($accounts)) return $this->send(array('result' => 'fail', 'message' => array('members[]' => $this->lang->jxcollab->noMember)));

            $this->dao->update(TABLE_JX_COLLAB)
                ->set('title')->eq($data->title)
                ->set('desc')->eq((string)$data->desc)
                ->set('deadline')->eq(empty($data->deadline) ? null : $data->deadline)
                ->where('id')->eq($collabID)
                ->exec();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->jxcollab->removeMembers($collabID, $accounts);
            if($task) $this->jxcollab->addMembers($collab, $task, $accounts);
            $this->loadModel('file')->processFileDiffsForObject('jxcollab', $collab, $data);
            $this->loadModel('action')->create('jxcollab', $collabID, 'edited');

            $collab = $this->jxcollab->getByID($collabID);
            if($collab) $this->jxcollab->closeIfSettled($collab);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => true, 'load' => true));
        }

        $this->view->title  = $this->lang->jxcollab->edit;
        $this->view->task   = $task;
        $this->view->collab = $collab;
        $this->view->users  = $task ? $this->jxcollab->getCandidateUsers($task) : $this->loadModel('user')->getPairs('noclosed,nodeleted,noletter');
        $this->display('jxcollab', 'create');
    }

    /**
     * 协同单详情：协同人表态一览，本人可直接填反馈。
     *
     * @param  int    $collabID
     * @access public
     * @return void
     */
    public function view(int $collabID)
    {
        $collab = $this->jxcollab->getByID($collabID);
        if(empty($collab))
        {
            if($_POST) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->notFound));
            return print($this->lang->jxcollab->notFound);
        }

        $account  = (string)$this->app->user->account;
        $isMember = false;
        $mine     = null;
        foreach($collab->members as $member)
        {
            if($member->account != $account) continue;
            $isMember = true;
            $mine     = $member;
        }

        if($_POST)
        {
            if(!$isMember || $this->post->action != 'markRead') return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->readDenied));
            if(!$this->jxcollab->markRead($collabID)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->readFailed));
            return $this->send(array('result' => 'success', 'collabID' => $collabID));
        }

        $this->view->title      = $collab->title;
        $this->view->collab     = $collab;
        $this->view->typeConfig = $this->jxcollab->getTypeConfig($collab->type);
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->isMember   = $isMember;
        $this->view->member     = $mine;
        $this->view->canManage  = $this->jxcollab->canManage($collab);
        $this->display();
    }

    /**
     * 协同人填写反馈。关闭前可以反复修改。
     *
     * @param  int    $collabID
     * @access public
     * @return void
     */
    public function feedback(int $collabID)
    {
        $collab = $this->jxcollab->getByID($collabID);
        if(empty($collab)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->notFound));
        if($collab->status != 'doing') return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->closedAlready));

        $account = (string)$this->app->user->account;
        $mine    = null;
        foreach($collab->members as $member) if($member->account == $account) $mine = $member;
        if(empty($mine)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->denied));

        $typeConfig = $this->jxcollab->getTypeConfig($collab->type);
        if($_POST)
        {
            $data = fixer::input('post')->get();
            if(!empty($typeConfig['hideAttitude'])) $data->attitude = (string)key($typeConfig['attitudes']);
            if(empty($data->attitude) || !isset($typeConfig['attitudes'][$data->attitude]))
            {
                return $this->send(array('result' => 'fail', 'message' => array('attitude' => sprintf($this->lang->error->notempty, $this->lang->jxcollab->attitude))));
            }
            if($this->jxcollab->isContentRequired($collab->type, (string)$data->attitude) && trim((string)$data->content) === '')
            {
                $label = zget($this->lang->jxcollab->contentLabel, $collab->type, $this->lang->jxcollab->content);
                return $this->send(array('result' => 'fail', 'message' => array('content' => sprintf($this->lang->error->notempty, $label))));
            }

            if(!$this->jxcollab->saveFeedback($collab, $account, $data)) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => true, 'load' => true));
        }

        $this->view->title      = $this->lang->jxcollab->feedback;
        $this->view->collab     = $collab;
        $this->view->member     = $mine;
        $this->view->typeConfig = $typeConfig;
        $this->display();
    }

    /**
     * 取消协同单。
     *
     * @param  int    $collabID
     * @access public
     * @return void
     */
    public function cancel(int $collabID)
    {
        $collab = $this->jxcollab->getByID($collabID);
        if(empty($collab)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->notFound));
        if(!$this->jxcollab->canManage($collab)) return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->cannotManage));
        if($collab->status != 'doing') return $this->send(array('result' => 'fail', 'message' => $this->lang->jxcollab->closedAlready));

        if(!$this->jxcollab->cancel($collab)) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => true));
    }
}
