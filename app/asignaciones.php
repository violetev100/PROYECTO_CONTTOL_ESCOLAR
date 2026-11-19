<?php require 'lib.php'; crud([
 'title'=>'Asignaciones (quién imparte qué)','table'=>'asignaciones','self'=>'asignaciones.php',
 'list_sql'=>"SELECT a.id,g.nombre grupo,m.nombre materia,CONCAT(t.apellidos,' ',t.nombre) maestro FROM asignaciones a JOIN grupos g ON g.id=a.grupo_id JOIN materias m ON m.id=a.materia_id JOIN maestros t ON t.id=a.maestro_id ORDER BY g.nombre,m.nombre",
 'columns'=>['grupo'=>'Grupo','materia'=>'Materia','maestro'=>'Maestro'],
 'fields'=>[
  ['name'=>'grupo_id','label'=>'Grupo','type'=>'select','sql'=>'SELECT id,nombre FROM grupos ORDER BY nombre','req'=>1],
  ['name'=>'materia_id','label'=>'Materia','type'=>'select','sql'=>'SELECT id,nombre FROM materias ORDER BY nombre','req'=>1],
  ['name'=>'maestro_id','label'=>'Maestro','type'=>'select','sql'=>"SELECT id,CONCAT(apellidos,' ',nombre) FROM maestros ORDER BY apellidos",'req'=>1],
 ]]);
