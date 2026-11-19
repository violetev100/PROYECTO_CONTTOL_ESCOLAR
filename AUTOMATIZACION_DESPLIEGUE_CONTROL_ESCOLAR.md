# Automatización de despliegue — Control Escolar

> Guía operativa para clonar, configurar, construir, iniciar, validar y mantener el proyecto mediante Docker Compose.

**Repositorio:** https://github.com/violetev100/PROYECTO_CONTTOL_ESCOLAR  
**Rama consultada:** `master`  
**Arquitectura observada:** PHP 8.3 + Apache, MariaDB 10.11 y Docker Compose.

---

## 1. Objetivo

Estandarizar el despliegue del sistema Control Escolar en un equipo Linux con Docker Engine y Docker Compose. El procedimiento utiliza los archivos existentes en la raíz del repositorio:

- `Dockerfile`: construye la imagen web basada en `php:8.3.36-apache` e instala `pdo_mysql`.
- `docker-compose.yml`: define los servicios `web` y `db`, su red interna y el volumen persistente de MariaDB.
- `database/01_control_escolar.sql`: script de inicialización de la base de datos.
- `app/`: código de la aplicación PHP.

La base de datos se publica únicamente en `127.0.0.1` mediante el puerto configurado; la aplicación web se publica mediante `WEB_PORT`.

## 2. Requisitos previos

En el servidor o equipo de despliegue:

- Linux (esta guía usa comandos compatibles con Debian/Ubuntu).
- Git.
- Docker Engine.
- Complemento Docker Compose v2 (`docker compose`).
- Acceso al repositorio.
- Puertos disponibles: `WEB_PORT` y `DB_HOST_PORT`.

Comprueba las herramientas:

```bash
git --version
docker --version
docker compose version
```

Si `docker compose version` no funciona, instala el complemento Compose v2 desde la documentación oficial de Docker para tu distribución.

## 3. Clonar el repositorio

```bash
git clone https://github.com/violetev100/PROYECTO_CONTTOL_ESCOLAR.git
cd PROYECTO_CONTTOL_ESCOLAR
```

Comprueba que estás en la raíz del proyecto:

```bash
pwd
ls -la
```

Deberías encontrar, entre otros, `Dockerfile`, `docker-compose.yml`, `app/` y `database/`.

## 4. Configurar las variables de entorno

Docker Compose necesita un archivo llamado exactamente `.env` en la raíz del proyecto. Revisa primero qué plantilla existe en la versión del repositorio:

```bash
ls -la .env*
```

Si el repositorio incluye `_.env.example`, cópialo con el nombre que Compose espera:

```bash
cp _.env.example .env
```

Si la plantilla no existe, crea `.env` tomando como referencia las variables utilizadas por `docker-compose.yml`:

```dotenv
DB_ROOT_PASS=CambiaEstaClaveRoot
DB_NAME=control_escolar
DB_USER=escolar_user
DB_PASS=CambiaEstaClaveDeAplicacion
DB_HOST_PORT=3306
WEB_PORT=8080
```

**Importante:** el bloque anterior es una configuración de ejemplo para las variables que consume el Compose; verifica los nombres y valores de la plantilla real antes de usarlo. Sustituye las contraseñas por valores fuertes y únicos. No uses estas claves de ejemplo en producción.

Restringe los permisos del archivo:

```bash
chmod 600 .env
```

No subas `.env` al repositorio. Debe estar excluido por `.gitignore`. Verifica:

```bash
git check-ignore .env
```

Si el comando no devuelve `.env`, revisa `.gitignore` y agrega esta entrada:

```gitignore
.env
```

No publiques credenciales, volcados de base de datos ni archivos de configuración con secretos.

## 5. Validar la configuración antes del despliegue

Comprueba que Compose puede interpretar los archivos y resolver las variables:

```bash
docker compose config --quiet
```

Si aparecen errores de variables no definidas, revisa que `.env` esté en la raíz y que contenga todos los nombres referenciados en `docker-compose.yml`.

Verifica que exista el SQL de inicialización:

```bash
test -f database/01_control_escolar.sql \
  && echo "SQL de inicialización encontrado" \
  || echo "ERROR: falta database/01_control_escolar.sql"
```

## 6. Construir e iniciar los servicios

Construye la imagen web y levanta los contenedores en segundo plano:

```bash
docker compose up -d --build
```

El servicio web depende de que MariaDB supere su `healthcheck`; Compose espera esa condición antes de iniciar la aplicación.

Consulta el estado:

```bash
docker compose ps
```

Consulta los registros:

```bash
docker compose logs -f
```

Para salir de la visualización de logs, presiona `Ctrl+C`. Esto no detiene los contenedores.

## 7. Validar el funcionamiento

### 7.1 Estado de los contenedores

```bash
docker compose ps
```

Los servicios `web` y `db` deben permanecer en estado `Up` (y la base de datos debe aparecer saludable cuando Compose muestre el estado de healthcheck).

### 7.2 Revisar los registros por servicio

```bash
docker compose logs --tail=100 db
docker compose logs --tail=100 web
```

Busca errores de inicialización de MariaDB, conexión PDO, Apache o PHP.

### 7.3 Probar la respuesta HTTP

Si `WEB_PORT` está configurado como `8080`:

```bash
curl -I http://localhost:8080
```

También puedes abrir en el navegador:

```text
http://IP_DEL_SERVIDOR:8080
```

Sustituye el puerto por el valor real de `WEB_PORT`. Si el acceso será desde otra máquina, verifica el firewall y que el puerto publicado esté permitido.

### 7.4 Comprobar la base de datos

```bash
docker compose exec db mariadb \
  -u"$DB_USER" -p"$DB_PASS" "$DB_NAME"
```

El comando anterior requiere que las variables estén exportadas en la terminal. Una alternativa es abrir el cliente y proporcionar las credenciales manualmente:

```bash
docker compose exec db mariadb -u escolar_user -p control_escolar
```

Después de introducir la contraseña, puedes ejecutar:

```sql
SHOW TABLES;
EXIT;
```

Usa el usuario y la base de datos definidos en tu `.env`.

## 8. Operaciones habituales

| Operación | Comando |
|---|---|
| Iniciar servicios | `docker compose up -d` |
| Reconstruir e iniciar | `docker compose up -d --build` |
| Detener servicios | `docker compose stop` |
| Iniciar servicios detenidos | `docker compose start` |
| Reiniciar servicios | `docker compose restart` |
| Ver estado | `docker compose ps` |
| Ver registros | `docker compose logs -f` |
| Ver registros de web | `docker compose logs -f web` |
| Ver registros de base de datos | `docker compose logs -f db` |
| Detener y eliminar contenedores/red | `docker compose down` |

Para actualizar el código desde Git:

```bash
git pull
docker compose up -d --build
docker compose ps
```

El servicio web monta `./app` en `/var/www/html`, por lo que los cambios de la aplicación pueden reflejarse directamente en el contenedor. Los cambios en el `Dockerfile` requieren reconstrucción de la imagen.

## 9. Persistencia y reinicialización de la base de datos

MariaDB utiliza el volumen nombrado `dbdata`. Esto permite conservar los datos aunque se eliminen los contenedores.

El archivo `database/01_control_escolar.sql` está montado en el directorio de inicialización de MariaDB. **Los scripts de inicialización se ejecutan cuando el directorio de datos está vacío**, no cada vez que se reinicia el contenedor.

Por tanto:

- `docker compose restart` no vuelve a importar el SQL.
- `docker compose down` conserva el volumen.
- `docker compose down -v` elimina el volumen y sus datos. Es una operación destructiva.

No ejecutes `docker compose down -v` en un entorno con información que debas conservar.

## 10. Respaldo y restauración

### 10.1 Crear un respaldo

Ejecuta desde la raíz del proyecto. El comando solicita la contraseña del usuario de base de datos:

```bash
mkdir -p backups
docker compose exec -T db mariadb-dump \
  -u escolar_user -p control_escolar \
  > "backups/control_escolar_$(date +%F_%H%M%S).sql"
```

Sustituye `escolar_user` y `control_escolar` por los valores reales de `DB_USER` y `DB_NAME`. Al usar `-p` sin contraseña en el argumento, el cliente puede solicitarla de forma interactiva; si tu versión de cliente no permite la interacción con `-T`, ejecuta el respaldo desde una terminal interactiva o utiliza un método seguro de gestión de secretos. No escribas contraseñas directamente en el historial de shell.

Verifica que el archivo no esté vacío:

```bash
ls -lh backups/
```

### 10.2 Restaurar un respaldo

La restauración sobrescribe o modifica los datos de la base de destino. Haz primero un respaldo del estado actual y confirma que seleccionaste el archivo correcto.

```bash
docker compose exec -T db mariadb \
  -u escolar_user -p control_escolar \
  < backups/ARCHIVO_RESPALDO.sql
```

Introduce las credenciales de forma segura. Si el cliente no puede solicitar la contraseña en esta modalidad no interactiva, utiliza un archivo temporal de opciones protegido con permisos `600` y elimínalo al terminar; no incluyas secretos en el comando ni en el repositorio.

## 11. Solución de problemas

### Los servicios no arrancan

```bash
docker compose ps
docker compose logs --tail=200
docker compose config
```

Revisa variables faltantes, errores de sintaxis, permisos y disponibilidad de puertos.

### El puerto ya está ocupado

Comprueba qué proceso utiliza el puerto configurado:

```bash
sudo ss -ltnp
```

Cambia `WEB_PORT` o `DB_HOST_PORT` en `.env` y vuelve a aplicar la configuración:

```bash
docker compose up -d
```

### La aplicación no conecta con MariaDB

Comprueba:

1. Que el servicio `db` esté saludable.
2. Que `DB_HOST` en el servicio web sea `db` (nombre del servicio de Compose).
3. Que `DB_NAME`, `DB_USER` y `DB_PASSWORD` coincidan con las credenciales iniciales de MariaDB.
4. Los registros de ambos servicios:

```bash
docker compose logs --tail=150 db web
```

Si cambiaste las credenciales después de que el volumen ya había sido inicializado, modificar `.env` no cambia automáticamente los usuarios o contraseñas almacenados en MariaDB. Actualiza las credenciales dentro de la base de datos de forma controlada.

### No aparecen las tablas esperadas

El SQL de inicialización solo se ejecuta con un volumen de datos vacío. Comprueba las tablas desde MariaDB y revisa los logs. No elimines el volumen para “probar” sin tener un respaldo y autorización: perderías los datos persistidos.

### Cambios PHP no aparecen

Verifica que el archivo esté dentro de `app/` y que el montaje `./app:/var/www/html` esté activo:

```bash
docker compose exec web ls -la /var/www/html
```

Si modificaste dependencias o configuración de la imagen, reconstruye:

```bash
docker compose up -d --build web
```

## 12. Procedimiento de despliegue resumido

Para una instalación nueva, una vez instalado Docker:

```bash
git clone https://github.com/violetev100/PROYECTO_CONTTOL_ESCOLAR.git
cd PROYECTO_CONTTOL_ESCOLAR
cp _.env.example .env
nano .env
chmod 600 .env
docker compose config --quiet
docker compose up -d --build
docker compose ps
docker compose logs --tail=100
```

Si el repositorio no contiene `_.env.example`, crea `.env` manualmente con las variables requeridas por `docker-compose.yml`.

## 13. Consideraciones antes de producción

La configuración observada es adecuada como base de despliegue local o de laboratorio, pero antes de exponer el sistema a Internet se recomienda:

- Configurar HTTPS mediante un proxy inverso y certificados válidos.
- No publicar el puerto de MariaDB en interfaces externas.
- Usar secretos robustos y almacenarlos fuera del repositorio.
- Mantener Docker, imágenes base y dependencias actualizadas.
- Definir respaldos automáticos y probar periódicamente su restauración.
- Limitar el acceso administrativo al servidor.
- Revisar los permisos de archivos y directorios de la aplicación.
- Incorporar monitoreo, rotación de logs y una política de actualizaciones.
- Probar el procedimiento en un entorno de staging antes de actualizar producción.

---

## Referencias

- Repositorio: https://github.com/violetev100/PROYECTO_CONTTOL_ESCOLAR
- Docker Compose: https://docs.docker.com/compose/
- Docker Engine en Linux: https://docs.docker.com/engine/install/
- Imagen oficial PHP: https://hub.docker.com/_/php
- Imagen oficial MariaDB: https://hub.docker.com/_/mariadb

**Nota de alcance:** esta guía se elaboró a partir de la estructura visible del repositorio y de su `Dockerfile` y `docker-compose.yml`. Antes de utilizarla como procedimiento productivo, confirma los valores exactos de `_.env.example`, la lógica de conexión de la aplicación y el esquema SQL del proyecto.
