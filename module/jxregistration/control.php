<?php
declare(strict_types=1);
class jxregistration extends control
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('jxcore')->ensureSchema();
        $this->app->loadLang('jxcore');
        $this->app->loadLang('jxproduct');
    }

    public function browse(string $browseType = 'all', string $orderBy = 'id_desc', int $recTotal = 0, int $recPerPage = 20, int $pageID = 1)
    {
        $this->app->loadClass('pager', true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);
        $rows  = $this->jxregistration->getList($browseType, $orderBy, $pager);
        $this->view->title      = $this->lang->jxregistration->browse;
        $this->view->rows       = $this->jxcore->appendDisplayFields($rows);
        $this->view->browseType = $browseType;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->display();
    }

    public function create()
    {
        if($_POST)
        {
            $matter = form::data($this->config->jxregistration->form->create)->get();
            $matter->openedBy   = $this->app->user->account;
            $matter->openedDate = helper::now();
            $matter->status     = 'wait';
            if(is_array($matter->supportDepts)) $matter->supportDepts = implode(',', $matter->supportDepts);
            $this->dao->insert(TABLE_JX_REGISTRATION)->data($matter)->exec();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $id = (int)$this->dao->lastInsertID();
            $matter->id = $id;
            $projectID  = $this->jxcore->createLinkedProject($matter, 'registration', $id, 'registration');
            $this->dao->update(TABLE_JX_REGISTRATION)->set('project')->eq($projectID)->where('id')->eq($id)->exec();
            $this->loadModel('action')->create('jxregistration', $id, 'opened');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => $this->createLink('jxregistration', 'view', "id={$id}")));
        }
        $this->view->title    = $this->lang->jxregistration->create;
        $this->view->products = array('' => '') + $this->jxcore->getProductPairs();
        $this->view->users    = $this->jxcore->getUserPairs();
        $this->view->depts    = $this->jxcore->getDeptNamePairs();
        $this->display();
    }

    public function edit(int $id)
    {
        $row = $this->jxregistration->getByID($id);
        if(!$row) return helper::end($this->lang->notFound);
        if($_POST)
        {
            $matter = form::data($this->config->jxregistration->form->edit)->get();
            if(is_array($matter->supportDepts)) $matter->supportDepts = implode(',', $matter->supportDepts);
            $this->dao->update(TABLE_JX_REGISTRATION)->data($matter)->where('id')->eq($id)->exec();
            if($row->project)
            {
                $this->dao->update(TABLE_PROJECT)
                    ->set('name')->eq($matter->name)
                    ->set('code')->eq($matter->code)
                    ->set('begin')->eq($matter->begin)
                    ->set('end')->eq($matter->end)
                    ->set('budget')->eq($matter->budget)
                    ->set('PM')->eq($matter->owner)
                    ->where('id')->eq($row->project)->exec();
                $extra = new stdclass();
                $extra->code = $matter->code;
                $extra->leadDept = $matter->leadDept;
                $extra->supportDepts = $matter->supportDepts;
                $this->jxcore->saveProjectExtra((int)$row->project, $extra);
            }
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => $this->createLink('jxregistration', 'view', "id={$id}")));
        }
        $this->view->title    = $this->lang->jxregistration->edit;
        $this->view->row      = $row;
        $this->view->products = $this->jxcore->getProductPairs();
        $this->view->users    = $this->jxcore->getUserPairs();
        $this->view->depts    = $this->jxcore->getDeptNamePairs();
        $this->display();
    }

    public function view(int $id)
    {
        $row = $this->jxregistration->getByID($id);
        if(!$row) return helper::end($this->lang->notFound);
        $this->jxcore->syncMatterStatus(TABLE_JX_REGISTRATION, $id, (int)$row->project);
        $row = $this->jxregistration->getByID($id);
        $this->view->title   = $row->name;
        $this->view->row     = $row;
        $this->view->product = $row->product ? $this->jxcore->getProductArchive((int)$row->product) : null;
        $this->view->extra   = $row->project ? $this->jxcore->getProjectExtra((int)$row->project) : null;
        $this->view->stages  = $row->project ? $this->jxcore->getStages((int)$row->project) : array();
        $this->view->costs   = $row->project ? $this->jxcore->getCosts((int)$row->project) : array();
        $this->view->summary = $row->project ? $this->jxcore->costSummary((int)$row->project) : null;
        $this->display();
    }
}
