<?php
declare(strict_types=1);
class jxcore extends control
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('jxcore');
        $this->jxcore->ensureSchema();
    }

    public function index()
    {
        $this->locate($this->createLink('jxdashboard', 'index'));
    }

    public function togglecheck(int $checkID = 0, int $done = 1)
    {
        if(!$checkID && $_POST) $checkID = (int)$this->post->checkID;
        if(isset($_POST['done'])) $done = (int)$this->post->done;
        $this->jxcore->toggleCheck($checkID, $done);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => true));
    }

    public function submitstage(int $stageID = 0)
    {
        if(!$stageID && $_POST) $stageID = (int)$this->post->stageID;
        $comment = (string)$this->post->comment;
        $ok = $this->jxcore->submitStage($stageID, $comment);
        if(!$ok || dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError() ?: $this->lang->fail));
        return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => true));
    }

    public function approvestage(int $stageID = 0)
    {
        if(!$stageID && $_POST) $stageID = (int)$this->post->stageID;
        $result  = (string)$this->post->result ?: 'pass';
        $comment = (string)$this->post->comment;
        $ok = $this->jxcore->approveStage($stageID, $result, $comment);
        if(!$ok || dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError() ?: $this->lang->fail));
        return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => true));
    }

    public function addcost(int $projectID = 0)
    {
        if($_POST)
        {
            $cost = new stdclass();
            $cost->project   = $projectID ?: (int)$this->post->project;
            $cost->dept      = (string)$this->post->dept;
            $cost->category  = (string)$this->post->category;
            $cost->amount    = (float)$this->post->amount;
            $cost->occurDate = (string)$this->post->occurDate ?: date('Y-m-d');
            $cost->desc      = (string)$this->post->desc;
            $this->jxcore->addCost($cost);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => true, 'load' => true));
        }

        $this->view->title     = $this->lang->jxcore->addcost;
        $this->view->projectID = $projectID;
        $this->view->depts     = $this->jxcore->getDeptNamePairs();
        $this->display();
    }
}
