# Notebook 主题

一个仿笔记本 / 信纸风格的响应式 WordPress 主题，适合个人博客、记录类站点，也支持技术文章中的代码展示。

- 主题名称：`Notebook`
- 主题作者：`RucLinux`
- 官方网站：[https://www.myzhenai.com.cn/](https://www.myzhenai.com.cn/)

- 主题的特点：轻便、简洁
适用于：小说站点、日记类站点、技术类站点、生活类站点
主题介绍：http://notebook.myzhenai.com.cn/
主题预览：http://qinwang.haikou-china.com/
主题特点：回复可以上传图片（限制500k），过滤所有伪造图片上传的文件，限制1小时只能上传3张图片，请后台开启评论需要审核，非审核的所有附件，7天后就会全部自动删除

## 功能特性

- 封面式首页 + 目录页风格
- 信纸样式正文排版
- 响应式布局（桌面与移动端）
- 评论区访客信息记录（IP / UA / 系统 / 浏览器）
- 评论图片上传（含大小限制与清理）
- 文章媒体自动识别（图片 / 音频 / 视频链接自动转标签）
- 代码块高亮（highlight.js）

## 环境要求

- WordPress >= 5.0
- PHP >= 7.4（建议 8.x）

## 安装方式

1. 将主题目录放入：`wp-content/themes/Notebook`
2. 进入后台：`外观 -> 主题`
3. 启用 `Notebook`

## 代码高亮使用说明

主题前台已集成 `highlight.js`，会自动高亮 `<pre><code>` 结构。

### 推荐写法：围栏代码块

在文章正文（建议“文本”模式）使用：

````markdown
```javascript
console.log('hello notebook');
```
````

支持常见语言：`javascript`、`php`、`css`、`bash`、`json`、`sql` 等。

> 兼容说明：主题对常见误输入（如 `,,,`）做了兼容，但仍建议始终使用标准反引号围栏 ` ``` `。

### HTML 写法

也可直接写：

```html
<pre class="notebook-code-block"><code class="language-php">&lt;?php echo 'hi';</code></pre>
```

注意：代码内容中的 `<`、`>` 建议转义为 `&lt;`、`&gt;`，避免被编辑器或过滤器误处理。

## 常见问题

### 1) 代码块没有高亮

请检查：

- 是否使用了标准反引号围栏（不是逗号、顿号、弯引号）
- 开始行是否写成 ` ```语言名 `（如 ` ```php `）
- 前台是否成功加载了 highlight.js 资源（可用浏览器开发者工具查看）

### 2) `<pre><code>` 里的 PHP 被截断

例如 `<?php` 显示不完整，通常是保存时被内容过滤。建议：

- 优先使用围栏代码块
- 或确保代码内容已实体化（`&lt;?php`）

## 目录结构（核心文件）

- `style.css`：主题样式与主题头信息
- `functions.php`：主题功能、资源加载、内容过滤
- `front-page.php`：封面首页模板
- `single.php`：文章页模板
- `page-notebook-directory.php`：目录页模板

## 版本信息

当前主题版本见 `style.css` 头部：`Version: 1.0.0`

## License

建议使用 GPLv2 或更高版本（与 WordPress 生态保持一致）。
