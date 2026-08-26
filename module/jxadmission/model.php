<?php
declare(strict_types=1);
class jxadmissionModel extends model
{
    public function getByID(int $id): object|bool
    {
        return $this->dao->select('*')->from(TABLE_JX_ADMISSION)->where('id')->eq($id)->andWhere('deleted')->eq(0)->fetch();
    }

    public function getList(string $browseType, string $orderBy, $pager): array
    {
        return $this->dao->select('*')->from(TABLE_JX_ADMISSION)
            ->where('deleted')->eq(0)
            ->beginIF($browseType == 'doing')->andWhere('status')->in('wait,doing,blocked')->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();
    }
}
