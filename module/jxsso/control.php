<?php
declare(strict_types=1);
/**
 * Boke 平台免登入口。构造函数不依赖已登录用户。
 */
class jxsso extends control
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 校验 Boke 签发的 JWT，按手机号匹配有效用户后写入会话并跳转首页。
     *
     * @access public
     * @return void
     */
    public function login()
    {
        $token = '';
        if(isset($this->get->token) && $this->get->token !== '') $token = (string)$this->get->token;
        elseif(isset($_GET['token'])) $token = (string)$_GET['token'];

        $message = $this->lang->jxsso->failDefault;
        if($token !== '')
        {
            $result = $this->jxsso->loginByToken($token);
            if(!empty($result['user']))
            {
                $this->loadModel('user')->login($result['user']);
                $home = !empty($this->config->jenshin->sso->homeLink) ? $this->config->jenshin->sso->homeLink : $this->createLink('my', 'index');
                return $this->locate($home);
            }
            $message = !empty($result['message']) ? $result['message'] : $this->lang->jxsso->failDefault;
        }

        $this->view->title   = $this->lang->jxsso->failTitle;
        $this->view->message = $message;
        $this->display('jxsso', 'fail');
    }
}
