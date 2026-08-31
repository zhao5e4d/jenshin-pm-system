# 健忻医疗项目管理系统

<p align="center">
  <img src="www/theme/default/images/main/jx-logo.png" width="160" alt="健忻医疗项目管理系统">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.0-7c5cbf" alt="Version">
  <img src="https://img.shields.io/badge/edition-medical--pm-5b4b8a" alt="Edition">
  <img src="https://img.shields.io/badge/php-8.0--8.2-777bb4" alt="PHP">
  <img src="https://img.shields.io/badge/mysql-8.0%20%7C%20mariadb-4479A1" alt="Database">
</p>

健忻科技内部使用的医疗项目管理平台（一期）。面向器械注册、市场准入、推广入院与日常项目协作，把产品档案、阶段门、任务执行和经营看板放在同一套系统里。

本仓库是完整可部署的站点源码，不是上游官方发行说明。界面品牌为「健忻医疗项目管理系统」。

---

## 一期做什么

| 能力 | 说明 |
| --- | --- |
| 产品组合 | 沿用产品、需求、计划、关联项目；新建 / 编辑 / 详情叠加医疗档案：型号、管理类别（一/二/三类）、注册证号、证照有效期、生产主体、集采编码、规格、知识产权 |
| 项目管理 | 默认 Scrum，跳过「选择管理方式」向导并锁定模型；创建事项时可自动生成项目、默认执行、阶段任务与检查项，并挂接产品 |
| 任务执行 | 执行与任务推进日常协作 |
| 数据看板 | 经营总览、部门经营、项目组合、会议视图；按部门 / 产品 / 状态筛选，汇总健康度、预算、工时、逾期任务 |
| 产品注册 | 首次 / 延续 / 许可事项变更；阶段门「路径锁定—型检—临床—体系考核—递交—发补—取证归档」 |
| 市场准入 | 挂网 / 集采申报 / 招投标；阶段门「标讯—资质产能—成本报价—申报投标—中选签约—分量配送」 |
| 推广入院 | 医院档案 + 入院事项；阶段门「目标筛选—院内准入—推广带教—首单放行—爬坡复购」 |
| 组织与文档 | 工作台、文档空间、组织部门、权限分组 |

产品注册、市场准入、推广入院及旧数据看板的代码保留，一级菜单默认关闭（`$config->jenshin->enableLegacyBizMenus`）。`jxproduct` 仅作旧地址兼容，会跳转到原产品模块。

---

## 默认导航

工作台 · 产品组合 · 项目管理 · 任务执行 · 数据看板 · 文档空间 · 组织部门 · 组织设置

对普通用户隐藏并拦截：测试 / Bug、代码库、构建、发布、DevOps。上游目录保留，升级不受影响。超级管理员仍可走直链排障。

---

## 架构约定

二次开发叠在内核之上，不删上游模块、不改内核业务逻辑。

| 约定 | 位置 |
| --- | --- |
| 品牌、导航、权限裁剪、产品档案字段 | [`extension/custom`](extension/custom) |
| 开关、语言列表、屏蔽模块、SSO、表常量 | [`config/ext/jenshin.php`](config/ext/jenshin.php) |
| 阶段门、项目生成、费用台账、幂等建表 | [`module/jxcore`](module/jxcore) |
| 业务模块 | `jxregistration`、`jxmarketaccess`、`jxadmission`、`jxdashboard`、`jxboard`、`jxsso` |
| 新增表 | 一律 `zt_jx_*`，脚本 [`db/jenshin/install.sql`](db/jenshin/install.sql) |

首次访问健忻业务页时，`jxcore` 的 `ensureSchema()` 会执行建表脚本（幂等）。生产环境建议安装后再手动执行一次该 SQL，便于核对结果。

---

## 仓库结构

```text
jenshin-pm-system/
├── config/                 # 全局配置；站点私有项在 my.php（不入库）
│   └── ext/jenshin.php     # 健忻开关与表常量
├── db/jenshin/             # 建表、阶段门模板、数据字典
├── docs/                   # 一期说明与生产部署
├── extension/custom/       # 品牌、菜单、权限、产品档案叠加
├── module/jx*/             # 健忻业务模块
├── www/                    # Web 入口，站点根必须指到这里
└── docker-compose.yml      # 本机联调（可选）
```

站点根必须是 `www/`，不能指到仓库根，否则 `config/`、`module/` 会暴露。

---

## 本地开发

本机常见方式：PHP + MySQL/MariaDB（如 Laragon），站点根指向 `www/`，访问 [http://127.0.0.1:8080](http://127.0.0.1:8080)。

`config/my.php` 只放本机库账号与调试开关，**不要提交、不要拷到生产**。本地免登密钥仅用于联调。

需要数据库时也可走仓库根目录的 `docker-compose.yml`（库端口 `3307`，Web 容器 `18080`）。Windows 上若 Docker 端口转发不可达，仍以本机 Web 服务为准。

建表（可重复执行）：

```bash
mysql zentao < db/jenshin/install.sql
```

不要依赖 `docs/JENSHIN.md` 里提到的 `deploy/jenshin-migrate.php`、`deploy/seed_jenshin.php`，当前仓库没有这两份脚本，也不要往生产灌演示数据。

---

## 生产部署

上云步骤、Nginx 站点根、HTTPS、计划任务与验收清单见 [docs/PRODUCTION-DEPLOY.md](docs/PRODUCTION-DEPLOY.md)。

要点：

- 不要用上游一键包或官方源码包覆盖本仓库
- 安装完成后删除 `www/install.php`、`www/upgrade.php`
- `$config->debug` 必须为 `false`；库口令与免登密钥与开发环境隔离
- 计划任务用 `bin/php/crond.php` 常驻进程（systemd 保活），不是「每分钟跑一次」

---

## 集成

| 集成 | 说明 |
| --- | --- |
| 博科平台免登 | [`module/jxsso`](module/jxsso) 校验 JWT，按手机号匹配有效用户后写入会话。密钥写在服务器 `config/my.php` 的 `$config->jenshinSsoSecret`，不要入库 |

后台权限需给业务角色勾选 `jxboard`、`jxcore`，以及实际开放的注册 / 准入 / 入院模块。超级管理员默认可用；普通用户必须显式授权。

---

## 文档

| 文档 | 内容 |
| --- | --- |
| [docs/JENSHIN.md](docs/JENSHIN.md) | 一期范围、架构约束、流程摘要 |
| [docs/PRODUCTION-DEPLOY.md](docs/PRODUCTION-DEPLOY.md) | 生产环境部署与验收 |
| [db/jenshin/DICTIONARY.md](db/jenshin/DICTIONARY.md) | `zt_jx_*` 表、状态与指标 |

---

## 运行环境

| 项 | 开发 | 生产 |
| --- | --- | --- |
| PHP | 8.0–8.2，需 CLI | **8.1** + PHP-FPM |
| 数据库 | MySQL 8.0 或 MariaDB 10.6+，`utf8mb4`，表前缀 `zt_` | 同左，独立强口令 |
| Web | 本机服务，站点根 `www/` | Nginx，站点根 `/opt/jenshin/www` |
| 访问 | `http://127.0.0.1:8080` | HTTPS 正式域名 |

PHP 扩展：pdo_mysql、json、openssl、mbstring、zlib、curl、gd、iconv、filter、zip、xml、fileinfo。

---

## 许可

内核源码遵循仓库根目录 [COPYING](COPYING) 中的 AGPL 3.0 / ZPL 1.2。`extension/custom`、`module/jx*`、`config/ext/jenshin.php`、`db/jenshin` 为健忻二次开发，仅供健忻内部部署与维护，请勿当作上游官方发行物对外分发。
