<?php
declare(strict_types=1);
/**
 * 设置迭代：沿用禅道原表单，去掉产品/测试/迭代/发布负责人。
 */
namespace zin;
$fields = useFields('execution.edit');
$fields->remove('PO,QD,PM,RD');

jsVar('confirmSync', $lang->execution->confirmSync);
jsVar('isWaterfall', isset($project) && ($project->model == 'waterfall' || $project->model == 'waterfallplus'));
jsVar('executionAttr', $execution->attribute);
jsVar('window.lastProjectID', $execution->project);
jsVar('weekend', $config->execution->weekend);

$confirmTip   = !empty($unclosedTasks) ? sprintf($this->lang->execution->confirmCloseExecution, implode($this->lang->comma, array_keys($unclosedTasks))) : '';
$beforeSubmit = jsRaw("() =>
{
    if($('[name=status]').val() != 'closed') return true;
    zui.Modal.confirm('{$confirmTip}').then((res) =>
    {
        if(res)
        {
            const formData = new FormData($('#zin_execution_edit_{$execution->id}_formGridPanel form')[0]);
            const confirmURL = $('#zin_execution_edit_{$execution->id}_formGridPanel form').attr('action');
            $.ajaxSubmit({url: confirmURL, data: formData});
        }
    });
    return false;
}");

formGridPanel
(
    on::change('[name=begin]', 'computeWorkDays(NaN)'),
    on::change('[name=end]', 'computeWorkDays(NaN)'),
    on::change('[name=project]', 'changeProject()'),
    set::formID('zin_execution_edit_' . $execution->id . '_formGridPanel'),
    !empty($unclosedTasks) ? set::ajax(array('beforeSubmit' => $beforeSubmit)) : null,
    set::fullModeOrders('project', !empty($config->setCode) ? 'lifetime,attribute,name,code,status' : 'lifetime,attribute,name,status', 'planDate,days,productsBox,teamMembers,desc,acl'),
    set::title($lang->execution->edit),
    set::modeSwitcher(false),
    set::defaultMode('full'),
    set::fields($fields)
);
