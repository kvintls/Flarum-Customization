<?php

namespace Kvin\RegisterPage;

use Flarum\Settings\SettingsRepositoryInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginController implements RequestHandlerInterface
{
    /** @var SettingsRepositoryInterface */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $csrf = ($session && method_exists($session, 'token')) ? $session->token() : '';

        $title = (string) ($this->settings->get('forum_title') ?: 'Forum');

        $rightHtml = '还没帐户？ <a href="/register">注册</a>';

        $page = Chrome::top('登录', '登录', $rightHtml)
            . $this->body()
            . Chrome::bottom()
            . $this->script();

        $html = strtr($page, [
            '__CSRF__'  => htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'),
            '__TITLE__' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            '__NAV1_URL__' => '/t/Technology',
            '__NAV2_URL__' => '/t/Resources',
        ]);

        return new HtmlResponse($html);
    }

    protected function body(): string
    {
        return <<<'HTML'
  <form id="kv-form" autocomplete="off">

    <div class="kv-box">
      <div class="kv-box-h">请在下方输入您的用户名和密码</div>
      <div class="kv-box-b">
        <div class="kv-field">
          <label for="kv-ident">用户名<span class="req">*</span></label>
          <input id="kv-ident" name="identification" type="text" required>
          <div class="kv-fieldhint">也可使用注册时的电子邮件地址登录。</div>
        </div>
        <div class="kv-field">
          <label for="kv-password">密码<span class="req">*</span></label>
          <input id="kv-password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <label class="kv-check">
          <input id="kv-remember" type="checkbox" checked>
          <span>每次访问时自动登录。</span>
        </label>
      </div>
    </div>

    <div class="kv-actions">
      <button id="kv-submit" class="kv-btn" type="submit">登录</button>
      <a href="#" id="kv-forgot" class="kv-link">忘记密码了？</a>
    </div>

  </form>
HTML;
    }

    protected function script(): string
    {
        return <<<'HTML'

<script>
(function(){
  var CSRF = "__CSRF__";
  var form = document.getElementById('kv-form');
  var msg = document.getElementById('kv-msg');
  var submit = document.getElementById('kv-submit');
  var forgot = document.getElementById('kv-forgot');

  function showErr(text){ msg.className = 'kv-msg err'; msg.innerHTML = text; window.scrollTo(0,0); }
  function showOk(text){ msg.className = 'kv-msg ok'; msg.innerHTML = text; window.scrollTo(0,0); }

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    msg.className = 'kv-msg';

    var identification = document.getElementById('kv-ident').value.trim();
    var password = document.getElementById('kv-password').value;
    var remember = document.getElementById('kv-remember').checked;

    if(!identification || !password){ showErr('请输入用户名和密码。'); return; }

    submit.disabled = true; submit.textContent = '登录中…';
    try {
      var res = await fetch('/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-CSRF-Token': CSRF },
        body: JSON.stringify({ identification: identification, password: password, remember: remember })
      });
      if (res.ok) { window.location = '/'; return; }

      var text = '用户名或密码错误。';
      if (res.status === 401) { text = '用户名或密码错误。'; }
      else {
        var data = null; try { data = await res.json(); } catch(e){}
        if (data && data.errors && data.errors.length) {
          text = data.errors.map(function(x){ return (x.detail || x.title || '登录失败'); }).join('<br>');
        }
      }
      showErr(text);
      submit.disabled = false; submit.textContent = '登录';
    } catch (err) {
      showErr('网络错误，请稍后重试。');
      submit.disabled = false; submit.textContent = '登录';
    }
  });

  // 忘记密码：输入邮箱 -> 调用 Flarum 找回密码接口
  forgot.addEventListener('click', async function(e){
    e.preventDefault();
    var email = prompt('请输入你注册时的电子邮件地址，我们会发送重置密码的邮件：');
    if(!email){ return; }
    try {
      var res = await fetch('/api/forgot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-CSRF-Token': CSRF },
        body: JSON.stringify({ email: email })
      });
      if (res.ok) { showOk('如果该邮箱已注册，重置密码邮件已发送，请查收。'); }
      else { showErr('发送失败，请确认邮箱是否正确。'); }
    } catch (err) { showErr('网络错误，请稍后重试。'); }
  });
})();
</script>
</body>
</html>
HTML;
    }
}
