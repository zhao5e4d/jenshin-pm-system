<?php
namespace zin;

if(function_exists('jxHideBugKanban') && jxHideBugKanban())
{
    query('#batchCreateBug')->remove();
}
