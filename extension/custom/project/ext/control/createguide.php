<?php
helper::importControl('project');
class myProject extends project
{
    /**
     * 跳过项目管理方式弹窗，直接进入默认 Scrum 创建页。
     */
    public function createGuide(int $programID = 0, string $from = 'project', int $productID = 0, int $branchID = 0)
    {
        if(empty($this->config->jenshin->skipCreateGuide))
        {
            return parent::createGuide($programID, $from, $productID, $branchID);
        }

        $model = !empty($this->config->jenshin->defaultProjectModel) ? $this->config->jenshin->defaultProjectModel : 'scrum';
        $extra = "productID={$productID},branchID={$branchID}";
        $link  = $this->createLink('project', 'create', "model={$model}&programID={$programID}&copyProjectID=0&extra={$extra}");

        if(isInModal())
        {
            $url = json_encode($link, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            helper::end('<!DOCTYPE html><html><body><script>(function(){var u=' . $url . ';var w=(window.parent&&window.parent!==window)?window.parent:window;try{if(w.zui&&w.zui.Modal)w.zui.Modal.hide();}catch(e){}if(typeof w.loadPage==="function"){w.loadPage(u);}else{w.location.href=u;}})();</script></body></html>');
        }

        $this->locate($link);
    }
}
