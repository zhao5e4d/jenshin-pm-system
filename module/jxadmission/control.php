<?php
declare(strict_types=1);
class jxadmission extends control
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
        $rows  = $this->jxadmission->getList($browseType, $orderBy, $pager);
        $hospitals = $this->dao->select('id,name')->from(TABLE_JX_HOSPITAL)->where('deleted')->eq(0)->fetchPairs();
        foreach($rows as $row) $row->hospitalName = $hospitals[$row->hospital] ?? '';
        $this->view->title      = $this->lang->jxadmission->browse;
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
            $matter = form::data($this->config->jxadmission->form->create)->get();
            $matter->openedBy   = $this->app->user->account;
            $matter->openedDate = helper::now();
            $matter->status     = 'wait';
            if(is_array($matter->supportDepts)) $matter->supportDepts = implode(',', $matter->supportDepts);
            $this->dao->insert(TABLE_JX_ADMISSION)->data($matter)->exec();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $id = (int)$this->dao->lastInsertID();
            $matter->id = $id;
            $projectID  = $this->jxcore->createLinkedProject($matter, 'admission', $id, 'admission');
            $this->dao->update(TABLE_JX_ADMISSION)->set('project')->eq($projectID)->where('id')->eq($id)->exec();
            $this->loadModel('action')->create('jxadmission', $id, 'opened');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => $this->createLink('jxadmission', 'view', "id={$id}")));
        }
        $this->view->title     = $this->lang->jxadmission->create;
        $this->view->products  = array('' => '') + $this->jxcore->getProductPairs();
        $this->view->users     = $this->jxcore->getUserPairs();
        $this->view->depts     = $this->jxcore->getDeptNamePairs();
        $this->view->hospitals = array('' => '') + $this->dao->select('id,name')->from(TABLE_JX_HOSPITAL)->where('deleted')->eq(0)->fetchPairs();
        $this->display();
    }

    public function edit(int $id)
    {
        $row = $this->jxadmission->getByID($id);
        if(!$row) return helper::end($this->lang->notFound);
        if($_POST)
        {
            $matter = form::data($this->config->jxadmission->form->edit)->get();
            if(is_array($matter->supportDepts)) $matter->supportDepts = implode(',', $matter->supportDepts);
            $this->dao->update(TABLE_JX_ADMISSION)->data($matter)->where('id')->eq($id)->exec();
            if($row->project) $this->dao->update(TABLE_PROJECT)->set('name')->eq($matter->name)->set('code')->eq($matter->code)->set('begin')->eq($matter->begin)->set('end')->eq($matter->end)->set('budget')->eq($matter->budget)->set('PM')->eq($matter->owner)->where('id')->eq($row->project)->exec();
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => $this->createLink('jxadmission', 'view', "id={$id}")));
        }
        $this->view->title     = $this->lang->jxadmission->edit;
        $this->view->row       = $row;
        $this->view->products  = $this->jxcore->getProductPairs();
        $this->view->users     = $this->jxcore->getUserPairs();
        $this->view->depts     = $this->jxcore->getDeptNamePairs();
        $this->view->hospitals = $this->dao->select('id,name')->from(TABLE_JX_HOSPITAL)->where('deleted')->eq(0)->fetchPairs();
        $this->display();
    }

    public function view(int $id)
    {
        $row = $this->jxadmission->getByID($id);
        if(!$row) return helper::end($this->lang->notFound);
        $this->jxcore->syncMatterStatus(TABLE_JX_ADMISSION, $id, (int)$row->project);
        $row = $this->jxadmission->getByID($id);
        $this->view->title    = $row->name;
        $this->view->row      = $row;
        $this->view->product  = $row->product ? $this->jxcore->getProductArchive((int)$row->product) : null;
        $this->view->hospital = $row->hospital ? $this->dao->select('*')->from(TABLE_JX_HOSPITAL)->where('id')->eq($row->hospital)->fetch() : null;
        $this->view->extra    = $row->project ? $this->jxcore->getProjectExtra((int)$row->project) : null;
        $this->view->stages   = $row->project ? $this->jxcore->getStages((int)$row->project) : array();
        $this->view->costs    = $row->project ? $this->jxcore->getCosts((int)$row->project) : array();
        $this->view->summary  = $row->project ? $this->jxcore->costSummary((int)$row->project) : null;
        $this->display();
    }

    public function hospital()
    {
        if($_POST)
        {
            $hospital = new stdclass();
            $hospital->name       = trim((string)$this->post->name);
            $hospital->level      = (string)$this->post->level;
            $hospital->province   = (string)$this->post->province;
            $hospital->city       = (string)$this->post->city;
            $hospital->department = (string)$this->post->department;
            $this->dao->insert(TABLE_JX_HOSPITAL)->data($hospital)->exec();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => true, 'load' => true));
        }
        $this->view->title     = $this->lang->jxadmission->hospital;
        $this->view->hospitals = $this->dao->select('*')->from(TABLE_JX_HOSPITAL)->where('deleted')->eq(0)->fetchAll();
        $this->display();
    }
}
