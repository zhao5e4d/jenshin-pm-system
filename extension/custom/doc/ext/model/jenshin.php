<?php
/**
 * Do not bounce 文档 home back to 接口空间 after it was last opened.
 */
public function getLastViewed(string $type): string|null
{
    $value = parent::getLastViewed($type);
    if($type === 'lastViewedSpaceHome' && $value === 'api') return 'mine';
    return $value;
}
