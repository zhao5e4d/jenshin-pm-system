<?php
helper::importControl('execution');
class myExecution extends execution
{
    /**
     * 任务批量编辑等会带 undefined executionID；对象不存在时返回空列表，避免读 type 报错。
     */
    public function ajaxGetMembers(int $objectID, string $assignedTo = '')
    {
        $object = $objectID > 0 ? $this->execution->fetchByID($objectID) : false;
        if(empty($object) || empty($object->type)) return print(json_encode(array()));

        $users = $this->loadModel('user')->getTeamMemberPairs($objectID, $object->type == 'project' ? 'project' : 'execution');
        if($this->app->getViewType() === 'json') return print(json_encode($users));

        $items = array();
        foreach($users as $account => $realName) $items[] = array('value' => $account, 'text' => $realName, 'keys' => $account);
        return print(json_encode($items));
    }
}
