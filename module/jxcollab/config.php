<?php
declare(strict_types=1);
/**
 * 任务协同：一个任务下可以并行开多张协同单，每张单指定若干协同人反馈。
 * 协同类型决定表态选项、内容是否必填以及额外字段，新增类型只要往这里加一条。
 *
 * attitudes 里每项：
 *   polarity  positive 正面 / negative 负面 / neutral 中性（如「处理中」）
 *   settled   该表态是否算「已给出最终反馈」，全员 settled 后协同单自动关闭
 * contentRequired  true 一律必填；数组则表示选中这些表态时必填
 * fields           额外字段，label 取 $lang->jxcollab->fieldList
 */
$config->jxcollab = new stdclass();

$config->jxcollab->typeList = array('verify', 'review', 'accept', 'consult', 'coordinate');

$config->jxcollab->types = array();

$config->jxcollab->types['verify'] = array(
    'attitudes' => array(
        'pass' => array('polarity' => 'positive', 'settled' => true),
        'fail' => array('polarity' => 'negative', 'settled' => true)
    ),
    'contentRequired' => array('fail'),
    'fields'          => array()
);

$config->jxcollab->types['review'] = array(
    'attitudes' => array(
        'agree'       => array('polarity' => 'positive', 'settled' => true),
        'conditional' => array('polarity' => 'positive', 'settled' => true),
        'disagree'    => array('polarity' => 'negative', 'settled' => true)
    ),
    'contentRequired' => true,
    'fields'          => array(
        'suggestion' => array('control' => 'textarea', 'required' => false)
    )
);

$config->jxcollab->types['accept'] = array(
    'attitudes' => array(
        'qualified'   => array('polarity' => 'positive', 'settled' => true),
        'unqualified' => array('polarity' => 'negative', 'settled' => true)
    ),
    'contentRequired' => array('unqualified'),
    'fields'          => array(
        'issues' => array('control' => 'textarea', 'required' => false)
    )
);

$config->jxcollab->types['consult'] = array(
    'attitudes' => array(
        'replied' => array('polarity' => 'positive', 'settled' => true)
    ),
    'hideAttitude'    => true,
    'contentRequired' => true,
    'fields'          => array()
);

$config->jxcollab->types['coordinate'] = array(
    'attitudes' => array(
        'handled'  => array('polarity' => 'positive', 'settled' => true),
        'handling' => array('polarity' => 'neutral',  'settled' => false),
        'unable'   => array('polarity' => 'negative', 'settled' => true)
    ),
    'contentRequired' => true,
    'fields'          => array(
        'expectDate' => array('control' => 'date', 'required' => false)
    )
);

$config->jxcollab->form = new stdclass();
$config->jxcollab->form->create = array(
    'type'     => array('required' => true,  'type' => 'string', 'default' => 'verify'),
    'title'    => array('required' => true,  'type' => 'string', 'filter' => 'trim'),
    'desc'     => array('required' => false, 'type' => 'string', 'default' => ''),
    'deadline' => array('required' => false, 'type' => 'date',   'default' => null),
    'members'  => array('required' => false, 'type' => 'array',  'default' => array())
);
$config->jxcollab->form->edit = $config->jxcollab->form->create;
$config->jxcollab->form->edit['deleteFiles'] = array('type' => 'array', 'required' => false, 'default' => array());
$config->jxcollab->form->edit['renameFiles'] = array('type' => 'array', 'required' => false, 'default' => array());
