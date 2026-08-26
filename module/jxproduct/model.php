<?php
declare(strict_types=1);
class jxproductModel extends model
{
    public function getByID(int $id): object|bool
    {
        return $this->loadModel('jxcore')->getProductArchive($id) ?: false;
    }
}
