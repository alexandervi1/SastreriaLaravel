# Tienda de ternos — Plataforma E-commerce y Gestión

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

Plataforma en **Laravel 12** para una sastrería de ternos de gala. Fachada pública para
clientes y panel de administración para catálogo, pedidos, medidas y citas del taller.

---

## ✨ Modos de venta

* **Por talla:** cada modelo de terno se vende en tallas y colores, con stock propio por
  variante y descuento transaccional al confirmar el pedido.
* **A medida:** el cliente elige tela, perfil de medidas y notas; el pedido guarda una copia
  de las medidas para la confección y no mueve stock.
* **Citas en el taller:** toma de medidas, prueba y consulta, con disponibilidad calculada
  sobre el horario configurado.

El pago no pasa por pasarela: el pedido se crea y el pago se coordina por WhatsApp o
transferencia.

---

## 🛠️ Stack

* **Backend:** PHP 8.2+, Laravel 12
* **Frontend:** Blade, Bootstrap 5.3, CSS propio con tokens de diseño
* **Base de datos:** MySQL (SQLite en memoria para las pruebas)

---

## 🚀 Instalación local

Necesitas **PHP >= 8.2**, **Composer** y **MySQL** (o Laragon / XAMPP).

```bash
git clone https://github.com/alexandervi1/SastreriaLaravel.git
cd SastreriaLaravel
composer install
cp .env.example .env
php artisan key:generate
```

Ajusta `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`. Para SQLite local:
`DB_CONNECTION=sqlite` y una ruta absoluta a `database/database.sqlite`.

```bash
php artisan migrate --seed
php artisan serve
```

Disponible en `http://localhost:8000`.

---

## 🐳 Docker

Sin PHP, Composer ni MySQL instalados: un servicio Apache/PHP (`app`) y un MySQL 8.4
(`db`, contenedor `sastreria-db`).

```bash
docker compose up --build
```

Disponible en `http://localhost:8080`. En el primer arranque el contenedor espera a MySQL,
migra y siembra la base (catálogo de ejemplo y administrador inicial).

* **Puerto HTTP:** `DOCKER_HTTP_PORT` (por defecto `8080`).
* **Base de datos:** `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD` y
  `MYSQL_ROOT_PASSWORD` (por defecto `sastreria` / `sastreria` / `secret` / `root`).
* **`APP_KEY`:** se toma de `.env`; si falta, el contenedor genera una efímera y lo avisa.
* **Datos persistentes:** volumen `dbdata` para MySQL y `./public/img` + `./storage`
  montados desde el repositorio.
* **Catálogo de ejemplo:** `SEED_DEMO` (por defecto `true`; desactivado en producción).

---

## 🔐 Credenciales de demostración

* **Rol:** Administrador
* **Usuario:** `admin123`
* **Contraseña:** `admin123`

> [!WARNING]
> Al entrar con estas credenciales aparece una **alerta crítica permanente** hasta que cambies
> usuario o contraseña en **Configuración → Seguridad**.

---

## 🧪 Pruebas

La suite corre sobre SQLite en memoria (configurado en `phpunit.xml`) y no toca tu base de
desarrollo:

```bash
php artisan test
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
```

---

## 🚀 Despliegue

El servidor usa el patrón de `~/infra/apps/<app>`: el repositorio se clona en esa carpeta,
el `docker-compose.yml` del proyecto corre **sin `ports:`** y se une a la red `proxy-net`,
donde nginx y el túnel de Cloudflare publican el dominio. No escribas rutas reales, IPs ni
subredes en este repositorio.

* **Contenedores:** `sastreria-app` y `sastreria-db`; la app usa `DB_HOST=sastreria-db`.
* **Variables mínimas:** `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `APP_KEY`,
  `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `INITIAL_ADMIN_*` (solo en el primer arranque).
* **Persistencia:** volúmenes para `public/img` (productos, telas y logo) y `storage`.
* **Migraciones:** el entrypoint espera a MySQL y ejecuta `migrate --force`; no hay paso manual.
* **Respaldo:** programa un `mysqldump` y una copia de `public/img` con la misma política de
  retención que el resto de aplicaciones del servidor.
* **Arranque:** el entrypoint cachea configuración, rutas, vistas y eventos cuando
  `APP_ENV=production`.

---

## 📂 Arquitectura

* **MVC** con controladores delgados y lógica de negocio en `app/Services/`
  (`OrderService`, `PricingService`, `AgendaService`).
* **Form Requests** para validación en `app/Http/Requests/`.
* **Configuración cacheada** (`Configuracion::valores()`), invalidada por eventos del modelo
  y memoizada por petición.
* **Un solo stylesheet** (`public/css/style.css`) con primitivos de marca, tokens semánticos y
  puente a Bootstrap; las vistas no usan colores hex.
