<?php
declare(strict_types=1);
if(!isset($lang->jxcollab)) $lang->jxcollab = new stdclass();

$lang->jxcollab->common   = 'Task Collaboration';
$lang->jxcollab->collab   = 'Collaboration';
$lang->jxcollab->create   = 'Start Collaboration';
$lang->jxcollab->edit     = 'Edit Collaboration';
$lang->jxcollab->view     = 'Collaboration Detail';
$lang->jxcollab->feedback     = 'Submit Feedback';
$lang->jxcollab->saveFeedback = 'Save Feedback';
$lang->jxcollab->cancel   = 'Cancel Collaboration';
$lang->jxcollab->members  = 'Collaborators';
$lang->jxcollab->member   = 'Collaborator';

$lang->jxcollab->id           = 'ID';
$lang->jxcollab->task         = 'Task';
$lang->jxcollab->project      = 'Project';
$lang->jxcollab->type         = 'Type';
$lang->jxcollab->title        = 'Subject';
$lang->jxcollab->desc         = 'Description';
$lang->jxcollab->deadline     = 'Deadline';
$lang->jxcollab->status       = 'Status';
$lang->jxcollab->result       = 'Result';
$lang->jxcollab->openedBy     = 'Opened By';
$lang->jxcollab->openedDate   = 'Opened Date';
$lang->jxcollab->closedBy     = 'Closed By';
$lang->jxcollab->closedDate   = 'Closed Date';
$lang->jxcollab->attitude     = 'Stance';
$lang->jxcollab->content      = 'Feedback';
$lang->jxcollab->feedbackDate = 'Feedback Date';
$lang->jxcollab->progress     = 'Progress';

$lang->jxcollab->typeList = array(
    'verify'     => 'Verification',
    'review'     => 'Review',
    'accept'     => 'Acceptance',
    'consult'    => 'Consultation',
    'coordinate' => 'Coordination'
);

$lang->jxcollab->typeDesc = array(
    'verify'     => 'Ask collaborators to confirm the result: pass or fail.',
    'review'     => 'Ask collaborators for a review comment. They may add suggestions.',
    'accept'     => 'Ask collaborators to accept or reject, and list issues if rejected.',
    'consult'    => 'Collect comments only. No stance is required.',
    'coordinate' => 'Track issue handling. “In progress” can be updated more than once.'
);

$lang->jxcollab->attitudeList = array(
    'pass'        => 'Pass',
    'fail'        => 'Fail',
    'agree'       => 'Agree',
    'conditional' => 'Agree with conditions',
    'disagree'    => 'Disagree',
    'qualified'   => 'Qualified',
    'unqualified' => 'Unqualified',
    'replied'     => 'Replied',
    'handled'     => 'Handled',
    'handling'    => 'In progress',
    'unable'      => 'Unable to handle'
);

$lang->jxcollab->contentLabel = array(
    'verify'     => 'Verification note',
    'review'     => 'Review comment',
    'accept'     => 'Acceptance note',
    'consult'    => 'Comment',
    'coordinate' => 'Handling note'
);

$lang->jxcollab->fieldList = array(
    'suggestion' => 'Suggestion',
    'issues'     => 'Issue list',
    'expectDate' => 'Expected date'
);

$lang->jxcollab->statusList = array(
    'doing'    => 'Doing',
    'closed'   => 'Closed',
    'canceled' => 'Canceled'
);

$lang->jxcollab->resultList = array(
    ''     => 'Pending',
    'pass' => 'Passed',
    'fail' => 'Failed',
    'done' => 'Done'
);

$lang->jxcollab->waiting = 'Pending';

$lang->jxcollab->noCollab      = 'This task has no collaboration yet.';
$lang->jxcollab->noMember      = 'Select at least one collaborator.';
$lang->jxcollab->notFound      = 'The collaboration does not exist or has been deleted.';
$lang->jxcollab->denied        = 'You are not a collaborator on this item.';
$lang->jxcollab->cannotManage  = 'Only the opener, the task assignee, or the project manager can maintain this item.';
$lang->jxcollab->closedAlready = 'The collaboration is already closed or canceled.';
$lang->jxcollab->confirmCancel = 'Cancel this collaboration?';
$lang->jxcollab->autoClosed    = 'All collaborators have submitted final feedback. The item is closed.';
$lang->jxcollab->taskClosing   = 'This task still has open or failed collaborations:';
$lang->jxcollab->taskClosingTip = 'You can still close the task. Collaborations are not affected.';
$lang->jxcollab->noticeText    = '%s started %3$s for task [%2$s]. Please submit feedback: ';
$lang->jxcollab->openCollab    = 'Open collaboration';
$lang->jxcollab->myPending     = 'Pending Feedback';
$lang->jxcollab->newItem       = 'New item';
$lang->jxcollab->readDenied    = 'You can only mark your own collaboration as read.';
$lang->jxcollab->readFailed    = 'Unable to save read status. Please reopen the item.';
$lang->jxcollab->feedbackTip   = 'You can update your feedback until the collaboration is closed.';
$lang->jxcollab->listSummary   = '%s / %s submitted';
