<?php require 'lib.php'; crud([
 'title'=>'Alumnos','table'=>'alumnos','self'=>'alumnos.php',
 'list_sql'=>"SELECT a.id,a.matricula,CONCAT(a.apellidos,' ',a.nombre) alumno,g.nombre grupo,IF(a.activo,'Sí','No') activo FROM alumnos a LEFT JOIN grupos g ON g.id=a.grupo_id ORDER BY a.apellidos,a.nombre",
 'columns'=>['matricula'=>'Matrícula','alumno'=>'Alumno','grupo'=>'Grupo','activo'=>'Activo'],
 'fields'=>[
  ['name'=>'matricula','label'=>'Matrícula','type'=>'text','req'=>1],
  ['name'=>'nombre','label'=>'Nombre(s)','type'=>'text','req'=>1],
  ['name'=>'apellidos','label'=>'Apellidos','type'=>'text','req'=>1],
  ['name'=>'fecha_nacimiento','label'=>'Fecha de nacimiento','type'=>'date'],
  ['name'=>'email','label'=>'Correo','type'=>'email'],
  ['name'=>'grupo_id','label'=>'Grupo','type'=>'select','sql'=>'SELECT id,nombre FROM grupos ORDER BY nombre'],
  ['name'=>'activo','label'=>'Activo','type'=>'select','options'=>['1'=>'Sí','0'=>'No'],'req'=>1,'default'=>'1'],
 ]]);
