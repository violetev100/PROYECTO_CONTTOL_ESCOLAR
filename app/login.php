<?php require 'lib.php';
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $s=db()->prepare('SELECT * FROM usuarios WHERE usuario=? AND activo=1'); $s->execute([trim($_POST['usuario']??'')]); $r=$s->fetch();
  if($r && password_verify($_POST['password']??'',$r['password_hash'])){
    session_regenerate_id(true);
    $_SESSION['user']=['id'=>$r['id'],'nombre'=>$r['nombre'],'rol'=>$r['rol']];
    redirect('index.php');
  }
  $err='Usuario o contraseña incorrectos';
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ingresar</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="login"><form method="post"><h1>Control Escolar</h1>
<?php if($err) echo '<p class="flash err">'.h($err).'</p>'; ?>
<input type="hidden" name="csrf" value="<?= csrf() ?>">
<label>Usuario<br><input name="usuario" required autofocus></label>
<label>Contraseña<br><input type="password" name="password" required></label>
<button class="btn">Entrar</button></form></body></html>
