# 项目交付文档模板

## 1. 项目概览
- 项目名称：教务系统
- 部署环境：Heroku
- 交付日期：2026-04-10

## 2. 环境变量与密钥管理
- 所有敏感配置仅放在 `.env`，禁止提交到仓库。
- 关键变量：
  - `APP_KEY`
  - `DB_*`
  - `OMISE_PUBLIC_KEY`
  - `OMISE_SECRET_KEY`

## 3. Heroku 部署流程
1. 创建 Heroku App 并绑定 Git 仓库。
2. 配置 Heroku Config Vars（与 `.env.example` 一致）。
3. 设置 PHP 与 Web 入口（`Procfile` / buildpack）。
4. 执行数据库迁移：`php artisan migrate --force`
5. 执行缓存命令：
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan event:cache`

## 4. Omise 集成说明（如启用）
1. 在 Omise 控制台注册测试账号。
2. 获取测试 `public key` 与 `secret key`。
3. 写入环境变量 `OMISE_PUBLIC_KEY` / `OMISE_SECRET_KEY`。
4. 前端创建 token，后端调用支付接口完成扣款。
5. 核对账单状态回写字段：`status`、`payment_reference`、`paid_at`。

## 5. 核心接口联调与测试记录
- 登录接口：POST https://poper-be-interview-01.herokuapp.com/api/login
- 课程列表接口：GET https://poper-be-interview-01.herokuapp.com/api/courses
- 课程创建接口：POST https://poper-be-interview-01.herokuapp.com/api/courses
- 账单列表接口：GET https://poper-be-interview-01.herokuapp.com/api/bills
- 账单创建接口：POST https://poper-be-interview-01.herokuapp.com/api/bills
- 支付接口：POST https://poper-be-interview-01.herokuapp.com/api/bills/{billNo}/pay

## 6. 运维与排障
- 日志位置：`storage/logs/laravel.log`
- 线上建议：
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `LOG_CHANNEL=daily`
  - `LOG_LEVEL=warning`（或 `error`）
- 常用日志命令：`tail -f storage/logs/laravel.log`
