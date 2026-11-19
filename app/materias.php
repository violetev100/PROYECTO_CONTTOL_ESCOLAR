<?php require 'lib.php'; crud([
 'title'=>'Materias','table'=>'materias','self'=>'materias.php',
 'list_sql'=>'SELECT id,clave,nombre FROM materias ORDER BY nombre',
 'columns'=>['clave'=>'Clave','nombre'=>'Materia'],
 'fields'=>[
  ['name'=>'clave','label'=>'Clave','type'=>'text','req'=>1],
  ['name'=>'nombre','label'=>'Nombre de la materia','type'=>'text','req'=>1],
 ]]);
