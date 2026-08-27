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
        $this->locate($this->createLink('product', 'all'));
    }

    public function create()
    {
        $this->locate($this->createLink('product', 'create'));
    }

    public function edit(int $id)
    {
        $this->locate($this->createLink('product', 'edit', "productID={$id}"));
    }

    public function view(int $id)
    {
        $this->locate($this->createLink('product', 'view', "productID={$id}"));
    }
}
