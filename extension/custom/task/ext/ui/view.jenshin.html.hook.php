<?php
namespace zin;

global $lang, $app;

$app->loadLang('jxcollab');

$jxTask    = data('task');
$jxUsers   = data('users');
if(!is_array($jxUsers)) $jxUsers = array();
$jxControl = $app->control;
$jxCollabs = array();
$jxCanCreate = false;
if(!empty($jxTask) && !empty($jxTask->id))
{
    $jxCollabModel = $jxControl->loadModel('jxcollab');
    $jxCollabs     = $jxCollabModel->getListByTask((int)$jxTask->id);
    $jxCanCreate   = empty($jxTask->deleted)
        && !in_array($jxTask->status, array('closed', 'cancel'))
        && hasPriv('jxcollab', 'create')
        && $jxCollabModel->canCreate($jxTask);
}

/**
 * 一张协同单一张卡片：抬头是类型 / 事项 / 状态 / 结论 / 进度，下面列每位协同人的表态。
 */
$jxBuildCollabCard = function($collab) use ($lang, $jxControl, $jxUsers)
{
    $statusClass = array('doing' => 'warning', 'closed' => 'success', 'canceled' => 'gray');
    $resultClass = array('pass' => 'success', 'done' => 'success', 'fail' => 'danger');
    $typeConfig  = $jxControl->jxcollab->getTypeConfig($collab->type);

    $settled = 0;
    $rows    = array();
    foreach($collab->members as $member)
    {
        if($member->settled) $settled ++;

        $extraText = array();
        foreach($typeConfig['fields'] as $field => $fieldConfig)
        {
            $value = isset($member->extra[$field]) ? trim((string)$member->extra[$field]) : '';
            if($value === '') continue;
            $extraText[] = zget($lang->jxcollab->fieldList, $field, $field) . '：' . $value;
        }

        $rows[] = h::tr
        (
            h::td(setClass('w-32'), zget($jxUsers, $member->account, $member->account)),
            h::td(setClass('w-24'), $member->attitude
                ? span(setClass('label ' . ($member->settled ? 'primary-pale' : 'warning-pale')), zget($lang->jxcollab->attitudeList, $member->attitude, $member->attitude))
                : span(setClass('text-gray'), $lang->jxcollab->waiting)),
            h::td(setClass('break-all'), (string)$member->content . ($extraText ? ' ' . implode('；', $extraText) : '')),
            h::td(setClass('w-32 text-gray'), helper::isZeroDate((string)$member->feedbackDate) ? '' : substr((string)$member->feedbackDate, 0, 16))
        );
    }

    return div
    (
        setClass('border rounded-md p-3 mb-2'),
        div
        (
            setClass('flex items-center gap-2 mb-2'),
            span(setClass('label gray'), zget($lang->jxcollab->typeList, $collab->type, $collab->type)),
            a
            (
                setClass('font-bold'),
                set::href(createLink('jxcollab', 'view', "collabID={$collab->id}")),
                set('data-toggle', 'modal'),
                set('data-size', 'lg'),
                $collab->title
            ),
            span(setClass('label ' . zget($statusClass, $collab->status, '')), zget($lang->jxcollab->statusList, $collab->status, $collab->status)),
            $collab->result ? span(setClass('label ' . zget($resultClass, $collab->result, '')), zget($lang->jxcollab->resultList, $collab->result, $collab->result)) : null,
            div(setClass('flex-auto')),
            span(setClass('text-gray'), sprintf($lang->jxcollab->listSummary, $settled, count($collab->members)))
        ),
        $rows ? h::table(setClass('table bordered'), h::tbody($rows)) : null
    );
};

onBeforeBuildNode(function($node) use ($lang, $jxTask, $jxCollabs, $jxCanCreate, $jxBuildCollabCard)
{
    if($node instanceof history)
    {
        $node->setProp('title', $lang->task->taskRecord);
        $node->setProp('commentBtn', $lang->task->addTaskRecord);
        return;
    }

    if($node instanceof detail)
    {
        $tabs = $node->prop('tabs');
        if(!empty($tabs) && is_array($tabs))
        {
            $filtered = array();
            foreach($tabs as $key => $tab)
            {
                $item = $tab instanceof setting ? $tab->toArray() : (array)$tab;
                if(($item['control'] ?? '') === 'taskMiscInfo') continue;
                if(($item['title'] ?? '') === $lang->task->legendMisc) continue;
                $filtered[$key] = $tab;
            }
            $node->setProp('tabs', array_values($filtered));
        }

        /* 任务协同区块：放在子任务之后、附件之前。 */
        if(empty($jxCollabs) && !$jxCanCreate) return;

        $content = array();
        if($jxCanCreate)
        {
            $content[] = div
            (
                setClass('mb-2'),
                btn
                (
                    setClass('primary size-sm'),
                    set::icon('plus'),
                    set::url(createLink('jxcollab', 'create', "taskID={$jxTask->id}")),
                    set('data-toggle', 'modal'),
                    $lang->jxcollab->create
                )
            );
        }
        if(empty($jxCollabs)) $content[] = div(setClass('text-gray'), $lang->jxcollab->noCollab);
        foreach($jxCollabs as $collab) $content[] = $jxBuildCollabCard($collab);

        $collabSection = setting()->title($lang->jxcollab->common)->children(div($content));

        $sections    = $node->prop('sections');
        if(!is_array($sections)) $sections = array();
        $newSections = array();
        $inserted    = false;
        foreach($sections as $key => $item)
        {
            $itemArray = $item instanceof setting ? $item->toArray() : (is_array($item) ? $item : null);
            if(!$inserted && is_array($itemArray) && ($itemArray['control'] ?? '') === 'fileList')
            {
                $newSections[] = $collabSection;
                $inserted      = true;
            }
            if(is_string($key)) $newSections[$key] = $item;
            else                $newSections[]     = $item;
        }
        if(!$inserted) $newSections[] = $collabSection;

        $node->setProp('sections', $newSections);
        return;
    }

    if(!($node instanceof datalist)) return;
    $items = $node->prop('items');
    if(!is_array($items)) return;

    unset($items[$lang->task->fromBug], $items[$lang->task->module], $items[$lang->task->story]);
    $node->setProp('items', $items);
});
