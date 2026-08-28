<?php
declare(strict_types=1);
namespace zin;

div
(
    setClass('flex items-center justify-center min-h-96 p-8'),
    div
    (
        setClass('text-center max-w-lg'),
        h2(setClass('mb-3'), $lang->jxsso->failTitle),
        p(setClass('text-gray-500'), $message)
    )
);
