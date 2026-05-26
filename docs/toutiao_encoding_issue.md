# GEOFlow ToutiaoPublisher 乱码问题

## 问题描述

通过 GEOFlow 发布到头条号时，标题和正文出现中文乱码（黑色菱形问号�）。

## 影响范围

- 标题：显示为乱码
- 正文：显示为乱码

## 技术分析

### 调用链路

```
GeoFlow ToutiaoPublisherService
    ↓ POST JSON (UTF-8)
geoflow-publish-server.js
    ↓ publishArticleForAccount()
publish_toutiao_once.js
    ↓ fillArticlePage()
toutiao_fill_helpers.js
    ↓ setClipboardText() + pasteAtCursor()
BitBrowser CDP → mp.toutiao.com 编辑器
```

### 乱码发生位置

乱码发生在 `toutiao_fill_helpers.js` 的 `setClipboardText()` 函数，通过 PowerShell 写入 Windows 剪贴板：

```javascript
function setClipboardText(text) {
  const tmpPath = path.join(os.tmpdir(), `toutiao-clipboard-${Date.now()}.txt`);
  fs.writeFileSync(tmpPath, text, "utf8");
  const escapedPath = tmpPath.replace(/'/g, "''");
  runPowerShell(
    "Add-Type -AssemblyName System.Windows.Forms; " +
    "[System.Windows.Forms.Clipboard]::SetText((Get-Content -LiteralPath '" +
    escapedPath +
    "' -Raw -Encoding UTF8))"
  );
  fs.unlinkSync(tmpPath);
}
```

### 已尝试的修复

1. **添加 Add-Type System.Windows.Forms**
   - 原因：`[System.Windows.Forms.Clipboard]` 未找到
   - 结果：解决了 TypeNotFound 错误

2. **设置 [Console]::OutputEncoding = UTF8**
   - 结果：PowerShell 解析失败 (& 符号转义问题)

3. **用 StreamReader 读取 UTF8 文件**
   - 结果：PowerShell 脚本语法问题

4. **写入 BOM + StreamReader**
   - 结果：无效编码 `utf8BOM`

5. **使用 Node.js Buffer 处理 BOM**
   ```javascript
   const bom = Buffer.from([0xEF, 0xBB, 0xBF]);
   fs.writeFileSync(tmpPath, Buffer.concat([bom, content]));
   ```
   - 结果：未测试最终效果

### 关键代码位置

- `G:\ToutiaoPublisher\tools\toutiao_fill_helpers.js` 第 94-106 行
- `G:\ToutiaoPublisher\tools\publish_toutiao_once.js` 第 133-175 行
- `G:\GEOFlow\app\Services\ToutiaoPublisherService.php`

## 根本原因分析

### 假设1：PowerShell Get-Content 编码问题

`Get-Content -LiteralPath '...' -Raw -Encoding UTF8` 可能不保持 UTF8 BOM。

### 假设2：PowerShell 控制台编码问题

PowerShell STA 模式下，控制台编码可能不是 UTF8。

### 假设3：Set-Clipboard / SetText 编码问题

Windows 剪贴板可能以系统默认编码（936 GBK）存储中文。

## 可能的解决方案

### 方案A：使用 PowerShell 5.1 的 [System.Windows.Forms.Clipboard]

```powershell
Add-Type -AssemblyName System.Windows.Forms
$bytes = [System.IO.File]::ReadAllBytes($path)
$mem = [System.IO.MemoryStream]::new($bytes, 3, $bytes.Length - 3)
$reader = [System.IO.StreamReader]::new($mem, [System.Text.Encoding]::UTF8)
[System.Windows.Forms.Clipboard]::SetText($reader.ReadToEnd())
```

### 方案B：使用 PowerShell 7 的 clipboard 模块

```bash
pwsh -c "Set-Clipboard -Value (Get-Content test.txt -Raw)"
```

### 方案C：直接用 PowerShell 写入 Clipboard

```powershell
$content = Get-Content -Path $path -Raw -Encoding UTF8
[System.Windows.Forms.Clipboard]::SetText($content, [System.Windows.Forms.TextDataFormat]::UnicodeText)
```

### 方案D：使用 Node.js clipboardy 库

```bash
npm install clipboardy
```

```javascript
const clipboardy = require('clipboardy');
clipboardy.writeSync(text);
```

## 下一步行动

1. **验证 PowerShell 脚本**：单独测试 PowerShell 编码问题
2. **尝试方案C/D**：用不同方式写入剪贴板
3. **对比 publish_single_article.js**：检查原脚本是否有类似问题

## 相关文件

| 文件 | 关键行 |
|------|--------|
| `toutiao_fill_helpers.js` | 94-106 (setClipboardText) |
| `toutiao_fill_helpers.js` | 147-150 (pasteAtCursor) |
| `publish_toutiao_once.js` | 133-175 (publishArticleForAccount) |
| `publish_toutiao_once.js` | 57-67 (clickPublishOnce) |
| `geoflow-publish-server.js` | 94-124 (handlePublish) |
| `ToutiaoPublisherService.php` | 25-37 (payload) |