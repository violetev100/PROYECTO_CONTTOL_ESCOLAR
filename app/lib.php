<?php
// Control Escolar | PHP 8.3.36 | MariaDB, puerto 3306
// Local (XAMPP): usa los valores por defecto de abajo (root, sin contraseña, 127.0.0.1).
// Docker: DB_HOST, DB_NAME, DB_USER y DB_PASSWORD vienen de docker-compose.yml y tienen prioridad.
session_start();
function db(): PDO {
  static $pdo;
  $host = getenv('DB_HOST') ?: '127.0.0.1';
  $name = getenv('DB_NAME') ?: 'control_escolar';
  $user = getenv('DB_USER') ?: 'root';
  $pass = getenv('DB_PASSWORD');
  if ($pass === false) $pass = '';
  return $pdo ??= new PDO("mysql:host=$host;port=3306;dbname=$name;charset=utf8mb4",
    $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($u){ header("Location: $u"); exit; }
function flash($m,$t='ok'){ $_SESSION['flash']=[$m,$t]; }
function csrf(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_check(){ if(!hash_equals(csrf(), $_POST['csrf']??'')){ http_response_code(400); exit('Token inválido'); } }
function user(){ return $_SESSION['user'] ?? null; }
function require_role(string ...$roles){
  if(!user()) redirect('login.php');
  if($roles && !in_array(user()['rol'],$roles,true)){ http_response_code(403); exit('Sin permiso'); }
}
function page_start($title){
  $u=user();
  $links = $u['rol']==='admin'
    ? ['index.php'=>'Inicio','alumnos.php'=>'Alumnos','maestros.php'=>'Maestros','grupos.php'=>'Grupos','materias.php'=>'Materias','asignaciones.php'=>'Asignaciones','calificaciones.php'=>'Calificaciones','usuarios.php'=>'Usuarios']
    : ['index.php'=>'Inicio','calificaciones.php'=>'Calificaciones'];
  echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' - Control Escolar</title><link rel="stylesheet" href="assets/style.css"></head><body><nav><b>Control Escolar</b>';
  foreach($links as $k=>$v) echo '<a href="'.$k.'">'.$v.'</a>';
  echo '<span class="sp"></span><span>'.h($u['nombre']).' ('.$u['rol'].')</span><a href="logout.php">Salir</a></nav><main><h1>'.h($title).'</h1>';
  if(!empty($_SESSION['flash'])){ [$m,$t]=$_SESSION['flash']; unset($_SESSION['flash']); echo '<p class="flash '.$t.'">'.h($m).'</p>'; }
}
function page_end(){ echo '</main></body></html>'; }

// CRUD genérico para las pantallas de administración
function crud(array $c){
  require_role('admin');
  $pdo=db(); $act=$_GET['a']??'list'; $id=(int)($_GET['id']??0); $err='';
  if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    if(($_POST['op']??'')==='delete'){
      $did=(int)$_POST['id'];
      if($c['table']==='usuarios' && $did===user()['id']) flash('No puedes eliminar tu propio usuario','err');
      else try{ $pdo->prepare("DELETE FROM `{$c['table']}` WHERE id=?")->execute([$did]); flash('Registro eliminado'); }
      catch(PDOException $e){ flash('No se puede eliminar: tiene registros relacionados','err'); }
      redirect($c['self']);
    }
    $cols=[]; $vals=[];
    foreach($c['fields'] as $f){
      $v=trim($_POST[$f['name']]??'');
      if($f['type']==='password'){
        if($v===''){ if($id) continue; if(!empty($f['req'])){ $err="Falta: {$f['label']}"; break; } }
        else $v=password_hash($v,PASSWORD_DEFAULT);
      } elseif($v===''){
        if(!empty($f['req'])){ $err="Falta: {$f['label']}"; break; }
        $v=null;
      }
      $cols[]=$f['name']; $vals[]=$v;
    }
    if(!$err){
      try{
        if($id){ $pdo->prepare("UPDATE `{$c['table']}` SET ".implode(',',array_map(fn($k)=>"`$k`=?",$cols))." WHERE id=?")->execute([...$vals,$id]); }
        else { $pdo->prepare("INSERT INTO `{$c['table']}` (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute($vals); }
        flash('Guardado correctamente'); redirect($c['self']);
      } catch(PDOException $e){ $err = $e->getCode()=='23000' ? 'Dato duplicado o inválido' : 'Error al guardar'; }
    }
  }
  page_start($c['title']);
  if($act==='new' || $act==='edit'){
    $row=$_POST ?: [];
    if($id && !$_POST){ $s=$pdo->prepare("SELECT * FROM `{$c['table']}` WHERE id=?"); $s->execute([$id]); $row=$s->fetch() ?: []; }
    if($err) echo '<p class="flash err">'.h($err).'</p>';
    echo '<form method="post"><input type="hidden" name="csrf" value="'.csrf().'">';
    foreach($c['fields'] as $f){
      $n=$f['name']; $t=$f['type']; $v=$t==='password' ? '' : ($row[$n] ?? ($f['default'] ?? ''));
      echo '<label>'.h($f['label']).(!empty($f['req'])?' *':'').'<br>';
      if($t==='select'){
        $opts=$f['options'] ?? $pdo->query($f['sql'])->fetchAll(PDO::FETCH_KEY_PAIR);
        echo '<select name="'.$n.'"><option value="">--</option>';
        foreach($opts as $k=>$l) echo '<option value="'.h($k).'"'.((string)$k===(string)$v?' selected':'').'>'.h($l).'</option>';
        echo '</select>';
      } else {
        $req = !empty($f['req']) && !($t==='password' && $id);
        echo '<input type="'.$t.'" name="'.$n.'" value="'.h($v).'"'.($req?' required':'').($t==='password'?' autocomplete="new-password"':'').'>';
      }
      echo '</label>';
    }
    echo '<button class="btn">Guardar</button> <a href="'.$c['self'].'">Cancelar</a></form>';
  } else {
    echo '<p><a class="btn" href="'.$c['self'].'?a=new">+ Nuevo</a></p><table><tr>';
    foreach($c['columns'] as $l) echo '<th>'.h($l).'</th>';
    echo '<th></th></tr>';
    foreach($pdo->query($c['list_sql'])->fetchAll() as $r){
      echo '<tr>'; foreach($c['columns'] as $k=>$l) echo '<td>'.h($r[$k]).'</td>';
      echo '<td><a href="'.$c['self'].'?a=edit&id='.$r['id'].'">Editar</a> <form method="post" style="display:inline" onsubmit="return confirm(\'¿Eliminar este registro?\')"><input type="hidden" name="csrf" value="'.csrf().'"><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="'.$r['id'].'"><button class="link">Eliminar</button></form></td></tr>';
    }
    echo '</table>';
  }
  page_end();
}
