<?php
declare(strict_types=1);
if(!isset($lang->jxcollab)) $lang->jxcollab = new stdclass();

$lang->jxcollab->common   = '任务协同';
$lang->jxcollab->collab   = '协同单';
$lang->jxcollab->create   = '发起协同';
$lang->jxcollab->edit     = '编辑协同单';
$lang->jxcollab->view     = '协同单详情';
$lang->jxcollab->feedback     = '填写反馈';
$lang->jxcollab->saveFeedback = '保存反馈';
$lang->jxcollab->cancel   = '取消协同';
$lang->jxcollab->members  = '协同人';
$lang->jxcollab->member   = '协同人';

$lang->jxcollab->id           = '编号';
$lang->jxcollab->task         = '所属任务';
$lang->jxcollab->project      = '所属项目';
$lang->jxcollab->type         = '协同类型';
$lang->jxcollab->title        = '协同事项';
$lang->jxcollab->desc         = '协同说明';
$lang->jxcollab->deadline     = '截止日期';
$lang->jxcollab->status       = '状态';
$lang->jxcollab->result       = '协同结论';
$lang->jxcollab->openedBy     = '发起人';
$lang->jxcollab->openedDate   = '发起时间';
$lang->jxcollab->closedBy     = '关闭人';
$lang->jxcollab->closedDate   = '关闭时间';
$lang->jxcollab->attitude     = '表态';
$lang->jxcollab->content      = '反馈内容';
$lang->jxcollab->feedbackDate = '反馈时间';
$lang->jxcollab->progress     = '反馈进度';

$lang->jxcollab->typeList = array(
    'verify'     => '核验确认',
    'review'     => '评审',
    'accept'     => '验收',
    'consult'    => '意见征询',
    'coordinate' => '问题协调'
);

$lang->jxcollab->typeDesc = array(
    'verify'     => '请协同人核对结果，表态通过或不通过。',
    'review'     => '请协同人给出评审意见，可附修改建议。',
    'accept'     => '请协同人判定是否合格，不合格时列出问题清单。',
    'consult'    => '只收集意见，不需要表态。',
    'coordinate' => '跟踪问题处理进展，处理中可以多次更新。'
);

$lang->jxcollab->attitudeList = array(
    'pass'        => '通过',
    'fail'        => '不通过',
    'agree'       => '同意',
    'conditional' => '有条件同意',
    'disagree'    => '不同意',
    'qualified'   => '合格',
    'unqualified' => '不合格',
    'replied'     => '已反馈',
    'handled'     => '已处理',
    'handling'    => '处理中',
    'unable'      => '无法处理'
);

$lang->jxcollab->contentLabel = array(
    'verify'     => '核验说明',
    'review'     => '评审意见',
    'accept'     => '验收说明',
    'consult'    => '意见',
    'coordinate' => '处理说明'
);

$lang->jxcollab->fieldList = array(
    'suggestion' => '修改建议',
    'issues'     => '问题清单',
    'expectDate' => '预计完成日期'
);

$lang->jxcollab->statusList = array(
    'doing'    => '进行中',
    'closed'   => '已关闭',
    'canceled' => '已取消'
);

$lang->jxcollab->resultList = array(
    ''     => '待反馈',
    'pass' => '通过',
    'fail' => '未通过',
    'done' => '已完成'
);

$lang->jxcollab->waiting = '待反馈';

$lang->jxcollab->noCollab      = '该任务还没有协同单。';
$lang->jxcollab->noMember      = '请至少选择一位协同人。';
$lang->jxcollab->notFound      = '协同单不存在或已删除。';
$lang->jxcollab->denied        = '你不是该协同单的协同人，不能提交反馈。';
$lang->jxcollab->cannotManage  = '只有发起人、任务指派人或项目负责人可以维护该协同单。';
$lang->jxcollab->closedAlready = '协同单已经关闭或取消。';
$lang->jxcollab->confirmCancel = '确定取消该协同单吗？';
$lang->jxcollab->autoClosed    = '协同人已全部反馈，协同单自动关闭。';
$lang->jxcollab->taskClosing   = '该任务下还有进行中或未通过的协同单：';
$lang->jxcollab->taskClosingTip = '可以继续关闭任务，协同单不受影响。';
$lang->jxcollab->noticeText    = '%s 在任务【%s】发起了%s，请填写反馈：';
$lang->jxcollab->openCollab    = '打开协同单';
$lang->jxcollab->myPending     = '待我反馈';
$lang->jxcollab->newItem       = '新事项';
$lang->jxcollab->readDenied    = '只能标记本人的协同事项已读。';
$lang->jxcollab->readFailed    = '保存阅读状态失败，请重新打开事项。';
$lang->jxcollab->feedbackTip   = '同一张协同单在关闭前可以反复修改自己的反馈。';
$lang->jxcollab->listSummary   = '%s / %s 已反馈';
