<?php
helper::importControl('misc');
class myMisc extends misc
{
    /**
     * 用户点击「立即体验」后，把欢迎弹窗标为已读，之后不再弹出。
     */
    public function ajaxDismissFeatureNotice()
    {
        $ok = $this->misc->dismissJenshinFeatureNotice();
        echo json_encode(array('result' => $ok ? 'success' : 'fail'));
    }
}
