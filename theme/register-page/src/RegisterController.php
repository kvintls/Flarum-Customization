<?php

namespace Kvin\RegisterPage;

use Flarum\Settings\SettingsRepositoryInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RegisterController implements RequestHandlerInterface
{
    /** @var SettingsRepositoryInterface */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // 从当前会话取 CSRF token（GET 请求不受 CSRF 校验限制）
        $session = $request->getAttribute('session');
        $csrf = ($session && method_exists($session, 'token')) ? $session->token() : '';

        $title = (string) ($this->settings->get('forum_title') ?: 'Forum');

        // 论坛规则文字（可自行修改）。用 <br> 分段，HTML 已转义标题但规则这里是可信的站点文案。
        $rules = "本论坛仅限 Arch Linux x86_64 用户。<br><br>"
            . "不适用于 Artix、Apricity、Manjaro 或任何“简易 Arch 安装程序”，也不适用于 Arch-ARM；仅限纯净的 64 位 Arch Linux。如需帮助，请联系相应的社区。<br><br>"
            . "注册本论坛即表示您同意本站隐私政策，并且您在论坛上发布的任何信息都将被视为“公共信息”。";

        $html = strtr($this->template(), [
            '__CSRF__'  => htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'),
            '__TITLE__' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            '__RULES__' => $rules,
        ]);

        return new HtmlResponse($html);
    }

    protected function template(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>注册 - __TITLE__</title>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; background: #eef1f4; color: #1f2328; font-family: -apple-system, "Segoe UI", "Microsoft YaHei", Arial, sans-serif; font-size: 14px; }
  a { color: #0771a8; text-decoration: none; }
  a:hover { text-decoration: underline; }
  .kv-wrap { max-width: 900px; margin: 0 auto; padding: 0 16px 40px; }

  /* 顶部标题栏 */
  .kv-top { border-bottom: 2px solid #0771a8; padding: 16px 2px 10px; display: flex; align-items: baseline; justify-content: space-between; }
  .kv-top .kv-logo { font-size: 20px; font-weight: 700; color: #0771a8; }
  .kv-top .kv-nav a { margin-left: 16px; font-size: 13px; }

  .kv-pagetitle { font-size: 20px; font-weight: 700; margin: 20px 2px 14px; color: #1f2d3d; }

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

  /* 规则同意 */
  .kv-agree { display: flex; align-items: flex-start; gap: 8px; padding: 12px 0 2px; }
  .kv-agree input { margin-top: 2px; }
  .kv-rules { line-height: 1.8; color: #444; }

  /* 按钮 */
  .kv-actions { margin-top: 6px; }
  .kv-btn { border: 1px solid #35688f; background: #3f7cac; color: #fff; font-weight: 700; font-size: 14px; padding: 9px 22px; border-radius: 2px; cursor: pointer; }
  .kv-btn:hover { background: #35688f; }
  .kv-btn:disabled { background: #9db4c6; border-color: #9db4c6; cursor: not-allowed; }

  /* 提示 */
  .kv-msg { display: none; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; line-height: 1.6; }
  .kv-msg.err { display: block; background: #fdecea; border: 1px solid #f5c6c2; color: #a12b21; }
  .kv-msg.ok  { display: block; background: #eaf7ee; border: 1px solid #bfe3ca; color: #256b39; }

  .kv-foot { text-align: center; color: #667; font-size: 13px; margin-top: 8px; }
</style>
</head>
<body>
<div class="kv-wrap">

  <div class="kv-top">
    <a class="kv-logo" href="/">__TITLE__</a>
    <span class="kv-nav">
      <a href="/">返回首页</a>
      <a href="/?login=1">登录</a>
    </span>
  </div>

  <div class="kv-pagetitle">注册</div>

  <div id="kv-msg" class="kv-msg"></div>

  <form id="kv-form" autocomplete="off">

    <div class="kv-box">
      <div class="kv-box-h">重要信息</div>
      <div class="kv-box-b">
        <p class="kv-help">注册后，您将可以使用许多原本无法使用的功能。如果您对本论坛有任何疑问，请咨询管理员。</p>
        <p class="kv-help">下方表格请如实填写。注册成功后，系统可能会向您的邮箱发送一封验证邮件，请注意查收。</p>
      </div>
    </div>

    <div class="kv-box">
      <div class="kv-box-h">请输入长度在 2 到 25 个字符之间的用户名</div>
      <div class="kv-box-b">
        <div class="kv-field">
          <label for="kv-username">用户名<span class="req">*</span></label>
          <input id="kv-username" name="username" type="text" required>
        </div>
      </div>
    </div>

    <div class="kv-box">
      <div class="kv-box-h">请输入并确认有效的电子邮件地址</div>
      <div class="kv-box-b">
        <p class="kv-help">您必须输入一个有效的电子邮件地址。</p>
        <div class="kv-field">
          <label for="kv-email">电子邮件<span class="req">*</span></label>
          <input id="kv-email" name="email" type="email" required>
        </div>
        <div class="kv-field">
          <label for="kv-email2">确认电邮地址<span class="req">*</span></label>
          <input id="kv-email2" name="email2" type="email" required>
        </div>
      </div>
    </div>

    <div class="kv-box">
      <div class="kv-box-h">设置密码</div>
      <div class="kv-box-b">
        <div class="kv-field">
          <label for="kv-password">密码<span class="req">*</span></label>
          <input id="kv-password" name="password" type="password" autocomplete="new-password" required>
          <div class="kv-fieldhint">至少 8 个字符。</div>
        </div>
      </div>
    </div>

    <div class="kv-box">
      <div class="kv-box-h">您必须同意以下条款才能注册</div>
      <div class="kv-box-b">
        <div class="kv-rules">__RULES__</div>
        <label class="kv-agree">
          <input id="kv-agree" type="checkbox">
          <span>我已阅读并同意以上论坛规则与隐私政策。</span>
        </label>
      </div>
    </div>

    <div class="kv-actions">
      <button id="kv-submit" class="kv-btn" type="submit" disabled>注册</button>
    </div>

  </form>

  <div class="kv-foot">已有帐户？ <a href="/?login=1">登录</a></div>
</div>

<script>
(function(){
  var CSRF = "__CSRF__";
  var form = document.getElementById('kv-form');
  var msg = document.getElementById('kv-msg');
  var agree = document.getElementById('kv-agree');
  var submit = document.getElementById('kv-submit');

  agree.addEventListener('change', function(){ submit.disabled = !agree.checked; });

  function showErr(text){ msg.className = 'kv-msg err'; msg.innerHTML = text; window.scrollTo(0,0); }
  function showOk(text){ msg.className = 'kv-msg ok'; msg.innerHTML = text; window.scrollTo(0,0); }

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    msg.className = 'kv-msg';

    var username = document.getElementById('kv-username').value.trim();
    var email = document.getElementById('kv-email').value.trim();
    var email2 = document.getElementById('kv-email2').value.trim();
    var password = document.getElementById('kv-password').value;

    if(!agree.checked){ showErr('请先勾选同意论坛规则。'); return; }
    if(username.length < 2 || username.length > 25){ showErr('用户名长度需在 2 到 25 个字符之间。'); return; }
    if(email !== email2){ showErr('两次输入的电子邮件地址不一致。'); return; }
    if(password.length < 8){ showErr('密码至少 8 个字符。'); return; }

    submit.disabled = true; submit.textContent = '提交中…';
    try {
      var reg = await fetch('/api/users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-CSRF-Token': CSRF },
        body: JSON.stringify({ data: { attributes: { username: username, email: email, password: password } } })
      });

      if (reg.status === 201 || reg.ok) {
        // 尝试自动登录；若需邮箱验证则登录会失败，提示去验证
        try {
          var login = await fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-CSRF-Token': CSRF },
            body: JSON.stringify({ identification: username, password: password, remember: true })
          });
          if (login.ok) { window.location = '/'; return; }
        } catch (e) {}
        showOk('注册成功！如需邮箱验证请查收邮件；完成后即可 <a href="/?login=1">登录</a>。');
        submit.textContent = '注册成功';
        return;
      }

      // 失败：解析 JSON:API 错误
      var data = null; try { data = await reg.json(); } catch(e){}
      var text = '注册失败。';
      if (data && data.errors && data.errors.length) {
        text = data.errors.map(function(x){ return (x.detail || x.title || '出错了'); }).join('<br>');
      } else if (reg.status === 429) {
        text = '操作过于频繁，请稍后再试。';
      }
      showErr(text);
      submit.disabled = false; submit.textContent = '注册';
    } catch (err) {
      showErr('网络错误，请稍后重试。');
      submit.disabled = false; submit.textContent = '注册';
    }
  });
})();
</script>
</body>
</html>
HTML;
    }
}
