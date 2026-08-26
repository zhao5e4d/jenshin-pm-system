<?php
declare(strict_types=1);
/**
 * The browsebycard view file of project module of ZenTaoPMS.
 * @copyright   Copyright 2009-2023 禅道软件（青岛）集团有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Shujie Tian<tianshujie@easycorp.ltd>
 * @package     project
 * @link        https://www.zentao.net
 */

namespace zin;

$projectCards = null;
if(!empty($projectStats))
{
    foreach($projectStats as $project)
    {
        $status        = isset($project->delay) ? 'delay' : $project->status;
        $statusLabel   = $config->project->statusLabelList[$status];
        $project->end  = $project->end == LONG_TIME ? $this->lang->project->longTime : $project->end;
        $project->date = str_replace('-', '.', $project->begin) . ' - ' . str_replace('-', '.', $project->end);

        $count         = 0;
        $memberAvatars = null;
        $lastMember    = end($project->teamMembers);
        if(!empty($project->teamMembers))
        {
            foreach($project->teamMembers as $key => $member)
            {
                if(!isset($users[$member]))
                {
                    $project->teamCount --;
                    unset($project->teamMembers[$key]);
                    continue;
                }
                if($count > 2) continue;

                $memberAvatars[] = div
                (
                    setClass('avatar circle size-sm'),
                    set::title($users[$member]),
                    avatar
                    (
                        set::size('sm'),
                        set::text($users[$member]),
                        set::src(zget($usersAvatar, $member, ''))
                    )
                );
                $count ++;
            }
        }

        $actionItems  = array();
        $actionParams = "projectID={$project->id}";
        $actionList   = array('edit', 'start', 'suspend', 'close', 'activate');
        foreach($actionList as $action)
        {
            if(!common::hasPriv('project', $action)) continue;
            $actionItem = $config->project->actionList[$action];
            if($this->project->isClickable($project, $action))
            {
                $actionItem['url']       = createLink('project', $action, $actionParams);
                $actionItem['className'] = 'text-primary';
            }
            else
            {
                unset($actionItem['url']);
                $actionItem['disabled'] = true;

            }

            $actionItems[] = $actionItem;
        }

        $isWaterfall = in_array($project->model, array('waterfall', 'waterfallplus'));

        $projectCards[] = div
        (
            setClass('col'),
            set('data-id', $project->id),
            div
            (
                setClass('panel project-card'),
                setClass("project-card-{$status}"),
                div
                (
                    setClass('panel-heading'),
                    span
                    (
                        setClass('label project-type-label'),
                        setClass($isWaterfall ? 'warning-pale ring-warning' : 'secondary-pale ring-secondary'),
                        icon($project->model == 'scrum' ? 'sprint' : $project->model)
                    ),
                    a
                    (
                        setClass('project-name'),
                        set::href(createLink('project', 'index', "projectID={$project->id}")),
                        set::title($project->name),
                        h::strong($project->name)
                    ),
                    span
                    (
                        setClass("project-status label rounded-full {$statusLabel}"),
                        $status != 'delay' ? $lang->project->statusList[$status] : sprintf($lang->project->delayInfo, $project->delay)
                    )
                ),
                div
                (
                    setClass('panel-body'),
                    div
                    (
                        setClass('project-infos'),
                        !empty($project->budget) ? span
                        (
                            set::title($project->budget),
                            setClass('info-item project-budget'),
                            $project->budget
                        ) : null,
                        span
                        (
                            set::title($project->date),
                            setClass('info-item project-date'),
                            $status == 'delay' ? setClass('is-delay') : null,
                            icon('calendar'),
                            span($project->date)
                        )
                    ),
                    div
                    (
                        setClass('project-detail'),
                        div
                        (
                            setClass('stat-item'),
                            div
                            (
                                setClass('stat-value'),
                                div
                                (
                                    set('data-zui', 'ProgressCircle'),
                                    set('data-percent', $project->progress),
                                    set('data-size', 40),
                                    set('data-circle-width', 4),
                                    set('data-circle-color', 'var(--color-success-500)')
                                )
                            ),
                            span
                            (
                                setClass('statistics-title'),
                                $lang->projectCommon . $lang->project->progress
                            )
                        ),
                        div
                        (
                            setClass('stat-item'),
                            div
                            (
                                setClass('stat-value'),
                                span
                                (
                                    setClass('leftTasks'),
                                    set::title((string)$project->leftTasks),
                                    $project->leftTasks
                                )
                            ),
                            span
                            (
                                setClass('statistics-title'),
                                $lang->project->leftTasks
                            )
                        ),
                        div
                        (
                            setClass('stat-item'),
                            div
                            (
                                setClass('stat-value'),
                                span
                                (
                                    setClass('totalLeft'),
                                    set::title(empty($project->left) ? '—' : $project->left . 'h'),
                                    empty($project->left) ? '—' : $project->left . 'h'
                                )
                            ),
                            span
                            (
                                setClass('statistics-title'),
                                $lang->project->leftHours
                            )
                        )
                    ),
                    div
                    (
                        setClass('project-footer'),
                        div
                        (
                            setClass('project-team'),
                            div
                            (
                                setClass('project-members avatar-group'),
                                $memberAvatars,
                                $project->teamCount > 4 ? span
                                (
                                    setClass('members-ellipsis'),
                                    '…'
                                ) : null,
                                $project->teamCount > 3 ? div
                                (
                                    setClass('avatar size-sm circle'),
                                    set::title($users[$lastMember]),
                                    avatar
                                    (
                                        set::size('sm'),
                                        set::text($users[$lastMember]),
                                        set::src(zget($usersAvatar, $lastMember, ''))
                                    )
                                ) : null
                            ),
                            a
                            (
                                setClass('project-members-total'),
                                set::href(createLink('project', 'team', "projectID={$project->id}")),
                                sprintf($lang->project->teamSumCount, $project->teamCount)
                            )
                        ),
                        div
                        (
                            setClass('project-actions'),
                            $actionItems ? dropdown
                            (
                                set::caret(false),
                                btn
                                (
                                    setClass('ghost btn square btn-default'),
                                    set::icon('ellipsis-v')
                                ),
                                set::placement('left-end'),
                                set::menu(array('class' => 'flex p-2 project-menu-actions')),
                                set::items($actionItems)
                            ) : null
                        )
                    )
                )
            )
        );
    }
}

div
(
    setID('cards'),
    setClass('row cell'),
    empty($projectStats) ? div
    (
        setClass('table-empty-tip w-full'),
        span
        (
            setClass('text-gray'),
            $lang->project->empty
        ),
        hasPriv('project', 'create') ? btn(set(array_merge(array
        (
            'icon'          => 'plus',
            'text'          => $lang->project->create,
            'data-toggle'   => 'modal',
            'data-position' => 'center'
        ), array
        (
            'class' => 'ml-2',
            'url'   => createLink('project', 'createGuide')
        )))) : null,
    ) : $projectCards,
    !empty($projectStats) ? div
    (
        setID('cardsFooter'),
        pager(set(usePager()))
    ) : null
);

/* ====== Render page ====== */
render();
