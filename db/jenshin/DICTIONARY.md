# Jenshin 数据字典（一期）

| 表 | 说明 | 关键关联 |
|----|------|----------|
| zt_jx_schema | 迁移版本 | code 唯一 |
| zt_jx_product | 医疗产品档案 | product → zt_product.id |
| zt_jx_project | 项目业务属性 | project → zt_project.id；bizType+bizID 指向事项 |
| zt_jx_template | 流程模板 | code=registration/marketaccess/admission |
| zt_jx_templatestage | 模板阶段门 | template → zt_jx_template.id |
| zt_jx_templatecheck | 模板检查项 | stage → zt_jx_templatestage.id |
| zt_jx_stage | 项目阶段实例 | project → zt_project.id；task → zt_task.id |
| zt_jx_check | 检查项实例 | stage → zt_jx_stage.id |
| zt_jx_approval | 轻量审批记录 | stage / objectType+objectID |
| zt_jx_cost | 费用台账 | project → zt_project.id |
| zt_jx_registration | 产品注册事项 | product、project |
| zt_jx_marketaccess | 市场准入事项 | product、project |
| zt_jx_hospital | 医院档案 | — |
| zt_jx_admission | 推广入院事项 | product、project、hospital |

## 状态

事项 status：wait / doing / blocked / done  
阶段 status：wait / doing / submitted / approved / rejected / done  
健康度 health：green / yellow / red

## 指标

- 进度 = 已完成阶段 / 阶段总数
- 经费偏差 = 项目 budget − Σ cost.amount
- 证照临期：certValidTo ≤ today+90
- 投标窗口：windowEnd ≤ today+14
