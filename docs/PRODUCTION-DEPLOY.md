# 健忻医疗项目管理系统 · 生产环境部署文档

本文说明如何把**当前仓库**部署到云服务器，作为正式生产环境。系统基于禅道开源版 **22.5**，叠加 `module/jx*` 与 `config/ext/jenshin.php` 的医疗项目管理能力。

> 不要用官方一键包（zbox）或官方源码包直接覆盖本仓库，否则会丢掉二次开发。  
> 本地 Docker / Laragon / `config/my.php` 只用于开发，**不要原样拷到生产**。

---

## 1. 系统构成

| 层级 | 内容 |
| --- | --- |
| 应用 | PHP 禅道 + 健忻模块（产品注册 / 市场准入 / 推广入院 / 看板 / 阶段门） |
| Web 入口 | 仅暴露 `www/`，站点根目录必须指向这里 |
| 数据库 | MySQL 8.0，库名建议 `zentao`，表前缀 `zt_`，业务表 `zt_jx_*` |
| 附件 | `www/data/upload/` |
| 运行时 | `tmp/`（日志、缓存、session） |
| 站点配置 | `config/my.php`（仓库不跟踪，服务器上单独生成） |

健忻表由 `module/jxcore` 的 `ensureSchema()` 在首次访问业务页时自动执行 `db/jenshin/install.sql`（幂等）。生产环境建议安装完成后**再手动执行一次**该 SQL，便于确认结果。

`docs/JENSHIN.md` 里提到的 `deploy/jenshin-migrate.php`、`deploy/seed_jenshin.php` **当前仓库不存在**，生产不要依赖它们，也不要灌演示数据。

---

## 2. 服务器选型

按同时在线人数估算（可纵向升级）：

| 规模 | 建议规格 |
| --- | --- |
| 试点 / ≤20 人 | 2 核 4 GB，系统盘 40 GB SSD |
| 正式 / 20–50 人 | 4 核 8 GB，系统盘 80 GB SSD |
| 更大或附件很多 | Web 与 MySQL 分机，附件盘单独挂载 |

推荐操作系统：**Ubuntu 22.04 LTS**（Debian 12 亦可）。云安全组只放行 **22 / 80 / 443**，MySQL **不要**对公网开放。

时区统一 `Asia/Shanghai`。

---

## 3. 软件要求

| 组件 | 生产建议 | 说明 |
| --- | --- | --- |
| PHP | **8.1**（8.0–8.2 可用） | 必须带 CLI，供计划任务使用 |
| PHP 扩展 | pdo、pdo_mysql、json、openssl、mbstring、zlib、curl、gd、iconv、filter、zip、xml、fileinfo | 安装向导会检查前若干项；zip/gd/xml 用于导出与附件 |
| Web | **Nginx + PHP-FPM** | 本仓库默认 `requestType = PATH_INFO`，必须正确传递 PATH_INFO |
| 数据库 | **MySQL 8.0**（MariaDB 10.6+ 也可） | 字符集 `utf8mb4` |
| TLS | 证书（Let’s Encrypt 或公司证书） | 框架已开启 `setCookieSecure`，**必须 HTTPS**，否则登录 Cookie 可能不生效 |

PHP 建议参数（写入 `/etc/php/8.1/fpm/conf.d/99-jenshin.ini`，并同样给 CLI）：

```ini
memory_limit = 256M
max_execution_time = 120
max_input_time = 120
post_max_size = 100M
upload_max_filesize = 100M
max_input_vars = 10000
date.timezone = Asia/Shanghai
display_errors = Off
expose_php = Off
session.cookie_httponly = 1
session.cookie_secure = 1
```

改完后重启：`systemctl restart php8.1-fpm`。

---

## 4. 部署路径约定

下文默认：

```text
应用目录   /opt/jenshin
站点根目录 /opt/jenshin/www
运行用户   www-data
访问域名   https://pm.example.com
```

可按公司规范改路径和域名，后文命令需同步替换。

---

## 5. 在服务器上装基础软件

以 Ubuntu 22.04 为例：

```bash
sudo apt update
sudo apt install -y nginx mysql-server \
  php8.1-fpm php8.1-cli php8.1-mysql php8.1-mbstring php8.1-xml \
  php8.1-gd php8.1-curl php8.1-zip php8.1-bcmath php8.1-intl \
  unzip rsync certbot python3-certbot-nginx
```

确认 PHP 版本与扩展：

```bash
php -v
php -m | grep -E 'pdo_mysql|mbstring|openssl|curl|gd|zip|xml'
```

---

## 6. 创建数据库

不要用开发环境口令（本地 `jenshin123` 一类弱口令禁止上生产）。

```bash
sudo mysql
```

```sql
CREATE DATABASE zentao DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'jenshin'@'localhost' IDENTIFIED BY '请换成高强度口令';
GRANT ALL PRIVILEGES ON zentao.* TO 'jenshin'@'localhost';
FLUSH PRIVILEGES;
```

---

## 7. 同步代码

### 7.1 不要带上的内容

| 路径 | 原因 |
| --- | --- |
| `config/my.php` | 含本机库地址、弱口令、`debug = true` |
| `tmp/` | 本机缓存与日志 |
| `www/data/` | 本机附件；若要迁历史数据再单独拷 `upload/` |
| `test/`、`.cursor/`、`.claude/` | 开发与测试 |
| `deploy/windows/` | 本机从 Docker 导组织用，会写入弱口令，**禁止直接在生产跑** |

`www/install.php`、`www/upgrade.php` 被 `.gitignore` 忽略。从 git 拉代码后必须从模板生成：

```bash
cp /opt/jenshin/www/install.php.tmp /opt/jenshin/www/install.php
cp /opt/jenshin/www/upgrade.php.tmp /opt/jenshin/www/upgrade.php
```

### 7.2 推荐：rsync（从有完整代码的机器推）

在能访问云主机的机器上（Windows 可用 WSL）：

```bash
rsync -avz --delete \
  --exclude '.git' \
  --exclude 'config/my.php' \
  --exclude 'tmp/' \
  --exclude 'www/data/' \
  --exclude 'test/' \
  --exclude '.cursor/' \
  --exclude '.claude/' \
  ./  deploy@云主机IP:/opt/jenshin/
```

服务器上补齐运行目录：

```bash
sudo mkdir -p /opt/jenshin/tmp/{cache,log,model,extension} \
              /opt/jenshin/www/data/upload
sudo chown -R www-data:www-data /opt/jenshin
sudo find /opt/jenshin -type d -exec chmod 755 {} \;
sudo find /opt/jenshin -type f -exec chmod 644 {} \;
sudo chmod -R 775 /opt/jenshin/tmp /opt/jenshin/www/data /opt/jenshin/config
```

安装向导需要能写 `config/my.php`。装完后可把 `config/` 收紧为 `750`，`my.php` 为 `640`。

---

## 8. Nginx

站点根必须是 `www`，不能指到 `/opt/jenshin`，否则 `config/`、`module/` 会暴露。

`/etc/nginx/sites-available/jenshin`：

```nginx
server {
    listen 80;
    server_name pm.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name pm.example.com;

    # ssl_certificate     /etc/letsencrypt/live/pm.example.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/pm.example.com/privkey.pem;

    root /opt/jenshin/www;
    index index.php;

    client_max_body_size 100M;

    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
        }
    }

    location ~ [^/]\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;

        set $path_info "";
        set $real_script_name $fastcgi_script_name;
        if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
            set $real_script_name $1;
            set $path_info $2;
        }

        fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
        fastcgi_param SCRIPT_NAME     $real_script_name;
        fastcgi_param PATH_INFO       $path_info;
        fastcgi_read_timeout 120;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?)$ {
        expires 7d;
        access_log off;
    }

    location ~ /\. { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/jenshin /etc/nginx/sites-enabled/jenshin
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d pm.example.com
```

PATH_INFO 配不通时，可临时把 `config/my.php` 里 `$config->requestType` 改成 `'GET'`，地址会变成 `index.php?m=xxx&f=yyy`。正式环境优先修好 Nginx。

---

## 9. 安装禅道（首次）

浏览器打开 `https://pm.example.com`，会跳到安装向导。

1. 环境检查全部通过（目录可写、扩展已加载）。
2. 数据库填第 6 节的主机、库名、账号；表前缀保持 `zt_`；编码 `utf8mb4`。
3. **不要勾选导入演示数据**。
4. 公司名称填实际名称（如「健忻医疗」）。
5. 管理员账号用独立口令，不要用 `123456` / `Admin123`。
6. 默认语言选简体中文。

向导会写入 `config/my.php`。装完立刻检查并改成生产值：

```php
<?php
$config->installed      = true;
$config->debug          = false;          // 生产必须 false
$config->requestType    = 'PATH_INFO';
$config->timezone       = 'Asia/Shanghai';
$config->db->driver     = 'mysql';
$config->db->host       = '127.0.0.1';
$config->db->port       = '3306';
$config->db->name       = 'zentao';
$config->db->user       = 'jenshin';
$config->db->encoding   = 'utf8mb4';
$config->db->collation  = 'utf8mb4_general_ci';
$config->db->password   = '与第6节一致';
$config->db->prefix     = 'zt_';
$config->db->strictMode = false;
$config->webRoot        = getWebRoot();
$config->default->lang  = 'zh-cn';
```

然后：

```bash
sudo rm -f /opt/jenshin/www/install.php /opt/jenshin/www/upgrade.php
```

系统若提示「请删除 install.php」，按提示操作即可。

---

## 10. 落地健忻扩展

访问任一健忻业务页（如数据看板、产品注册）时，`jxcore` 会自动建 `zt_jx_*` 表并写入阶段门模板。

建议在服务器上再执行一次，便于核对：

```bash
sudo mysql zentao < /opt/jenshin/db/jenshin/install.sql
```

该脚本使用 `CREATE TABLE IF NOT EXISTS` / `INSERT IGNORE`，可重复执行。成功后应能看到 `zt_jx_schema`、`zt_jx_template` 等表，且模板 3 条、阶段 18 条。

后台「权限」里给业务角色勾选：

- `jxboard`（数据看板）
- `jxregistration`（产品注册）
- `jxmarketaccess`（市场准入）
- `jxadmission`（推广入院）
- `jxcore`（阶段门 / 费用等）

超级管理员默认可用。普通用户必须显式授权，否则菜单能看到也可能进不去。

---

## 11. 计划任务（必须）

禅道的提醒、备份调度、部分定时计算依赖 cron。`bin/php/crond.php` 是**常驻循环**，不要按「每分钟跑一次」理解。

```bash
sudo /opt/jenshin/bin/init.sh /usr/bin/php https://pm.example.com
```

用 systemd 保活，`/etc/systemd/system/jenshin-cron.service`：

```ini
[Unit]
Description=Jenshin ZenTao cron daemon
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/jenshin
ExecStart=/usr/bin/php /opt/jenshin/bin/php/crond.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now jenshin-cron
sudo systemctl status jenshin-cron
```

---

## 12. 生产加固

1. `config/my.php` 中 `$config->debug = false`。
2. 删除 `www/install.php`、`www/upgrade.php`。
3. 全站 HTTPS；HTTP 301 到 HTTPS。
4. MySQL 仅本机或内网；云安全组不放行 3306。
5. 管理员、数据库口令与开发环境隔离；首次登录强制改密。
6. 不要把 `deploy/windows/import-org.php` 拿到生产跑（脚本里是开发弱口令）。
7. 定期看 `tmp/log/`，磁盘告警盯 `www/data/` 与 binlog。
8. 云主机做快照；应用层按第 13 节备份。

---

## 13. 备份与恢复

最少备份两样：**数据库** + **附件**。

```bash
# 建议写入 root crontab，每天凌晨
0 2 * * * mysqldump --single-transaction --routines --triggers -ujenshin -p'口令' zentao | gzip > /data/backup/zentao-$(date +\%F).sql.gz
0 2 * * * rsync -a /opt/jenshin/www/data/upload/ /data/backup/upload/
```

也可在系统后台使用禅道自带备份，但仍建议机器级 `mysqldump`，不要只依赖应用内备份。

恢复：

```bash
gunzip -c /data/backup/zentao-YYYY-MM-DD.sql.gz | mysql zentao
rsync -a /data/backup/upload/ /opt/jenshin/www/data/upload/
sudo chown -R www-data:www-data /opt/jenshin/www/data
```

保留至少 7 天日备 + 每月一份。备份文件不要放在 `www/` 下。

---

## 14. 邮件（建议上线后立刻配）

后台「后台 → 消息 → 邮件」配置 SMTP。发信失败常见原因：未开 `openssl`、云厂商封 25 端口（改用 465/587）。

配好后用「测试发信」验证，计划任务才能把超时、阶段提醒发出去。

---

## 15. 从现有环境迁数据（可选）

若要把本地 / Docker 里已有业务迁到云上，在**目标库已装完同版本禅道**后进行：

1. 源库停写或选业务低峰。
2. `mysqldump` 源库，导入生产库（会覆盖生产数据）。
3. 同步源环境 `www/data/upload/`。
4. **只改**生产 `config/my.php` 的库地址与口令，保持 `$config->debug = false`。
5. 清 `tmp/cache`、`tmp/model`。
6. 用生产域名登录核对附件、事项、阶段门。

不要把开发机 `my.php` 整文件覆盖上去。

---

## 16. 版本更新

1. 备份库和附件。
2. rsync 新代码（继续排除 `config/my.php`、`www/data`、`tmp`）。
3. 如有新的 `db/jenshin/` 脚本再执行；`install.sql` 可重复导入。
4. 若框架版本变化，临时恢复 `upgrade.php.tmp` → `upgrade.php`，打开升级页，完成后删掉。
5. `chown` 回 `www-data`，清缓存目录。
6. 按第 17 节回归。

日常只发健忻模块时，一般不用走禅道升级向导。

---

## 17. 上线验收

用浏览器按真实用户路径走一遍（不要只看首页截图）：

- [ ] `https://域名` 能打开登录页，无混合内容、无证书告警
- [ ] 管理员登录成功；HTTP 访问会被转到 HTTPS
- [ ] 产品列表、项目列表可打开
- [ ] 产品注册 / 市场准入 / 推广入院：列表 → 新建 → 详情
- [ ] 数据看板有数据或空态正常
- [ ] 上传一张附件，详情页能下载
- [ ] 后台权限：业务角色能进健忻模块，未授权角色进不去
- [ ] `systemctl is-active jenshin-cron nginx php8.1-fpm mysql` 均为 active
- [ ] `mysql zentao -e "SHOW TABLES LIKE 'zt_jx_%';"` 有表
- [ ] `tmp/log` 无持续 PHP Fatal

---

## 18. 常见问题

**页面 404 或一直跳到 `index.php`**  
Nginx 未把 PATH_INFO 传给 PHP。对照第 8 节 `fastcgi_param PATH_INFO`，或临时改 `requestType` 为 `GET`。

**登录后立刻退出 / Cookie 无效**  
未上 HTTPS，或反代后 PHP 不知道自己是 HTTPS。在反代上加 `X-Forwarded-Proto`，并保证浏览器地址是 `https://`。

**安装向导报目录不可写**  
`config/`、`tmp/`、`www/data/` 属主应为 `www-data`。

**数据库连接失败**  
`my.php` 里 host 用 `127.0.0.1`（不要用本机开发地址）；确认用户只有 `localhost` 权限。

**健忻菜单没有或打开报错**  
先确认 `config/ext/jenshin.php` 在目录中；再执行 `db/jenshin/install.sql`；再给权限分组勾模块。

**上传失败**  
同时提高 `upload_max_filesize`、`post_max_size`、Nginx `client_max_body_size`，并重启 php-fpm / reload nginx。

**计划任务不跑**  
`jenshin-cron` 是否在跑；`bin/init.sh` 是否已执行；看 `tmp/log` 与 `journalctl -u jenshin-cron`。

---

## 19. 上线后运维清单

| 频率 | 事项 |
| --- | --- |
| 每天 | 确认备份文件生成、磁盘水位 |
| 每周 | 看错误日志、抽查上传与登录 |
| 每月 | 轮转管理员口令策略、检查证书有效期 |
| 发版 | 按第 16 节更新，按第 17 节回归 |

---

## 附录 A · 目录与进程

```text
/opt/jenshin/
├── config/my.php          # 生产配置（不入库）
├── config/ext/jenshin.php # 健忻开关与表常量
├── db/jenshin/install.sql # 健忻建表 + 阶段门模板
├── module/jx*             # 业务模块
├── bin/php/crond.php      # 计划任务守护
├── tmp/                   # 日志与缓存
└── www/                   # Nginx root
    └── data/upload/       # 附件
```

进程：`nginx`、`php-fpm`、`mysql`、`jenshin-cron`。

---

## 附录 B · 与开发环境的差异

| 项 | 开发 | 生产 |
| --- | --- | --- |
| `debug` | 常为 true | 必须 false |
| 数据库口令 | 本机弱口令 | 独立强口令 |
| 演示数据 | 可有 | 不导入 |
| `install.php` | 可保留 | 安装后删除 |
| 组织导入脚本 | `deploy/windows/` | 不用 |
| 访问 | `http://127.0.0.1:8080` | `https://正式域名` |
