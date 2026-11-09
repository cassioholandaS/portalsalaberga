<?php
$mensagens = [
    'campos_vazios' => ['error', 'Preencha todos os campos obrigatórios.'],
    '2' => ['error', 'Usuário não encontrado.'],
    '3' => ['error', 'E-mail ou senha inválidos.'],
    '5' => ['error', 'Este usuário está desativado.'],
    '6' => ['warning', 'Seu cadastro aguarda aprovação do master.'],
    '7' => ['error', 'Seu cadastro foi recusado.'],
    'erro_servidor' => ['error', 'Não foi possível concluir a operação. Tente novamente.'],
    'usuario_ja_existe' => ['error', 'Já existe um cadastro com este e-mail.'],
    'cpf_invalido' => ['error', 'Informe um CPF válido com 11 dígitos.'],
    'telefone_invalido' => ['error', 'Informe um telefone válido com DDD.'],
    'perfil_invalido' => ['error', 'Perfil inválido. Procure o master.'],
];
$chaveMensagem = $_GET['sucesso'] ?? $_GET['erro'] ?? '';
$mensagem = $mensagens[$chaveMensagem] ?? null;
if ($_GET['sucesso'] ?? '' === 'primeiro_acesso') {
  $mensagem = ['success', 'Cadastro realizado com sucesso! Sua solicitação foi enviada para análise. Aguarde a aprovação de um administrador para realizar o primeiro acesso.'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Sistema PCD</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Comfortaa:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root { --forest:#2d5016; --green:#7fb069; --sage:#a4c3a2; --cream:#f7f3e9; --peach:#f4a261; --coral:#e76f51; --sky:#e8f4f8; }
    * { box-sizing:border-box; }
    body { margin:0; min-height:100vh; background:linear-gradient(135deg,var(--cream),var(--sky)); font-family:'Nunito',sans-serif; }
    .auth-shell { min-height:100vh; width:min(1180px,100%); margin:auto; display:grid; grid-template-columns:1fr 1fr; overflow:hidden; background:var(--cream); box-shadow:0 10px 35px rgba(45,80,22,.18); }
    .scene { position:relative; display:flex; align-items:center; justify-content:center; padding:48px; overflow:hidden; color:#fff; background:linear-gradient(135deg,#2d5016,#7fb069); }
    .scene:before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(45,80,22,.8),rgba(231,111,81,.55)); }
    .scene-content { position:relative; z-index:1; text-align:center; max-width:430px; }
    .scene-content i { color:var(--peach); font-size:78px; margin-bottom:24px; }
    .scene h1 { font:700 clamp(32px,4vw,54px) 'Comfortaa',sans-serif; margin:0 0 20px; }
    .scene h1 span { color:var(--peach); }
    .scene p { font-size:18px; line-height:1.6; opacity:.9; }
    .form-side { display:flex; align-items:center; padding:clamp(28px,5vw,70px); overflow:auto; }
    .form-content { width:100%; max-width:450px; margin:auto; }
    .logo { width:76px; height:76px; border-radius:20px; display:grid; place-items:center; margin:0 auto 14px; background:rgba(164,195,162,.25); color:var(--forest); font-size:34px; }
    .heading { text-align:center; margin-bottom:24px; }
    .heading h2 { color:var(--forest); font:700 32px 'Comfortaa',sans-serif; margin:0 0 7px; }
    .heading p { color:rgba(45,80,22,.7); margin:0; }
    .tabs { display:flex; background:rgba(164,195,162,.2); border-radius:12px; padding:4px; margin-bottom:22px; }
    .tab { flex:1; border:0; background:transparent; color:rgba(45,80,22,.6); padding:11px 8px; border-radius:9px; cursor:pointer; font:600 13px 'Nunito',sans-serif; }
    .tab.active { color:var(--forest); background:#fff; box-shadow:0 5px 15px rgba(164,195,162,.25); }
    .alert { border-radius:10px; padding:11px 14px; margin-bottom:16px; font-size:13px; }
    .alert.error { color:#8d2e20; background:#fff0ec; border:1px solid #f2c1b6; }
    .alert.warning { color:#795b12; background:#fff8df; border:1px solid #efd98d; }
    .alert.success { color:#266b2c; background:#edf8ed; border:1px solid #b9ddb9; }
    .panel { display:none; gap:15px; }
    .panel.active { display:grid; }
    .field { position:relative; }
    .field input, .field select { width:100%; border:2px solid rgba(127,176,105,.3); border-radius:11px; padding:14px 15px 14px 43px; color:var(--forest); background:rgba(255,255,255,.8); font:600 14px 'Nunito',sans-serif; outline:none; }
    .field input:focus, .field select:focus { border-color:var(--peach); box-shadow:0 8px 22px rgba(244,162,97,.2); }
    .field i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--green); z-index:1; }
    .submit { border:0; border-radius:11px; padding:14px; color:#fff; cursor:pointer; background:linear-gradient(135deg,var(--green),var(--sage)); font:700 14px 'Comfortaa',sans-serif; box-shadow:0 8px 20px rgba(127,176,105,.3); }
    .submit:hover { background:linear-gradient(135deg,var(--forest),var(--green)); }
    .leaf { position:absolute; width:22px; height:22px; border-radius:0 100% 0 100%; background:rgba(255,255,255,.22); transform:rotate(25deg); }
    @media (max-width:800px) { .auth-shell { display:block; } .scene { min-height:225px; padding:28px; } .scene-content i { font-size:42px; margin-bottom:10px; } .scene h1 { font-size:30px; margin-bottom:8px; } .scene p { font-size:14px; margin:0; } .form-side { min-height:calc(100vh - 225px); padding:28px 20px; } }
  </style>
</head>
<body>
  <main class="auth-shell">
    <section class="scene">
      <span class="leaf" style="top:12%;left:15%"></span><span class="leaf" style="top:70%;right:13%;transform:rotate(120deg)"></span><span class="leaf" style="bottom:14%;left:28%;transform:rotate(70deg)"></span>
      <div class="scene-content"><i class="fas fa-universal-access"></i><h1>Sistema <span>PCD</span></h1><p>Gestão, acompanhamento e inclusão para toda a comunidade escolar.</p></div>
    </section>
    <section class="form-side">
      <div class="form-content">
        <div class="logo"><i class="fas fa-shield-heart"></i></div>
        <div class="heading"><h2>Bem-vindo</h2><p>Acesse sua conta institucional</p></div>
        <div class="tabs"><button type="button" class="tab active" data-panel="login-panel"><i class="fas fa-sign-in-alt"></i> Login</button><button type="button" class="tab" data-panel="access-panel"><i class="fas fa-key"></i> Primeiro acesso</button></div>
        <?php if ($mensagem): ?><div class="alert <?= htmlspecialchars($mensagem[0]) ?>"><?= htmlspecialchars($mensagem[1]) ?></div><?php endif; ?>
        <form id="login-panel" class="panel active" action="controllers/usuario_controller.php?action=login" method="POST">
          <div class="field"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="E-mail" required></div>
          <div class="field"><i class="fas fa-lock"></i><input type="password" name="senha" placeholder="Senha" required></div>
          <button class="submit" type="submit"><i class="fas fa-sign-in-alt"></i> Acessar sistema</button>
        </form>
        <form id="access-panel" class="panel" action="controllers/usuario_controller.php?action=primeiro_acesso" method="POST">
          <div class="field"><i class="fas fa-user"></i><input type="text" name="nome" placeholder="Nome completo" required></div>
          <div class="field"><i class="fas fa-id-card"></i><input type="text" name="cpf" placeholder="CPF (000.000.000-00)" maxlength="14" inputmode="numeric" oninput="mascaraCPF(this)" required></div>
          <div class="field"><i class="fas fa-phone"></i><input type="text" name="telefone" placeholder="Telefone (00) 00000-0000" maxlength="15" inputmode="tel" oninput="mascaraTelefone(this)" required></div>
          <div class="field"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="E-mail" required></div>
          <div class="field"><i class="fas fa-lock"></i><input type="password" name="senha" minlength="6" placeholder="Senha (mínimo de 6 caracteres)" required></div>
          <div class="field"><i class="fas fa-user-shield"></i><select name="tipo" required><option value="">Tipo de acesso</option><option value="aee">AEE</option><option value="apoio">Apoio</option><option value="responsavel">Responsável</option></select></div>
          <button class="submit" type="submit"><i class="fas fa-key"></i> Solicitar primeiro acesso</button>
        </form>
      </div>
    </section>
  </main>
  <script>
    function mascaraCPF(input) {
      input.value = input.value.replace(/\D/g, '').slice(0, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }

    function mascaraTelefone(input) {
      const digits = input.value.replace(/\D/g, '').slice(0, 11);
      input.value = digits.length <= 10
        ? digits.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
        : digits.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
    }

    document.querySelectorAll('.tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        document.querySelectorAll('.tab').forEach(function (item) { item.classList.remove('active'); });
        document.querySelectorAll('.panel').forEach(function (panel) { panel.classList.remove('active'); });
        tab.classList.add('active');
        document.getElementById(tab.dataset.panel).classList.add('active');
      });
    });
  </script>
</body>
</html>
