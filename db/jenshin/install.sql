-- Jenshin medical PM schema v1.0.0
-- Idempotent: CREATE TABLE IF NOT EXISTS + INSERT IGNORE for templates.

CREATE TABLE IF NOT EXISTS `zt_jx_schema` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL DEFAULT '',
  `version` varchar(20) NOT NULL DEFAULT '',
  `appliedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_product` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product` int unsigned NOT NULL DEFAULT 0,
  `model` varchar(80) NOT NULL DEFAULT '',
  `category` varchar(30) NOT NULL DEFAULT '',
  `line` varchar(60) NOT NULL DEFAULT '',
  `certNo` varchar(80) NOT NULL DEFAULT '',
  `certValidTo` date DEFAULT NULL,
  `specs` varchar(255) NOT NULL DEFAULT '',
  `udi` varchar(80) NOT NULL DEFAULT '',
  `manufacturer` varchar(120) NOT NULL DEFAULT '',
  `patents` varchar(500) NOT NULL DEFAULT '',
  `tenderCode` varchar(80) NOT NULL DEFAULT '',
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product` (`product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_project` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `project` int unsigned NOT NULL DEFAULT 0,
  `bizType` varchar(30) NOT NULL DEFAULT '',
  `bizID` int unsigned NOT NULL DEFAULT 0,
  `code` varchar(45) NOT NULL DEFAULT '',
  `leadDept` varchar(90) NOT NULL DEFAULT '',
  `supportDepts` varchar(255) NOT NULL DEFAULT '',
  `goal` text DEFAULT NULL,
  `health` varchar(20) NOT NULL DEFAULT 'green',
  `progress` decimal(5,2) unsigned NOT NULL DEFAULT 0.00,
  `blocker` varchar(255) NOT NULL DEFAULT '',
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project` (`project`),
  KEY `biz` (`bizType`, `bizID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL DEFAULT '',
  `name` varchar(90) NOT NULL DEFAULT '',
  `bizType` varchar(30) NOT NULL DEFAULT '',
  `needApprove` tinyint unsigned NOT NULL DEFAULT 1,
  `desc` text DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_templatestage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `template` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(90) NOT NULL DEFAULT '',
  `order` smallint unsigned NOT NULL DEFAULT 0,
  `needApprove` tinyint unsigned NOT NULL DEFAULT 1,
  `deliverable` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `template` (`template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_templatecheck` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `stage` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `required` tinyint unsigned NOT NULL DEFAULT 1,
  `order` smallint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `stage` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_stage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `project` int unsigned NOT NULL DEFAULT 0,
  `templateStage` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(90) NOT NULL DEFAULT '',
  `order` smallint unsigned NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'wait',
  `needApprove` tinyint unsigned NOT NULL DEFAULT 1,
  `deliverable` varchar(255) NOT NULL DEFAULT '',
  `task` int unsigned NOT NULL DEFAULT 0,
  `begin` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `submittedBy` varchar(30) NOT NULL DEFAULT '',
  `submittedDate` datetime DEFAULT NULL,
  `approvedBy` varchar(30) NOT NULL DEFAULT '',
  `approvedDate` datetime DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `stage` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `required` tinyint unsigned NOT NULL DEFAULT 1,
  `done` tinyint unsigned NOT NULL DEFAULT 0,
  `doneBy` varchar(30) NOT NULL DEFAULT '',
  `doneDate` datetime DEFAULT NULL,
  `order` smallint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `stage` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_approval` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `objectType` varchar(30) NOT NULL DEFAULT '',
  `objectID` int unsigned NOT NULL DEFAULT 0,
  `stage` int unsigned NOT NULL DEFAULT 0,
  `action` varchar(20) NOT NULL DEFAULT '',
  `actor` varchar(30) NOT NULL DEFAULT '',
  `result` varchar(20) NOT NULL DEFAULT '',
  `comment` text DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `object` (`objectType`, `objectID`),
  KEY `stage` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_cost` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `project` int unsigned NOT NULL DEFAULT 0,
  `dept` varchar(90) NOT NULL DEFAULT '',
  `category` varchar(60) NOT NULL DEFAULT '',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `occurDate` date DEFAULT NULL,
  `desc` varchar(255) NOT NULL DEFAULT '',
  `createdBy` varchar(30) NOT NULL DEFAULT '',
  `createdDate` datetime DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_registration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product` int unsigned NOT NULL DEFAULT 0,
  `project` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL DEFAULT '',
  `code` varchar(45) NOT NULL DEFAULT '',
  `type` varchar(30) NOT NULL DEFAULT 'first',
  `category` varchar(30) NOT NULL DEFAULT '',
  `path` varchar(80) NOT NULL DEFAULT '',
  `acceptNo` varchar(80) NOT NULL DEFAULT '',
  `applyDate` date DEFAULT NULL,
  `supplementDate` date DEFAULT NULL,
  `certDate` date DEFAULT NULL,
  `certNo` varchar(80) NOT NULL DEFAULT '',
  `certValidTo` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'wait',
  `owner` varchar(30) NOT NULL DEFAULT '',
  `leadDept` varchar(90) NOT NULL DEFAULT '',
  `supportDepts` varchar(255) NOT NULL DEFAULT '',
  `begin` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `budget` decimal(14,2) unsigned NOT NULL DEFAULT 0.00,
  `dependsOn` int unsigned NOT NULL DEFAULT 0,
  `desc` text DEFAULT NULL,
  `openedBy` varchar(30) NOT NULL DEFAULT '',
  `openedDate` datetime DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product` (`product`),
  KEY `project` (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_marketaccess` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product` int unsigned NOT NULL DEFAULT 0,
  `project` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL DEFAULT '',
  `code` varchar(45) NOT NULL DEFAULT '',
  `type` varchar(30) NOT NULL DEFAULT 'listing',
  `region` varchar(60) NOT NULL DEFAULT '',
  `platform` varchar(80) NOT NULL DEFAULT '',
  `package` varchar(80) NOT NULL DEFAULT '',
  `windowBegin` date DEFAULT NULL,
  `windowEnd` date DEFAULT NULL,
  `quote` decimal(14,2) unsigned NOT NULL DEFAULT 0.00,
  `result` varchar(30) NOT NULL DEFAULT '',
  `agreementNo` varchar(80) NOT NULL DEFAULT '',
  `fulfillBegin` date DEFAULT NULL,
  `fulfillEnd` date DEFAULT NULL,
  `requireCert` tinyint unsigned NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'wait',
  `owner` varchar(30) NOT NULL DEFAULT '',
  `leadDept` varchar(90) NOT NULL DEFAULT '',
  `supportDepts` varchar(255) NOT NULL DEFAULT '',
  `begin` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `budget` decimal(14,2) unsigned NOT NULL DEFAULT 0.00,
  `dependsOn` int unsigned NOT NULL DEFAULT 0,
  `desc` text DEFAULT NULL,
  `openedBy` varchar(30) NOT NULL DEFAULT '',
  `openedDate` datetime DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product` (`product`),
  KEY `project` (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_hospital` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '',
  `level` varchar(30) NOT NULL DEFAULT '',
  `province` varchar(40) NOT NULL DEFAULT '',
  `city` varchar(40) NOT NULL DEFAULT '',
  `department` varchar(80) NOT NULL DEFAULT '',
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `zt_jx_admission` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product` int unsigned NOT NULL DEFAULT 0,
  `project` int unsigned NOT NULL DEFAULT 0,
  `hospital` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL DEFAULT '',
  `code` varchar(45) NOT NULL DEFAULT '',
  `path` varchar(80) NOT NULL DEFAULT '',
  `department` varchar(80) NOT NULL DEFAULT '',
  `pharmacyDate` date DEFAULT NULL,
  `firstOrderDate` date DEFAULT NULL,
  `volume` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `repurchase` varchar(30) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'wait',
  `owner` varchar(30) NOT NULL DEFAULT '',
  `leadDept` varchar(90) NOT NULL DEFAULT '',
  `supportDepts` varchar(255) NOT NULL DEFAULT '',
  `begin` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `budget` decimal(14,2) unsigned NOT NULL DEFAULT 0.00,
  `desc` text DEFAULT NULL,
  `openedBy` varchar(30) NOT NULL DEFAULT '',
  `openedDate` datetime DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product` (`product`),
  KEY `project` (`project`),
  KEY `hospital` (`hospital`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `zt_jx_template` (`id`, `code`, `name`, `bizType`, `needApprove`, `desc`) VALUES
(1, 'registration', '产品注册阶段门', 'registration', 1, '路径锁定—型检—临床—体系考核—递交—发补—取证归档'),
(2, 'marketaccess', '市场准入阶段门', 'marketaccess', 1, '标讯—资质产能—成本报价—申报投标—中选签约—分量配送'),
(3, 'admission',    '推广入院阶段门', 'admission',    1, '目标筛选—院内准入—推广带教—首单放行—爬坡复购');

INSERT IGNORE INTO `zt_jx_templatestage` (`id`, `template`, `name`, `order`, `needApprove`, `deliverable`) VALUES
(1,  1, '立项与法规路径锁定', 1, 1, '法规路径说明'),
(2,  1, '型式检验送检',       2, 1, '型检报告'),
(3,  1, '临床评价/试验',      3, 1, '临床评价资料'),
(4,  1, '体系考核迎审',       4, 1, '体系考核记录'),
(5,  1, '注册申报递交',       5, 1, '申报回执'),
(6,  1, '发补应答',           6, 1, '发补答复'),
(7,  1, '取证归档与变更管控', 7, 1, '注册证扫描件'),
(8,  2, '标讯监测与信息梳理', 1, 0, '标讯摘要'),
(9,  2, '资质与产能齐套',     2, 1, '资质清单'),
(10, 2, '成本测算与报价决策', 3, 1, '报价决策单'),
(11, 2, '申报/投标',          4, 1, '申报回执'),
(12, 2, '中选确认与协议签订', 5, 1, '中选/协议'),
(13, 2, '分量落地与终端配送', 6, 1, '履约计划'),
(14, 3, '目标医院遴选与入院路径锁定', 1, 0, '目标医院清单'),
(15, 3, '药事备案与院内准入',         2, 1, '药事备案'),
(16, 3, '科室推广与临床带教',         3, 0, '带教记录'),
(17, 3, '首单入院与跟台放行',         4, 1, '首单凭证'),
(18, 3, '用量爬坡与复购维护',         5, 0, '用量复盘');

INSERT IGNORE INTO `zt_jx_templatecheck` (`id`, `stage`, `name`, `required`, `order`) VALUES
(1,  1,  '法规路径已评审锁定', 1, 1),
(2,  1,  '产品分类与标准清单齐套', 1, 2),
(3,  2,  '送检样品放行', 1, 1),
(4,  2,  '型检报告归档', 1, 2),
(5,  3,  '临床方案/评价路径确认', 1, 1),
(6,  3,  '临床资料齐套', 1, 2),
(7,  4,  '体系迎审资料齐套', 1, 1),
(8,  4,  '不符合项关闭', 1, 2),
(9,  5,  '申报资料终审通过', 1, 1),
(10, 5,  '申报回执归档', 1, 2),
(11, 6,  '发补意见分解完成', 1, 1),
(12, 6,  '发补答复提交', 1, 2),
(13, 7,  '注册证扫描件归档', 1, 1),
(14, 7,  '主数据证号/规格已更新', 1, 2),
(15, 8,  '标讯要素梳理完成', 1, 1),
(16, 9,  '注册证/体系证齐套', 1, 1),
(17, 9,  '产能与供应承诺确认', 1, 2),
(18, 10, '成本测算完成', 1, 1),
(19, 10, '报价决策已批准', 1, 2),
(20, 11, '申报材料提交', 1, 1),
(21, 12, '中选结果确认', 1, 1),
(22, 12, '协议签订归档', 1, 2),
(23, 13, '分量与配送计划发布', 1, 1),
(24, 14, '目标医院清单确认', 1, 1),
(25, 15, '药事会材料齐套', 1, 1),
(26, 15, '院内准入通过', 1, 2),
(27, 16, '科室推广计划执行', 1, 1),
(28, 17, '首单入院完成', 1, 1),
(29, 17, '跟台放行确认', 0, 2),
(30, 18, '用量与复购复盘', 1, 1);

INSERT IGNORE INTO `zt_jx_schema` (`code`, `version`, `appliedDate`) VALUES ('install', '1.0.0', NOW());
