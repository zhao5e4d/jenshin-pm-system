<?php
declare(strict_types=1);
class jxmarketaccessModel extends model
{
    public function getByID(int $id): object|bool
    {
        return $this->dao->select('*')->from(TABLE_JX_MARKETACCESS)->where('id')->eq($id)->andWhere('deleted')->eq(0)->fetch();
    }

    public function getList(string $browseType, string $orderBy, $pager): array
    {
        return $this->dao->select('*')->from(TABLE_JX_MARKETACCESS)
            ->where('deleted')->eq(0)
            ->beginIF($browseType != 'all' && $browseType != '')->andWhere('type')->eq($browseType)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();
    }
}
