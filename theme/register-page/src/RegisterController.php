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
        $session = $request->getAttribute('session');
        $csrf = ($session && method_exists($session, 'token')) ? $session->token() : '';

        $title = (string) ($this->settings->get('forum_title') ?: 'Forum');

        // 论坛规则文字：优先读取扩展目录下的 rules.html（方便随时改）；读不到则用默认。
        $rulesFile = dirname(__DIR__) . '/rules.html';
        if (is_file($rulesFile)) {
            $rules = (string) file_get_contents($rulesFile);
        } else {
            $rules = "本论坛仅限 Arch Linux x86_64 用户。<br><br>"
                . "不适用于 Artix、Apricity、Manjaro 或任何“简易 Arch 安装程序”，也不适用于 Arch-ARM；仅限纯净的 64 位 Arch Linux。如需帮助，请联系相应的社区。<br><br>"
                . "注册本论坛即表示您同意本站隐私政策，并且您在论坛上发布的任何信息都将被视为“公共信息”。";
        }

        $rightHtml = '已有帐户？ <a href="/login">登录</a>';

        $page = Chrome::top('注册', '注册', $rightHtml)
            . $this->body()
            . Chrome::bottom()
            . $this->script();

        $html = strtr($page, [
            '__CSRF__'  => htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'),
            '__TITLE__' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            '__RULES__' => $rules,
            // 顶部导航两个标签链接——如与实际不符，改这里的 URL 即可
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
        <label class="kv-check">
          <input id="kv-agree" type="checkbox">
          <span>我已阅读并同意以上论坛规则与隐私政策。</span>
        </label>
      </div>
    </div>

    <div class="kv-actions">
      <button id="kv-submit" class="kv-btn" type="submit" disabled>注册</button>
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
        try {
          var login = await fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-CSRF-Token': CSRF },
            body: JSON.stringify({ identification: username, password: password, remember: true })
          });
          if (login.ok) { window.location = '/'; return; }
        } catch (e) {}
        showOk('注册成功！如需邮箱验证请查收邮件；完成后即可 <a href="/login">登录</a>。');
        submit.textContent = '注册成功';
        return;
      }

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
