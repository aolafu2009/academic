# 教务系统API

后端 API 已完成核心功能开发（认证、课程、账单、学生支付账单）。

## 代码规范

- 本项目使用 `laravel/pint`，并通过 `pint.json` 固定为 `PSR-12` 风格。
- 自动格式化命令：`./vendor/bin/pint`

## 生产环境缓存与错误处理

- 建议上线后执行：
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan event:cache`
- 建议环境变量：
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `LOG_CHANNEL=daily`
  - `LOG_LEVEL=warning`
- 日志查看：
  - 文件：`storage/logs/laravel.log`
  - 命令：`tail -f storage/logs/laravel.log`

## 六、AI 编程能力

本项目在开发过程中使用了 AI 编程助手（Cursor）进行人机协作，主要用于提升开发速度、联调效率和排错质量。

### 1) 我如何使用 AI 提升效率

- **需求拆解与接口落地**：先给 AI 明确架构设计，让 AI 生成分层代码骨架（Request / Controller / Service / Payment Gateway）。
- **快速生成可运行脚本**：基于当前代码结构，AI 生成了 `omise_token_test.php`，用于一键创建 Omise 测试 Token，并输出支付接口所需 JSON/curl。
- **联调与排障辅助**：遇到各种错误 如Omise的token（`tokn_test_xxx` 占位值）等问题时，AI 根据终端输出快速定位原因并给出可执行修复步骤。
- **回归验证**：每次改动后通过 AI 辅助完成语法检查、接口重试与结果核对（成功状态、支付流水号、账单状态回写）。

### 2) 是否采用 Vibe Coding（人机协作方式）

采用了“**人主导 + AI 实现细节**”的 Vibe Coding 模式：

- 我负责架构设计与验收标准（按照工厂+策略模式的方法设计Omise支付方便扩展，以及各个分层避免重复代码，提高代码的可维护性）；
- AI 负责单元测试脚本、命令模板与错误定位建议；
- 最终由我进行业务确认与结果验收，确保关键逻辑可控。

### 3) 关键 Prompt / 工作流示例

1. “根据现有 Laravel 代码，补一个 Omise 测试脚本：帮忙创建 token，并打印支付接口 JSON 和 curl。”
2. “根据PSR-12代码风格和注释生成代码，用工厂和策略模式封装支付接口方便扩展。将代码分层 单一原则，提高代码复用性和维护性”
3. “用真实 token 重试 `/api/bills/{billNo}/pay`，并确认账单状态与第三方流水是否正确回写。”

#### 工作流（实际执行）

1. AI 先读取当前代码上下文（`PayBillRequest` / `BillService` / `OmisePaymentGateway`）；
2. 生成测试脚本并本地校验可执行；
3. 通过脚本生成 `tokn_test_xxx`；
4. 调用支付接口完成联调；
5. 对失败场景（占位 token）进行定位并修正；
6. 验证成功回包（`payment_reference`、`paid_at`、`status`）是否符合预期。

### 4) AI 带来的实际收益

- 明显减少了代码编写时间；
- 提高了排错效率，尤其是在容器环境与第三方支付报错场景下。

---

> 说明：AI 主要作为开发加速与排错辅助工具，业务规则、关键决策与结果验收由本人负责。
