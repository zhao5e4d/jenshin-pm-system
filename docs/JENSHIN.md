# 健忻医疗项目管理系统（一期）

基于禅道开源版 22.5，通过 `extension/custom` 与 `module/jx*` 做二次开发，不删除上游模块。

生产环境上云步骤见 [PRODUCTION-DEPLOY.md](PRODUCTION-DEPLOY.md)。

## 架构约束

- 不改禅道内核业务逻辑；品牌、导航、权限裁剪放在 [extension/custom](../extension/custom)。
- 新增表一律 `zt_jx_*`，常量在 [config/ext/jenshin.php](../config/ext/jenshin.php)。
- 阶段门、项目生成、费用台账在 [module/jxcore](../module/jxcore)。
- 业务模块：禅道原产品（叠加医疗档案字段）、`jxregistration`、`jxmarketaccess`、`jxadmission`、`jxdashboard`。`jxproduct` 仅作旧地址兼容跳转。

## 安装与升级

Docker 启动会执行：

1. 禅道自动安装（若尚未安装）
2. `php deploy/jenshin-migrate.php`（幂等建表、权限、功能开关）
3. `php deploy/seed_jenshin.php`（演示数据，已有事项则跳过）

手动执行：

```bash
php deploy/jenshin-migrate.php
php deploy/seed_jenshin.php
```

关闭演示灌数：环境变量 `JENSHIN_SEED=0`。

## 裁剪范围

对普通用户隐藏并拦截：测试/Bug、代码库、构建、发布、DevOps。上游目录保留，升级不受影响。超级管理员仍可走直链以利于排障。

## 一期流程

- 产品：沿用禅道原产品模块（需求、计划、关联项目）；医疗档案字段（型号、分类、注册证、有效期、UDI、规格）叠在产品新建/编辑/详情上
- 产品注册：首次 / 延续 / 变更，阶段门「路径锁定—型检—临床—体系考核—递交—发补—取证归档」
- 市场准入：挂网 / 集采 / 招投标，阶段门「标讯—资质产能—成本报价—申报投标—中选签约—分量配送」
- 推广入院：医院档案 + 入院事项，阶段门「目标筛选—院内准入—推广带教—首单放行—爬坡复购」
- 看板：经营总览、部门经营、项目组合、会议视图

创建事项时会自动生成禅道项目、默认执行、阶段任务、检查项，并挂接产品。
