# Kvin 整页注册扩展（register-page）

给 Flarum 加一个真实的 `/register` **整页注册页**（FluxBB 风格），替代默认的注册弹窗。

- **纯 PHP，无需前端构建**（不用装 node）。
- 页面由服务端渲染；表单通过 `fetch` 调用 Flarum 自带的 `POST /api/users` 完成注册，复用原生校验/发信逻辑，不重写后端。
- 注册成功后尝试自动登录；若开启了邮箱验证则提示去邮箱验证。

---

## 部署步骤（你的 Docker 环境）

前提：`docker-compose.yml` 里已有卷 `- ./theme:/flarum/app/theme`，所以宿主机 `./theme/register-page` 会映射到容器 `/flarum/app/theme/register-page`。

### 1. 把扩展放到宿主机 theme 目录
在你放 `docker-compose.yml` 的目录下，确保有 `./theme/register-page/`（把本仓库 `theme/register-page/` 整个复制过去）。例如：

```bash
# 在宿主机、docker-compose.yml 所在目录
git clone https://github.com/kvintls/Flarum-Customization.git /tmp/kvinfc
mkdir -p ./theme
cp -r /tmp/kvinfc/theme/register-page ./theme/
```

### 2. 在容器里用 composer path 仓库安装（免构建）

```bash
docker exec -u 991:991 -w /flarum/app flarum composer config repositories.register-page path /flarum/app/theme/register-page
docker exec -u 991:991 -w /flarum/app flarum composer require kvin/register-page:"*"
docker exec -u 991:991 -w /flarum/app flarum php flarum extension:enable kvin-register-page
docker exec -u 991:991 -w /flarum/app flarum php flarum cache:clear
```

### 3. 固化镜像快照

```bash
docker commit flarum flarum-local:1.8.19
```

### 4. 验证
- 浏览器访问 `https://kvin.de5.net/register` → 应看到整页注册页。
- 点顶部/archbar 的「注册」也会跳到这里（页头 JS 已改）。

---

## 重要前提 / 排错

- **注册必须是「开放」的**：后台 Administration → 权限/注册设置里，注册方式要是"开放注册"，否则 `/api/users` 会拒绝游客注册。
- **fof/terms**：本页已自带"规则同意"勾选。如果同时装了 `fof/terms` 且设为注册必须同意，`/api/users` 可能因缺少 terms 字段而拒绝。二选一：
  - 关闭 fof/terms 的"注册强制同意"（推荐，规则由本页负责），或
  - 卸载 fof/terms。
- **白屏/500**：看日志 `docker exec -u 991:991 -w /flarum/app flarum tail -n 50 storage/logs/flarum-$(date +%F).log`（或 `docker logs flarum`）。
- 改了本扩展的 PHP 后，需要 `php flarum cache:clear` 再 `docker commit` 重新固化。

---

## 自定义

- **规则文字**：改 `src/RegisterController.php` 里的 `$rules` 变量。
- **页面样式/文案**：都在 `RegisterController::template()` 的内联 HTML/CSS 里，直接改即可（改完清缓存 + 重新 commit 镜像）。
