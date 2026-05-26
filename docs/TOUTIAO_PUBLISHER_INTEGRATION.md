# GEOFlow ToutiaoPublisher 集成文档

## 概述

GEOFlow 通过宿主机 HTTP 服务与 ToutiaoPublisher 解耦，实现文章自动发布到头条号。

## 架构

```
GeoFlow (PHP容器)
    ↓ HTTP POST
宿主机 HTTP 服务 (127.0.0.1:18432)
    ↓ node publishArticleForAccount()
ToutiaoPublisher (playwright + BitBrowser)
    ↓ CDP
BitBrowser 已登录窗口
    ↓
mp.toutiao.com 发布页
```

## 文件清单

### 宿主机侧 (G:\ToutiaoPublisher\)

| 文件 | 说明 |
|------|------|
| `tools/geoflow-publish-server.js` | HTTP 服务入口，监听 /publish-single |
| `tools/publish_platform_once.js` | 平台路由，分发到各平台发布器 |
| `tools/publish_toutiao_once.js` | 头条发布核心逻辑 |
| `tools/toutiao_fill_helpers.js` | 填充助手，包含剪贴板操作 |
| `run_geoflow_publish_server.bat` | 启动脚本 |

### GeoFlow 侧 (G:\GEOFlow\)

| 文件 | 说明 |
|------|------|
| `app/Services/ToutiaoPublisherService.php` | PHP HTTP 客户端，调宿主机服务 |
| `app/Http/Controllers/Admin/PublishTaskController.php` | 发布任务控制器 |
| `app/Models/PublishTask.php` | 发布任务模型 |
| `config/services.php` | toutiao_publisher 配置项 |

## 启动方式

宿主机上双击运行：
```
G:\ToutiaoPublisher\run_geoflow_publish_server.bat
```

或手动：
```bash
cd G:\ToutiaoPublisher
node tools/geoflow-publish-server.js
```

## HTTP 接口

### POST /publish-single

请求：
```json
{
  "title": "文章标题",
  "content": "<p>HTML内容</p>",
  "bit_browser_id": "1b1815e660aa4304b02a22698a2695fa",
  "platform": "toutiao",
  "account_name": "1",
  "cover_path": "C:\\path\\to\\cover.jpg",
  "content_is_html": true,
  "skip_pre_publish_check": false
}
```

响应成功：
```json
{
  "success": true,
  "status": "submitted",
  "title": "文章标题",
  "published_url": null,
  "remote_article_id": null,
  "message": "submitted",
  "error": null
}
```

响应失败：
```json
{
  "success": false,
  "status": "failed",
  "error": "错误信息"
}
```

### GET /health

健康检查，返回 `{"ok": true}`

## 配置项 (.env)

```
TOUTIAO_PUBLISHER_HTTP_HOST=127.0.0.1
TOUTIAO_PUBLISHER_HTTP_PORT=18432
TOUTIAO_SKIP_PRE_PUBLISH_CHECK=false
```

## 已知问题

### 1. 中文乱码

**状态**：待解决

头条编辑器中出现中文乱码（黑色菱形问号）。原因：
- `setClipboardText()` 通过 PowerShell Set-Clipboard 写入中文时编码丢失
- 可能与 PowerShell STA 模式下的控制台编码有关

**尝试过的修复**：
- 添加 `[Console]::OutputEncoding = [System.Text.Encoding]::UTF8`
- 尝试用 StreamReader 读取 UTF8 文件
- 添加 System.Windows.Forms 引用

**仍需排查**：
- Windows 控制台默认编码 (chcp 936 vs 65001)
- PowerShell 运行时编码环境
- clipboard 实际接收到的编码

### 2. 图片上传

**状态**：未测试

封面图片通过 `cover_path` 参数传入，目前：
- 没有测试带封面图片的发布
- `pasteCoverThenMoveCaret()` 的图片粘贴逻辑未验证

### 3. 确认发布按钮超时

**状态**：部分修复

"确认发布"按钮可能在预览后不出现，需滚动页面。已添加：
- 滚动到底部
- 等待增强
- 备用按钮查找

## 依赖

- Node.js (codex runtime): `C:\Users\40336\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules`
- Playwright: `npm install playwright@1.60.0` (在 ToutiaoPublisher 目录)
- PowerShell with System.Windows.Forms
- BitBrowser 运行中，已登录头条号

## 调试

查看服务日志：
```bash
curl http://127.0.0.1:18432/health
```

手动测试发布：
```bash
curl -X POST http://127.0.0.1:18432/publish-single \
  -H "Content-Type: application/json" \
  -d '{
    "title": "测试标题",
    "content": "<p>测试内容</p>",
    "bit_browser_id": "1b1815e660aa4304b02a22698a2695fa",
    "platform": "toutiao",
    "content_is_html": true,
    "skip_pre_publish_check": true
  }'
```