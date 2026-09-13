<?php

namespace Kvin\RegisterPage;

/*
 * 共享页面外壳：全站统一的头部(logo+导航+搜索) + archbar + 白色容器 + 页脚 + CSS。
 * 注册页、登录页都用它，保证完全一致；以后改头部只改这里一处。
 *
 * 占位符（由各控制器最终 strtr 填充）：
 *   __TITLE__      论坛标题
 *   __NAV1_URL__   顶部导航「日常/技术」链接
 *   __NAV2_URL__   顶部导航「情报/测评」链接
 *   __CSRF__       仅出现在各控制器自己的 <script> 里
 */
class Chrome
{
    public static function css(): string
    {
        return <<<'CSS'
  * { box-sizing: border-box; }
  body { margin: 0; background: #eef1f4; color: #1f2328; font-family: -apple-system, "Segoe UI", "Microsoft YaHei", Arial, sans-serif; font-size: 14px; }
  a { color: #0771a8; text-decoration: none; }
  a:hover { text-decoration: underline; }

  /* ===== 全站统一头部（复刻论坛顶部） ===== */
  .kv-header { background: #fff; box-shadow: 0 1px 3px rgba(20,30,50,.06); }
  .kv-header-in { max-width: 1320px; width: 94%; margin: 0 auto; display: flex; align-items: center; padding: 14px 0; }
  .kv-brand { font-size: 20px; font-weight: 700; color: #3f7cac; white-space: nowrap; }
  .kv-topnav { display: flex; gap: 18px; margin-left: 22px; }
  .kv-topnav a { color: #5a6b7a; font-size: 14px; }
  .kv-search { margin-left: auto; position: relative; }
  .kv-search input { width: 260px; max-width: 40vw; padding: 8px 34px 8px 12px; border: 1px solid #ccd2d9; border-radius: 5px; font-size: 14px; outline: none; background: #fff; }
  .kv-search input:focus { border-color: #42b983; }
  .kv-search button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; cursor: pointer; padding: 0; display: flex; align-items: center; }

  /* archbar 放在白容器内部顶部，蓝色下划线（与首页一致） */
  .kv-archbar { display: flex; align-items: center; justify-content: space-between; font-size: 13px; padding: 2px 0 8px; margin: 0 0 14px; border-bottom: 2px solid #0771a8; }
  .kv-arch-left { list-style: none; display: flex; gap: 18px; margin: 0; padding: 0; }
  .kv-arch-left a { color: #0771a8; }
  .kv-arch-right { color: #667; }
  .kv-arch-right a { color: #0771a8; margin-left: 8px; }

  /* 内容区：外层灰底，内层一个白色大容器（像论坛那样） */
  .kv-wrap { max-width: 1320px; width: 94%; margin: 0 auto; padding: 20px 0 40px; }
  .kv-panel { background: #fff; border: 1px solid #c9cfd6; padding: 16px; }
  .kv-pagehead { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin: 0 0 14px; }
  .kv-pagetitle { font-size: 20px; font-weight: 700; margin: 0; color: #1f2d3d; }
  .kv-haveaccount { font-size: 13px; color: #667; white-space: nowrap; }

  /* 分节盒子（FluxBB 风格） */
  .kv-box { border: 1px solid #ccc; background: #fff; margin-bottom: 16px; }
  .kv-box-h { background: linear-gradient(#f0f0f0,#e2e2e2); border-bottom: 1px solid #ccc; padding: 9px 14px; font-weight: 700; font-size: 13px; color: #333; }
  .kv-box-b { padding: 16px; }
  .kv-help { color: #667; font-size: 13px; line-height: 1.7; margin: 0 0 10px; }
  .kv-help:last-child { margin-bottom: 0; }

  /* 字段 */
  .kv-field { margin-bottom: 14px; }
  .kv-field:last-child { margin-bottom: 0; }
  .kv-field label { display: block; font-weight: 700; font-size: 13px; color: #333; margin: 0 0 5px 2px; }
  .kv-field label .req { color: #c0392b; margin-left: 3px; }
  .kv-field input[type=text], .kv-field input[type=email], .kv-field input[type=password] {
    width: 320px; max-width: 100%; padding: 8px 10px; border: 1px solid #a9bcc9; border-radius: 2px; background: #fff; font-size: 14px; outline: none;
  }
  .kv-field input:focus { border-color: #3f7cac; }
  .kv-fieldhint { color: #8a96a3; font-size: 12px; margin: 4px 0 0 2px; }

  /* 复选框行 / 规则同意 */
  .kv-check { display: flex; align-items: flex-start; gap: 8px; padding: 4px 0 2px; font-size: 13px; color: #444; }
  .kv-check input { margin-top: 2px; }
  .kv-rules { line-height: 1.8; color: #444; }

  /* 按钮 */
  .kv-actions { margin-top: 6px; display: flex; align-items: center; gap: 16px; }
  .kv-btn { border: 1px solid #35688f; background: #3f7cac; color: #fff; font-weight: 700; font-size: 14px; padding: 9px 22px; border-radius: 2px; cursor: pointer; }
  .kv-btn:hover { background: #35688f; }
  .kv-btn:disabled { background: #9db4c6; border-color: #9db4c6; cursor: not-allowed; }
  .kv-link { font-size: 13px; }

  /* 提示 */
  .kv-msg { display: none; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; line-height: 1.6; }
  .kv-msg.err { display: block; background: #fdecea; border: 1px solid #f5c6c2; color: #a12b21; }
  .kv-msg.ok  { display: block; background: #eaf7ee; border: 1px solid #bfe3ca; color: #256b39; }

  /* 底部页脚（复刻论坛页脚） */
  .kv-sitefoot { border-top: 2px solid #0771a8; margin-top: 16px; padding: 10px 2px; text-align: right; font-size: 12px; color: #888; }
  .kv-sitefoot a { color: #0771a8; font-weight: 700; }
CSS;
    }

    /**
     * 页面头部到内容起点（含 header + archbar + 标题行 + 消息区）。
     * @param string $docTitle  浏览器标题栏文字（如 注册 / 登录）
     * @param string $pageTitle 页面大标题
     * @param string $rightHtml 标题行右侧 HTML（如「已有帐户？登录」）
     */
    public static function top(string $docTitle, string $pageTitle, string $rightHtml): string
    {
        $css = self::css();
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$docTitle} - __TITLE__</title>
<style>
{$css}
</style>
</head>
<body>

<div class="kv-header">
  <div class="kv-header-in">
    <a class="kv-brand" href="/">__TITLE__</a>
    <nav class="kv-topnav">
      <a href="__NAV1_URL__">日常/技术</a>
      <a href="__NAV2_URL__">情报/测评</a>
    </nav>
    <form class="kv-search" action="/" method="get" role="search">
      <input name="q" type="search" placeholder="搜索">
      <button type="submit" aria-label="搜索"><svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="#8a96a3" stroke-width="2" stroke-linecap="round"><circle cx="8.5" cy="8.5" r="6"></circle><line x1="13.5" y1="13.5" x2="18" y2="18"></line></svg></button>
    </form>
  </div>
</div>

<div class="kv-wrap">
  <div class="kv-panel">

  <div class="kv-archbar">
    <ul class="kv-arch-left">
      <li><a href="/">首页</a></li>
      <li><a href="/tags">板块</a></li>
      <li><a href="/rankings">排名</a></li>
      <li><a href="/all">全部主题</a></li>
    </ul>
    <span class="kv-arch-right">
      主题：<a href="/">活跃</a> | <a href="/all">未回复</a>
      <a href="/register">注册</a>
      <a href="/login">登录</a>
    </span>
  </div>

  <div class="kv-pagehead">
    <div class="kv-pagetitle">{$pageTitle}</div>
    <div class="kv-haveaccount">{$rightHtml}</div>
  </div>

  <div id="kv-msg" class="kv-msg"></div>

HTML;
    }

    /** 页脚 + 关闭白容器（注意：<script> 与 </body></html> 由各控制器在其后自行追加）。 */
    public static function bottom(): string
    {
        return <<<'HTML'

  <div class="kv-sitefoot">由 <a href="https://flarum.org" target="_blank" rel="noopener">Flarum</a> 提供技术支持 · © __TITLE__</div>

  </div><!-- /.kv-panel -->
</div><!-- /.kv-wrap -->
HTML;
    }
}
