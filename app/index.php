<?php require 'lib.php'; require_role(); $pdo=db(); $u=user();
page_start('Inicio');
echo '<p>Bienvenido, <b>'.h($u['nombre']).'</b>.</p>';
if($u['rol']==='admin'){
  echo '<div class="cards">';
  foreach(['alumnos'=>'Alumnos','maestros'=>'Maestros','grupos'=>'Grupos','materias'=>'Materias'] as $t=>$l)
    echo '<a class="card" href="'.$t.'.php"><b>'.$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn().'</b><br>'.$l.'</a>';
  echo '</div><p class="muted">PHP '.phpversion().' · MariaDB '.h($pdo->query('SELECT VERSION()')->fetchColumn()).'</p>';
} else {
  $s=$pdo->prepare('SELECT COUNT(*) FROM asignaciones a JOIN maestros m ON m.id=a.maestro_id WHERE m.usuario_id=?'); $s->execute([$u['id']]);
  echo '<p>Tienes <b>'.$s->fetchColumn().'</b> grupo(s)-materia(s) asignados. <a href="calificaciones.php">Capturar calificaciones</a></p>';
}
page_end();
