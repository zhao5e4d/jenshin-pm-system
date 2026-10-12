<?php
/**
 * Action labels for task collaboration and execution rename.
 */
global $app;

$lang->action->objectTypes['execution'] = $lang->executionCommon;
$lang->action->label->execution         = "{$lang->executionCommon}|execution|task|executionID=%s";

$lang->action->objectTypes['jxcollab'] = 'Task Collaboration';
$lang->action->label->jxcollab         = 'Task Collaboration|jxcollab|view|collabID=%s';

$lang->action->label->jxcollabopened   = 'Started collaboration';
$lang->action->label->jxcollabfeedback = 'Submitted collaboration feedback';
$lang->action->label->jxcollabclosed   = 'Collaboration auto-closed';
$lang->action->label->jxcollabcanceled = 'Canceled collaboration';

$lang->action->desc->jxcollabopened   = '$date, <strong>$actor</strong> started collaboration <strong>$extra</strong>.' . "\n";
$lang->action->desc->jxcollabfeedback = '$date, <strong>$actor</strong> submitted collaboration feedback: <strong>$extra</strong>.' . "\n";
$lang->action->desc->jxcollabclosed   = '$date, collaborators finished <strong>$extra</strong> and it was closed automatically.' . "\n";
$lang->action->desc->jxcollabcanceled = '$date, <strong>$actor</strong> canceled collaboration <strong>$extra</strong>.' . "\n";
