<?php require 'lib.php'; crud([
 'title'=>'Maestros','table'=>'maestros','self'=>'maestros.php',
 'list_sql'=>"SELECT m.id,CONCAT(m.apellidos,' ',m.nombre) maestro,m.email,u.usuario FROM maestros m LEFT JOIN usuarios u ON u.id=m.usuario_id ORDER BY m.apellidos",
 'columns'=>['maestro'=>'Maestro','email'=>'Correo','usuario'=>'Usuario de acceso'],
 'fields'=>[
  ['name'=>'nombre','label'=>'Nombre(s)','type'=>'text','req'=>1],
  ['name'=>'apellidos','label'=>'Apellidos','type'=>'text','req'=>1],
  ['name'=>'email','label'=>'Correo','type'=>'email'],
  ['name'=>'telefono','label'=>'Teléfono','type'=>'text'],
  ['name'=>'usuario_id','label'=>'Usuario de acceso (créalo antes en Usuarios con rol maestro)','type'=>'select','sql'=>"SELECT id,CONCAT(nombre,' (',usuario,')') FROM usuarios WHERE rol='maestro' ORDER BY nombre"],
 ]]);
