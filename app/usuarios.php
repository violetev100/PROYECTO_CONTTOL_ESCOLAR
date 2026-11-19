<?php require 'lib.php'; crud([
 'title'=>'Usuarios','table'=>'usuarios','self'=>'usuarios.php',
 'list_sql'=>"SELECT id,usuario,nombre,rol,IF(activo,'Sí','No') activo FROM usuarios ORDER BY usuario",
 'columns'=>['usuario'=>'Usuario','nombre'=>'Nombre','rol'=>'Rol','activo'=>'Activo'],
 'fields'=>[
  ['name'=>'usuario','label'=>'Usuario','type'=>'text','req'=>1],
  ['name'=>'nombre','label'=>'Nombre completo','type'=>'text','req'=>1],
  ['name'=>'password_hash','label'=>'Contraseña (en edición, vacío = no cambiar)','type'=>'password','req'=>1],
  ['name'=>'rol','label'=>'Rol','type'=>'select','options'=>['admin'=>'admin','maestro'=>'maestro'],'req'=>1,'default'=>'maestro'],
  ['name'=>'activo','label'=>'Activo','type'=>'select','options'=>['1'=>'Sí','0'=>'No'],'req'=>1,'default'=>'1'],
 ]]);
