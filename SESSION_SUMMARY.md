# Session 2026-05-25

## Fixed
- **WritingTaskController**: keywords 数组→字符串修复, 中文 slug 生成改用 `generateUniqueSlug()`, category slug 修复, 清理空 slug 分类
- **积分系统全删**: models(UserPoint,PointTransaction), service(PointService), controller(UserPointController), views(admin/points/*), routes, 2 DB tables, migrations. 从 5 个 controller 移除 PointService, 从 5 个 model 移除 points_consumed, 清理 13+ Blade 视图

## Installed
- **AutoGEO 微服务**: Python Flask port 5000, MiniMax M2.7 via OpenAI-compatible API, 已测试通过, 已加开机启动

## Stopped
- `geoflow-publish-server.js` (端口 18432)

## Understood (not yet changed)
旧批量脚本 `publish_toutiao_batch.js` + `publish_toutiao_once.js` 工作机制:
1. 从 `G:\ToutiaoPublisher\articles\作者名\` 下扫描 MD 文件
2. 窗口→作者映射: 1-4→宋毅, 5-10→刘德顺, 11-12→尹卫民
3. 各窗口每天最多 5 篇, 5-10 分钟间隔轮询
4. 发完删除 MD 文件

现存 MD 文章: 李兴 261, 刘德顺 265, 宋毅 353, 尹卫民 1258, 于国东 268

## Planned (tomorrow)
改造 `publishNextForAccount` 改为 HTTP 调 GeoFlow DB API 取文章, 只发新生成的文章(任务2的30篇刘德顺)
