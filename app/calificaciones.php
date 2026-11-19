<?php require 'lib.php'; require_role('admin','maestro'); $pdo=db(); $u=user();
$q="SELECT a.id,CONCAT(g.nombre,' - ',mt.nombre) etiqueta FROM asignaciones a JOIN grupos g ON g.id=a.grupo_id JOIN materias mt ON mt.id=a.materia_id"; $p=[];
if($u['rol']==='maestro'){ $q.=' JOIN maestros ma ON ma.id=a.maestro_id WHERE ma.usuario_id=?'; $p[]=$u['id']; }
$s=$pdo->prepare($q.' ORDER BY etiqueta'); $s->execute($p); $asigs=$s->fetchAll(PDO::FETCH_KEY_PAIR);
$aid=(int)($_GET['asignacion']??0); if(!isset($asigs[$aid])) $aid=0;
$alumnos=[];
if($aid){
  $s=$pdo->prepare('SELECT al.id,al.matricula,CONCAT(al.apellidos," ",al.nombre) nombre FROM alumnos al JOIN asignaciones a ON a.grupo_id=al.grupo_id WHERE a.id=? AND al.activo=1 ORDER BY al.apellidos,al.nombre');
  $s->execute([$aid]); $alumnos=$s->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
}
if($aid && $_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $ins=$pdo->prepare('INSERT INTO calificaciones(alumno_id,asignacion_id,parcial,calificacion) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE calificacion=VALUES(calificacion)');
  $del=$pdo->prepare('DELETE FROM calificaciones WHERE alumno_id=? AND asignacion_id=? AND parcial=?');
  foreach(($_POST['cal']??[]) as $alu=>$ps) foreach($ps as $par=>$v){
    $alu=(int)$alu; $par=(int)$par; $v=trim($v);
    if(!isset($alumnos[$alu]) || $par<1 || $par>3) continue;
    if($v==='') $del->execute([$alu,$aid,$par]);
    elseif(is_numeric($v) && $v>=0 && $v<=10) $ins->execute([$alu,$aid,$par,$v]);
  }
  flash('Calificaciones guardadas'); redirect("calificaciones.php?asignacion=$aid");
}
page_start('Calificaciones');
echo '<form method="get"><label>Grupo y materia<br><select name="asignacion" onchange="this.form.submit()"><option value="">-- elige --</option>';
foreach($asigs as $k=>$l) echo '<option value="'.$k.'"'.($k==$aid?' selected':'').'>'.h($l).'</option>';
echo '</select></label></form>';
if($aid){
  $cal=[]; $s=$pdo->prepare('SELECT alumno_id,parcial,calificacion FROM calificaciones WHERE asignacion_id=?'); $s->execute([$aid]);
  foreach($s->fetchAll() as $r) $cal[$r['alumno_id']][$r['parcial']]=$r['calificacion'];
  echo '<form method="post"><input type="hidden" name="csrf" value="'.csrf().'"><table><tr><th>Matrícula</th><th>Alumno</th><th>Parcial 1</th><th>Parcial 2</th><th>Parcial 3</th><th>Promedio</th></tr>';
  foreach($alumnos as $id=>$a){
    $v=$cal[$id]??[]; $prom=$v?number_format(array_sum($v)/count($v),1):'-';
    echo '<tr><td>'.h($a['matricula']).'</td><td>'.h($a['nombre']).'</td>';
    for($i=1;$i<=3;$i++) echo '<td><input class="n" type="number" min="0" max="10" step="0.1" name="cal['.$id.']['.$i.']" value="'.h($v[$i]??'').'"></td>';
    echo '<td><b>'.$prom.'</b></td></tr>';
  }
  echo '</table>'.($alumnos?'<button class="btn">Guardar calificaciones</button>':'<p>Este grupo no tiene alumnos activos.</p>').'</form>';
}
page_end();
