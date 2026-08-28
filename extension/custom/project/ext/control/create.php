<?php
helper::importControl('project');
class myProject extends project
{
    /**
     * 创建项目时锁定为默认 Scrum，忽略 URL / 表单里的其它模型。
     */
    public function create(string $model = 'scrum', int $programID = 0, int $copyProjectID = 0, string $extra = '')
    {
        if(!empty($this->config->jenshin->lockProjectModel) || !empty($this->config->jenshin->skipCreateGuide))
        {
            $model = !empty($this->config->jenshin->defaultProjectModel) ? (string)$this->config->jenshin->defaultProjectModel : 'scrum';
            if(isset($_POST['model'])) $_POST['model'] = $model;
        }

        parent::create($model, $programID, $copyProjectID, $extra);
    }
}
