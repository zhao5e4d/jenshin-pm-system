<?php
namespace zin;

global $lang;

onBeforeBuildNode(function($node) use ($lang)
{
    if(!($node instanceof section)) return;
    if($node->prop('title') !== $lang->task->story) return;
    $node->remove();
});
