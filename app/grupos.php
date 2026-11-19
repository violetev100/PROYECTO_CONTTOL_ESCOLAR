<?php require 'lib.php'; crud([
 'title'=>'Grupos','table'=>'grupos','self'=>'grupos.php',
 'list_sql'=>'SELECT id,nombre,grado,ciclo FROM grupos ORDER BY nombre',
 'columns'=>['nombre'=>'Grupo','grado'=>'Grado','ciclo'=>'Ciclo'],
 'fields'=>[
  ['name'=>'nombre','label'=>'Nombre del grupo','type'=>'text','req'=>1],
  ['name'=>'grado','label'=>'Grado','type'=>'text'],
  ['name'=>'ciclo','label'=>'Ciclo escolar (ej. 2026-2027)','type'=>'text'],
 ]]);
