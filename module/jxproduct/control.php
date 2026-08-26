<?php
declare(strict_types=1);
class jxproduct extends control
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('jxcore')->ensureSchema();
        $this->loadModel('product');
    }

    public function browse(string $browseType = 'all', string $orderBy = 'id_desc', int $recTotal = 0, int $recPerPage = 20, int $pageID = 1)
    {
        $this->app->loadClass('pager', true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $products = $this->dao->select('t1.*, t2.model, t2.category, t2.line, t2.certNo, t2.certValidTo, t2.specs, t2.udi, t2.manufacturer, t2.patents, t2.tenderCode')
            ->from(TABLE_PRODUCT)->alias('t1')
            ->leftJoin(TABLE_JX_PRODUCT)->alias('t2')->on('t1.id = t2.product')
            ->where('t1.deleted')->eq(0)
            ->beginIF($browseType == 'expiring')->andWhere('t2.certValidTo')->ne('')->andWhere('t2.certValidTo')->le(date('Y-m-d', strtotime('+90 days')))->fi()
            ->orderBy('t1.' . $orderBy)
            ->page($pager)
            ->fetchAll();

        foreach($products as $product)
        {
            $product->certWarn = (!empty($product->certValidTo) && $product->certValidTo <= date('Y-m-d', strtotime('+90 days')));
        }

        $this->view->title      = $this->lang->jxproduct->browse;
        $this->view->products   = $products;
        $this->view->browseType = $browseType;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->display();
    }

    public function create()
    {
        if($_POST)
        {
            $productName = trim((string)$this->post->name);
            if($productName === '')
            {
                dao::$errors['name'] = sprintf($this->lang->error->notempty, $this->lang->jxproduct->name);
                return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            }
            $product = new stdclass();
            $product->name   = $productName;
            $product->code   = (string)$this->post->model;
            $product->status = 'normal';
            $product->type   = 'normal';
            $product->acl    = 'open';
            $product->PO     = $this->app->user->account;
            $product->desc   = (string)$this->post->desc;
            $this->dao->insert(TABLE_PRODUCT)->data($product)->exec();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $productID = (int)$this->dao->lastInsertID();

            $archive = form::data($this->config->jxproduct->form->create)->get();
            $this->jxcore->saveProductArchive($productID, $archive);
            $this->loadModel('action')->create('product', $productID, 'opened');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => $this->createLink('jxproduct', 'view', "id={$productID}")));
        }

        $this->view->title = $this->lang->jxproduct->create;
        $this->display();
    }

    public function edit(int $id)
    {
        $product = $this->jxcore->getProductArchive($id);
        if(!$product) return $this->send(array('result' => 'fail', 'message' => $this->lang->notFound));

        if($_POST)
        {
            $this->dao->update(TABLE_PRODUCT)
                ->set('name')->eq(trim((string)$this->post->name))
                ->set('code')->eq((string)$this->post->model)
                ->set('desc')->eq((string)$this->post->desc)
                ->where('id')->eq($id)->exec();
            $archive = form::data($this->config->jxproduct->form->edit)->get();
            $this->jxcore->saveProductArchive($id, $archive);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'load' => $this->createLink('jxproduct', 'view', "id={$id}")));
        }

        $this->view->title   = $this->lang->jxproduct->edit;
        $this->view->product = $product;
        $this->display();
    }

    public function view(int $id)
    {
        $product = $this->jxcore->getProductArchive($id);
        if(!$product) return helper::end($this->lang->notFound);

        $regs   = $this->dao->select('*')->from(TABLE_JX_REGISTRATION)->where('product')->eq($id)->andWhere('deleted')->eq(0)->fetchAll();
        $access = $this->dao->select('*')->from(TABLE_JX_MARKETACCESS)->where('product')->eq($id)->andWhere('deleted')->eq(0)->fetchAll();
        $admits = $this->dao->select('*')->from(TABLE_JX_ADMISSION)->where('product')->eq($id)->andWhere('deleted')->eq(0)->fetchAll();

        $this->view->title   = $product->name;
        $this->view->product = $product;
        $this->view->regs    = $regs;
        $this->view->access  = $access;
        $this->view->admits  = $admits;
        $this->display();
    }
}
