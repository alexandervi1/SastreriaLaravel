# Plan: de la Pescadería a la tienda de ternos de gala

> **Destinatario:** agente de código (DeepSeek V4.1 Flash).
> **Fecha:** 2026-11-19 · **Origen:** `alexandervi1/PescaderiaLaravel` @ `master` (`3d6af63`, Laravel 12.73, 88 pruebas en verde).
> **Destino:** repositorio nuevo `<REPO_NUEVO>` para la sastrería `<NOMBRE_NEGOCIO>`.
> **Negocio:** venta de ternos **confeccionados por talla** (con stock por talla y color) y **a medida** (sin stock, con medidas y tela), más **citas en el taller** (toma de medidas, prueba y consulta). **El pago no cambia:** se crea el pedido y el pago se coordina por WhatsApp o transferencia, sin pasarela.

Los valores entre `< >` los define la dueña del negocio. Mientras no estén, usa el marcador literal y regístralo en la bitácora como `PENDIENTE DATO`. **Nunca inventes datos reales** (RUC, teléfonos, direcciones, nombres de personas).

---

## 0. Reglas de ejecución (aplican a todo el plan)

1. **Una subfase = un commit**, en formato Conventional Commits en español. La línea de bitácora va en un commit `docs(bitacora)` posterior, con el hash real.
2. **Orden estricto:** F0 → F6. No saltes subfases: si una ya parece hecha, verifica sus criterios de aceptación.
3. **Puerta de calidad tras cada subfase** (obligatoria):
   ```bash
   php artisan test                                    # 0 failed
   vendor/bin/phpstan analyse --no-progress --memory-limit=1G   # 0 errores
   ```
   **No añadas entradas a `phpstan-baseline.neon`.** Corrige la causa.
4. **Puerta Docker** al final de F1, F2, F3 y F5: ejecuta la verificación en contenedor de la sección 7 y pega su salida en la bitácora. Si Docker no está disponible, la fase **no se cierra**: anótalo y detente.
5. **Migraciones:**
   - **Solo durante F1 y F2** puedes editar o borrar migraciones existentes: es un repo nuevo, sin datos de producción. Prefiere editar la migración `create_*` original de cada tabla en vez de añadir migraciones `alter`.
   - **Desde F3 vuelven a ser inmutables:** todo cambio va en una migración nueva.
6. **Prohibido:**
   - Ejecutar `migrate:fresh`, `migrate:refresh`, `db:wipe` o `db:seed` contra una base local real. Usa solo `php artisan test` (SQLite en memoria) o un contenedor desechable.
   - Ejecutar `artisan` como root dentro del contenedor: siempre `docker exec -u www-data ...`.
   - Borrar o debilitar pruebas de seguridad (`AuthThrottleTest`, `AutorizacionRutasTest`, `CabecerasSeguridadTest`, `TrustedProxiesTest`, `SeederProduccionTest`, `ImagenProductoTest`, `SaludTest`). Puedes adaptar sus datos de prueba, no sus aserciones de seguridad.
   - Añadir dependencias de Composer o npm sin que la subfase lo pida.
   - Usar imágenes con derechos de autor (fotos de internet). Usa los SVG de marcador de F5.1.
   - Commitear `.env`, secretos, `database/database.sqlite` o `.phpunit.result.cache`.
   - `git push --force`, `git reset --hard`, `--no-verify`.
7. **Conserva sin cambios funcionales** (salvo que una subfase lo pida): autenticación y throttling, roles, `CabecerasSeguridad`, `TrustProxies`, `/api/salud`, `Configuracion` con caché, bitácora de configuración, `PricingService` (IVA), seeder con credenciales de producción, Dockerfile y entrypoint.
8. **Convenciones:** dominio en español; tablas en singular (`producto`, `pedido`); PK `<tabla>_id`; lógica de negocio en `app/Services/`; validación en `app/Http/Requests/`; relaciones Eloquent con tipo de retorno (Larastan 3 lo exige); colores solo con tokens de `public/css/style.css`, nunca hex en las vistas.
9. **Si algo no cuadra** con lo descrito, detente en esa subfase y explícalo en la bitácora. No rediseñes por tu cuenta.
10. **Bitácora:** `documentacion/BITACORA.md`, una línea por subfase: `- [x] F1.2 — <hash> — <resumen> — pruebas: <N> passed`.

### Mapa de fases

| Fase | Contenido | Resultado visible |
|---|---|---|
| F0 | Repo nuevo e identidad técnica | Repo propio, sin rastros de la pescadería en la configuración |
| F1 | Catálogo por talla: de peso a variantes | Se compran ternos eligiendo talla y color, con stock por variante |
| F2 | Ternos a medida | Se encarga un terno con tela y medidas; ficha de confección imprimible |
| F3 | Citas en el taller | Reserva de citas sin choques de horario; agenda en el admin |
| F4 | Identidad visual y contenido | Sitio con aspecto de sastrería, guía de tallas y políticas |
| F5 | Datos de demostración y QA | Catálogo demo, pruebas de extremo a extremo y revisión responsive |
| F6 | Despliegue | Segunda app en el servidor, junto a la pescadería |

---

## F0 — Repo nuevo e identidad técnica

### F0.1 — Crear el repositorio conservando el historial
- **La persona** crea en GitHub un repositorio **vacío** `<REPO_NUEVO>` (sin README ni .gitignore) y decide si es privado.
- **El agente:**
  ```bash
  git clone https://github.com/alexandervi1/PescaderiaLaravel.git <REPO_NUEVO>
  cd <REPO_NUEVO>
  git remote rename origin pescaderia
  git remote add origin https://github.com/alexandervi1/<REPO_NUEVO>.git
  git checkout -b mejora/ternos
  ```
  Se conserva el historial: el trabajo de seguridad queda trazable. El remoto `pescaderia` sirve solo de referencia; **nunca hagas push a él**.
- Copia este plan a `documentacion/PLAN_TIENDA_TERNOS.md` y crea `documentacion/BITACORA.md`.
- **Commit:** `docs: plan de transformación a tienda de ternos`
- **Aceptación:** `git remote -v` muestra `origin` → `<REPO_NUEVO>`; pruebas en verde (88).

### F0.2 — Identidad técnica
- **Archivos:** `composer.json` (`name`, `description`), `.env.example` (`APP_NAME="<NOMBRE_NEGOCIO>"`, `DB_DATABASE=sastreria`), `docker-compose.yml`, `config/app.php` (el `name` por defecto), `.github/workflows/ci.yml` (nombres de imagen), `phpunit.xml` si tiene nombres.
- **Pasos:**
  1. En `docker-compose.yml`: `name: sastreria`, imagen `sastreria-laravel:local`, base `sastreria` / usuario `sastreria`.
  2. **Lección del despliegue anterior:** en el servidor, el alias `db` de `proxy-net` pertenece a otra app. En `docker-compose.yml` pon `container_name: sastreria-db` y `DB_HOST: sastreria-db`. Haz lo mismo en el entrypoint si tiene `db` por defecto (`${DB_HOST:-db}` → `${DB_HOST:-sastreria-db}`).
  3. `grep -rniE "pescader|don walter|mariscos" --exclude-dir=vendor --exclude-dir=.git .` debe dejar solo vistas, seeders, CSS e imágenes (se tratan en F1–F5) y el plan o la bitácora.
- **Commit:** `chore: identidad técnica de la sastrería`

### F0.3 — Retirar lo que solo servía a la pescadería
- **Borra:**
  - `docker-compose.prod.yml`, `deploy/Caddyfile`, `deploy/cloudflare-ips.caddy`, `deploy/update-cloudflare-ips.sh` y `deploy/deploy.sh`. El servidor despliega desde `~/infra` con su propio patrón; deja `deploy/backup.sh` y `deploy/restore.sh` solo si los referencia `README.md`, y si no, bórralos también.
  - Imágenes de producto de `public/img` (pescados, mariscos, local, logo). Conserva `public/img/icons/` si las vistas lo usan, y `public/img/productos/.gitkeep` y `public/img/negocio/.gitkeep`.
  - `documentacion/SRS_PescaderiaLaravel.md`, `documentacion/PLAN_MEJORA_PRODUCCION.md` y `documentacion/PLAN_BITACORA.md`. Quedan en el historial.
  - `database/seeders/ExtraProductosSeeder.php` y `TestDataSeeder.php` si nada los llama (compruébalo con `grep`).
- **Ajusta:** las referencias rotas a imágenes borradas. Usa temporalmente `img/placeholder.svg`: créalo como un SVG gris neutro de 600×800 con el texto "Imagen".
- **Ajusta la CI:** quita del job `shell` los scripts borrados.
- **Commit:** `chore: retira despliegue con Caddy y recursos de la pescadería`
- **Aceptación:** pruebas en verde; `PagesSmokeTest` sin 404 de imágenes; la CI ejecuta solo scripts existentes.

### F0.4 — README mínimo
- Reescribe `README.md`: nombre, qué hace (los tres modos de venta), stack, instalación local, Docker, pruebas y credenciales de demo. Ninguna mención a la pescadería. El despliegue se documenta en F6.
- **Commit:** `docs: README de la sastrería`

---

## F1 — Catálogo por talla: de peso a variantes

**Modelo destino:** `producto` es un **modelo de terno** (p. ej. "Terno Slim Fit Azul Noche"). Su stock vive en `producto_variante` (talla + color). Desaparecen las unidades de medida y el factor de conversión.

### F1.1 — Eliminar unidades de medida
- **Archivos:**
  - Borrar: `app/Models/UnidadMedida.php`, `app/Http/Controllers/Admin/UnidadMedidaController.php`, `resources/views/admin/unidades/`, la migración `*_create_unidades_medida_table.php`.
  - Editar: la migración `*_create_productos_table.php` (quitar `unidad_compra_id`, `unidad_venta_id`, `factor_conversion` y sus FK; `stock` pasa a `unsignedInteger`), `app/Models/Producto.php` (quitar `unidadCompra`, `unidadVenta`, `factorConversion`, `enUnidadCompra`, el `booted()` que asigna el factor y los casts de unidades; `stockVenta` desaparece y se usa `stock`), `routes/web.php` (rutas `admin.unidades.*`), el menú del admin (`layouts/admin.blade.php` o `partials/admin_navbar.blade.php`), `StoreProductoRequest`, `UpdateProductoRequest`, `Admin/ProductoController`, `CarritoController`, `OrderService`, `ReporteController`, `DatabaseSeeder` y `DemoCatalogoSeeder`.
- **Pasos:**
  1. `grep -rn "UnidadMedida\|unidad_venta\|unidad_compra\|factor_conversion\|stockVenta\|unidadVenta\|unidadCompra\|unidades\." app resources routes database tests` y elimina **cada** uso.
  2. En las vistas, donde se mostraba la unidad ("lb", "Tn") muestra "unidad(es)" o nada.
  3. `descontarStock(int $cantidad)` y `reponerStock(int $cantidad)` trabajan en enteros.
  4. Adapta las pruebas: quita los casos de conversión Tn→lb y conserva los de stock insuficiente, rollback, IVA y cancelación con números enteros.
- **Commit:** `refactor(catalogo): elimina unidades de medida y la venta por peso`
- **Aceptación:** el `grep` del paso 1 sale vacío; pruebas y Larastan en verde.

### F1.2 — Variantes por talla y color (esquema y admin)
- **Esquema:** edita las migraciones (todavía estás en F1).
  - Nueva tabla `producto_variante`: `variante_id` PK; `producto_id` FK → `producto` con `cascade`; `talla` string(10); `color` string(40) nullable; `sku` string(40) unique; `stock` unsignedInteger default 0; `activo` boolean default true; timestamps; unique `(producto_id, talla, color)`.
  - En `producto`: elimina `stock`. Añade `slug` string(160) unique, `activo` boolean default true y `destacado` boolean default false.
- **Modelos:**
  - `ProductoVariante`, con `producto(): BelongsTo`, `descontarStock()` y `reponerStock()` movidos desde `Producto`, y `scopeBajoStock` con `Configuracion::obtenerUmbralBajoStock()`.
  - En `Producto`: `variantes(): HasMany`, `getStockTotalAttribute()` como suma de variantes activas, y `slug` generado en `saving` con `Str::slug($nombre)` más un sufijo numérico si ya existe.
- **Admin:** en `admin/productos/edit.blade.php`, una tabla "Tallas y colores" con alta, edición de stock y desactivación de variantes. Usa un controlador nuevo `Admin/VarianteController` con rutas anidadas `admin.productos.variantes.{store,update,destroy}`. Una variante con pedidos no se borra: se desactiva (el mismo patrón que "un producto con pedidos no se puede borrar").
- **Tallas sugeridas** en el formulario (datalist, no bloqueante): `<TALLAS>`; por defecto `36, 38, 40, 42, 44, 46, 48`.
- **Pruebas:** CRUD de variantes; SKU duplicado rechazado; talla y color duplicados en el mismo producto rechazados; variante con pedidos no se borra; `stock_total` suma solo las activas.
- **Commit:** `feat(catalogo): variantes por talla y color con stock propio`

### F1.3 — Tienda, carrito y checkout por variante
- **Esquema:**
  - `carrito`: añade `variante_id` FK nullable → `producto_variante` (cascade). Cambia el unique `(usuario_id, producto_id)` por un **índice** normal: un usuario puede tener dos tallas del mismo modelo. Las líneas por talla se fusionan por `variante_id` en el controlador.
  - **`pedidoproducto` se reemplaza por `pedido_linea`:** `pedido_linea_id` PK; `pedido_id` FK cascade; `producto_id` FK restrict; `variante_id` FK nullable restrict; copias `producto_nombre`, `talla` y `color` (nullable); `cantidad` unsignedInteger; `precio_unitario` y `subtotal`. La PK compuesta ya no sirve: en F2 un mismo modelo puede aparecer en dos líneas a medida distintas.
- **Modelos:** `PedidoLinea`, con `pedido()`, `producto()` y `variante()`. En `Pedido`: `lineas(): HasMany`. **Elimina** `Pedido::productos()` (belongsToMany con pivot), `Producto::pedidos()` y `PedidoProducto`.
- **Usos a migrar:** `grep -rn "productos\b\|->pivot\|pedidoproducto\|PedidoProducto" app resources tests`. En la revisión previa aparecieron, entre otros: `OrderService`, `Admin/PedidoController`, `PedidoController`, `ReporteController`, `HomeController`, las vistas `pedidos/{index,show,receipt}`, `admin/pedidos/show`, `admin/reportes/*` y `admin/dashboard`. Recorre la salida completa del `grep`, no solo esta lista.
- **Tienda:** en `productos/show.blade.php`, un selector de talla (botones) y de color, mostrando solo las variantes activas con stock. "Agregar al carrito" envía `variante_id`. Si nada tiene stock, muestra "Agotado".
- **`OrderService`:**
  - `processOrder` bloquea con `lockForUpdate` las **variantes** (no los productos), valida el stock por variante, crea las `pedido_linea` con las copias de nombre, talla y color, y descuenta el stock de la variante.
  - `cancelOrder` repone el stock por `variante_id` de cada línea (las líneas sin variante no reponen).
  - Mantén la transaccionalidad y las pruebas de rollback.
- **Rutas públicas:** `/productos/{producto:slug}`.
- **Pruebas:** comprar dos tallas del mismo modelo; stock insuficiente en una variante revierte todo; cancelar repone en la variante correcta; el recibo muestra talla y color; un precio cambiado después del pedido no altera la línea.
- **Commit:** `feat(tienda): compra por talla y líneas de pedido propias`

### F1.4 — Reportes y dashboard
- "Bajo stock" lista **variantes** (modelo, talla, color, stock).
- "Stock por categoría" suma el stock de las variantes.
- "Más vendidos" agrupa `pedido_linea` por `producto_id` y muestra el desglose por talla.
- El dashboard cuenta las variantes en bajo stock.
- **Commit:** `feat(reportes): reportes de inventario por variante`
- **Puerta Docker de F1** (sección 7, flujo "talla").

---

## F2 — Ternos a medida

### F2.1 — Catálogo de telas
- **Tabla `tela`:** `tela_id`; `nombre` string(80); `composicion` string(120) nullable (p. ej. "100 % lana merino"); `color` string(40); `recargo` decimal(10,2) default 0; `imagen_url` nullable; `activo` boolean; timestamps.
- **Admin:** CRUD en `/admin/telas`, con imagen siguiendo exactamente el patrón y las validaciones de `Admin/ProductoController::guardarImagen` (UUID, jpg/png/webp, máximo 4000×4000) en `public/img/telas/`. Crea `public/img/telas/.gitkeep` y añade la carpeta al `mkdir`/`chown` del Dockerfile y del entrypoint.
- Añade la entrada al menú del admin y las rutas a `AutorizacionRutasTest` (middleware `admin`).
- **Commit:** `feat(telas): catálogo de telas para confección a medida`

### F2.2 — Perfil de medidas del cliente
- **Tabla `medida_cliente`:** `medida_id`; `usuario_id` FK cascade; `nombre` string(60) (p. ej. "Mis medidas", "Medidas de mi hijo"); columnas decimal(5,1) **en centímetros**, todas nullable:
  - **Saco:** `pecho`, `cintura`, `cadera`, `hombros`, `espalda`, `largo_saco`, `largo_manga`, `cuello`.
  - **Pantalón:** `cintura_pantalon`, `cadera_pantalon`, `tiro`, `muslo`, `entrepierna`, `largo_pantalon`, `bota`.
  - **Otros:** `estatura` decimal(5,1) nullable, `notas` text nullable, `tomadas_en_taller` boolean default false, timestamps.
- **Validación** (`MedidaClienteRequest`): cada medida `nullable|numeric|between:10,250` (`estatura` entre 100 y 230). Exige al menos `pecho`, `cintura`, `largo_manga`, `entrepierna` y `largo_pantalon` para poder usar el perfil en un pedido (regla de negocio en el servicio, no en la request).
- **Cliente:** `/mis-medidas` con CRUD de sus perfiles; solo ve los suyos, y los ajenos dan 404 (incluye una prueba igual a la de `/mis-pedidos`). Junto al formulario, un enlace a la guía "Cómo tomar tus medidas" (contenido en F4.3).
- **Admin:** el admin ve y edita las medidas desde la ficha del cliente (`admin/clientes/edit`) y puede marcar `tomadas_en_taller`.
- **Commit:** `feat(medidas): perfiles de medidas del cliente`

### F2.3 — Producto vendible a medida
- **En `producto`:** `vende_por_talla` boolean default true, `vende_a_medida` boolean default false, `precio_medida` decimal(10,2) nullable, `dias_confeccion` unsignedSmallInteger nullable. Validación: si `vende_a_medida`, entonces `precio_medida` y `dias_confeccion` son obligatorios. Al menos uno de los dos modos debe estar activo.
- **Ficha pública con dos pestañas:** "Comprar por talla" (F1.3) y "Encargar a medida".
- **Pestaña "a medida":**
  1. Elegir tela (tarjetas con imagen, composición y recargo).
  2. Elegir un perfil de medidas propio, o la opción **"Tomar mis medidas en el taller"**, que crea la línea sin medidas y marca `requiere_cita` (F3 la enlaza con una cita).
  3. Notas (p. ej. "solapa de pico, forro bordó").
  4. Precio mostrado = `precio_medida` + `recargo` de la tela, con la fecha estimada como hoy + `dias_confeccion` días hábiles.
- **Commit:** `feat(tienda): encargo de ternos a medida desde la ficha`

### F2.4 — Carrito y pedido con líneas a medida
- **Esquema:**
  - `carrito`: añade `tipo` (`talla` o `medida`, default `talla`), `tela_id` nullable, `medida_id` nullable (FK `set null`), `requiere_cita` boolean default false y `notas` text nullable.
  - `pedido_linea`: añade `tipo`, `tela_id` nullable, `tela_nombre` nullable, `medidas` **json nullable** (**copia** del perfil al momento del pedido: si el cliente edita su perfil después, la orden de trabajo no cambia), `requiere_cita` y `notas`.
- **`PricingService`:** sigue siendo la única autoridad de los totales. Añade `precioLinea(Producto, ?Tela, string $tipo): float` y úsalo en el carrito y en `OrderService`.
- **`OrderService::processOrder`:** las líneas a medida **no tocan stock**. Valida que el producto siga `vende_a_medida`, que la tela siga activa y que el perfil tenga las medidas mínimas de F2.2, salvo `requiere_cita`. En un pedido mixto (talla + medida), si falla una línea, se revierte todo.
- **`cancelOrder`:** las líneas a medida no reponen stock.
- **Pruebas:**
  - Pedido solo a medida: el stock no cambia.
  - Pedido mixto con rollback.
  - La copia de las medidas no cambia al editar el perfil.
  - Precio con recargo de tela, con el IVA calculado por `PricingService`.
  - Una tela desactivada entre el carrito y el checkout se rechaza con un mensaje claro.
- **Commit:** `feat(pedidos): líneas a medida con medidas y tela congeladas`

### F2.5 — Estados de confección y orden de trabajo
- **Estados nuevos** en `Pedido::ESTADOS` / `TRANSICIONES`:
  - `Pendiente` → `Confirmado`, `Cancelado`
  - `Confirmado` → `En confección`, `Listo para entrega`, `Cancelado`
  - `En confección` → `Listo para entrega`
  - `Listo para entrega` → `Entregado`
  - `Entregado` y `Cancelado` son terminales.
  - `Confirmado → Listo para entrega` es para pedidos solo por talla. Valida en `OrderService::changeStatus` que un pedido con líneas a medida **no** pueda saltarse `En confección`.
- **Columnas en `pedido`:** `fecha_entrega_estimada` (date nullable; se calcula al confirmar como hoy + el mayor `dias_confeccion` de sus líneas a medida, en días hábiles), `metodo_entrega` (`retiro` o `envio`, elegido en el checkout) y `direccion_envio` (nullable, obligatoria si `envio`).
- **Orden de trabajo imprimible** `admin/pedidos/{id}/orden-trabajo`, una hoja por línea a medida: cliente, teléfono, modelo, tela, todas las medidas en una tabla clara, notas y fecha de entrega. Toma como base el estilo de impresión de `pedidos/receipt.blade.php`. **Es la herramienta de trabajo de la sastre.**
- **Pruebas:** transiciones válidas e inválidas, incluido el salto prohibido para pedidos a medida; cálculo de la fecha de entrega; la orden de trabajo muestra las medidas copiadas.
- **Commit:** `feat(pedidos): flujo de confección y orden de trabajo imprimible`
- **Puerta Docker de F2** (sección 7, flujos "talla" y "medida").

---

## F3 — Citas en el taller

> Desde aquí, las migraciones son inmutables.

### F3.1 — Horario de atención y bloqueos
- **Claves nuevas** en `configuracion`, editables en **Configuración → Taller** con validación:
  - `taller_dias`: CSV de días ISO, por defecto `1,2,3,4,5,6`.
  - `taller_hora_inicio`: por defecto `09:00`.
  - `taller_hora_fin`: por defecto `18:00`.
  - `cita_duracion_min`: por defecto `45`.
  - `cita_anticipacion_max_dias`: por defecto `30`.
  - `cita_cancelacion_min_horas`: por defecto `24`.
  - `negocio_whatsapp`: formato internacional sin `+`, por defecto vacío.
  - Añade las claves al seeder y sus `obtener*()` en `Configuracion`.
- **Tabla `cita_bloqueo`:** `bloqueo_id`, `inicio` datetime, `fin` datetime, `motivo` string(120). Sirve para feriados, vacaciones y días de entrega.
- Las claves editadas quedan en la bitácora de configuración existente (`configuracion_log`).
- **Commit:** `feat(citas): horario del taller y bloqueos configurables`

### F3.2 — Disponibilidad y reserva sin choques
- **Tabla `cita`:** `cita_id`; `usuario_id` FK restrict; `tipo` (`medidas`, `prueba` o `consulta`); `inicio` datetime; `fin` datetime; `estado` (`Solicitada`, `Confirmada`, `Atendida`, `No asistió` o `Cancelada`); `pedido_id` nullable FK set null; `notas` text nullable; timestamps; índice en `inicio`.
- **`app/Services/AgendaService.php`:**
  - `huecosDisponibles(Carbon $dia): array`: intervalos de `cita_duracion_min` dentro del horario. Excluye días no laborables, los bloqueos, las citas `Solicitada` o `Confirmada` que se solapen, las horas pasadas y lo que esté más allá de `cita_anticipacion_max_dias`.
  - `reservar(Usuario, string $tipo, Carbon $inicio, ?Pedido, ?string $notas): Cita`: dentro de `DB::transaction`, bloquea con `lockForUpdate` las citas activas del día, revalida que el hueco siga libre y crea la cita. **Dos reservas simultáneas del mismo hueco: una gana y la otra recibe `RuntimeException` con un mensaje claro.**
  - Usa la zona horaria de `Configuracion::obtenerZonaHoraria()`.
- **Pruebas** (con `Carbon::setTestNow`):
  - Huecos de un día normal.
  - Domingo sin huecos.
  - Bloqueo que excluye huecos.
  - Cita existente que excluye su hueco.
  - Hora pasada.
  - Límite de anticipación.
  - Doble reserva del mismo hueco rechazada.
  - Cambio de horario de verano: no aplica en Ecuador, pero `America/Guayaquil` debe estar en las pruebas.
- **Commit:** `feat(citas): disponibilidad y reserva transaccional`

### F3.3 — Citas del cliente
- **Rutas** (`auth` + `cliente`): `/citas` (mis citas), `/citas/nueva` (elige tipo y día, ve los huecos y confirma), `POST /citas` y `DELETE /citas/{cita}` (solo propias y solo hasta `cita_cancelacion_min_horas` antes; las ajenas dan 404).
- **Enlaces:**
  - Al terminar un pedido con líneas `requiere_cita`, la confirmación muestra **"Agenda tu cita de medidas"**, que prellena `tipo=medidas` y `pedido_id`.
  - Desde `/mis-pedidos/{id}`, en un pedido `En confección`, el botón **"Agendar prueba"**.
- **Throttle:** `POST /citas` con un límite `citas` de 10 por hora y usuario.
- **Commit:** `feat(citas): reserva y cancelación para clientes`

### F3.4 — Agenda del taller (admin)
- `/admin/citas`: vista por día y por semana (lista agrupada por día, sin librerías de calendario nuevas), filtros por estado y acciones Confirmar, Atendida, No asistió y Cancelar.
- **Transiciones:**
  - `Solicitada` → `Confirmada`, `Cancelada`
  - `Confirmada` → `Atendida`, `No asistió`, `Cancelada`
  - Las demás son terminales.
- El admin puede **crear citas para un cliente** (pedidos por teléfono) usando el mismo `AgendaService::reservar`.
- Gestión de bloqueos en la misma pantalla.
- Dashboard: tarjeta "Citas de hoy" y contador en el menú (el mismo patrón que `AdminLayoutComposer`).
- **Commit:** `feat(citas): agenda del taller en el panel`

### F3.5 — Avisos por WhatsApp (sin API)
- Helper `App\Support\WhatsApp::enlace(string $texto): ?string`, que devuelve `https://wa.me/<negocio_whatsapp>?text=<urlencoded>`, o `null` si no hay número configurado.
- **Botones:**
  - Cliente, en la confirmación de pedido: "Coordinar pago por WhatsApp", con el número de comprobante y el total.
  - Cliente, al reservar una cita: "Avisar al taller", con la fecha y la hora.
  - Admin, en la ficha de pedido y de cita: "Escribir al cliente" (usa el teléfono del cliente).
- **No** se integra ninguna API ni se envían mensajes automáticos.
- **Pruebas:** el enlace se codifica bien con tildes y saltos de línea; no hay botón si falta el número.
- **Commit:** `feat(whatsapp): enlaces para coordinar pagos y citas`
- **Puerta Docker de F3** (sección 7, flujos "talla", "medida" y "cita").

---

## F4 — Identidad visual y contenido

### F4.1 — Paleta y tipografía de sastrería
- **Solo** en `public/css/style.css`, redefiniendo los **primitivos de marca** (`--brand-*`). Las clases (`text-brand`, `bg-brand`, `btn-brand-dark`, `stat-card-*`) y el puente a Bootstrap se mantienen, así las vistas no cambian.
- **Propuesta por defecto** (la persona puede cambiarla en `<PALETA>`):
  - Carbón `#1C1C1E` para el primario fuerte.
  - Azul noche `#1F2A44` para el primario.
  - Dorado champaña `#B08D57` para el acento: solo en bordes, iconos y texto grande, nunca en texto pequeño sobre blanco.
  - Marfil `#F7F4EE` para la superficie.
- **Tipografía:** títulos con *Cormorant Garamond* (600) y texto con *Inter* (400/500), desde Google Fonts y con `font-display: swap`.
- **Contraste WCAG AA verificado** en botones, badges, enlaces, el texto atenuado y los estados de pedido y cita. Deja la tabla de contrastes medidos en la bitácora.
- **Commit:** `style: paleta y tipografía de sastrería`

### F4.2 — Marca configurable
- `negocio_nombre`, `negocio_logo`, `negocio_leyenda`, `negocio_direccion`, `negocio_telefono` y `negocio_ruc` ya existen en `configuracion`: rellénalos en el seeder con **marcadores** (`<NOMBRE_NEGOCIO>`, etc.), no con datos inventados.
- **Logo por defecto:** `public/img/negocio/logo-placeholder.svg` (monograma con las iniciales sobre círculo carbón).
- **Favicon** desde el logo.
- El `<title>` de cada página usa `negocio_nombre`.
- **Commit:** `feat(marca): nombre, logo y favicon configurables`

### F4.3 — Contenido del sitio
- **Home:**
  - Portada con frase y CTA "Agenda tu cita".
  - Categorías.
  - Ternos destacados (`destacado = true`).
  - "Así funciona tu terno a medida" en 4 pasos: eliges modelo y tela → tomamos medidas → prueba → entrega.
  - Bloque de ubicación y horario, leído de `configuracion`.
- **Quiénes somos:** estructura con marcadores `[COMPLETAR: historia de la sastre, años de oficio, foto del taller]`.
- **Ayuda:**
  - Guía de tallas: tabla de talla → pecho y cintura en cm, editable como vista.
  - "Cómo tomar tus medidas": una sección por medida de F2.2, con instrucción breve y un SVG esquemático simple.
  - Preguntas frecuentes.
- **Páginas nuevas:**
  - `/politicas`: cambios y devoluciones. Usa como texto por defecto, marcado como `[REVISAR]`: "los ternos a medida no admiten devolución; ajustes gratuitos dentro de 15 días; los ternos por talla admiten cambio de talla dentro de 7 días con etiqueta".
  - `/terminos`.
  - Enlázalas en el footer y en el checkout ("Al confirmar aceptas…").
- `PagesSmokeTest` cubre todas las páginas públicas nuevas.
- **Commit:** `feat(contenido): home, guía de tallas, medidas y políticas`

### F4.4 — Galería de imágenes por modelo
- **Tabla `producto_imagen`:** `imagen_id`, `producto_id` cascade, `url`, `orden` unsignedSmallInteger y `alt` string(160). `producto.imagen_url` se mantiene como portada.
- **Admin:** subir varias imágenes (máximo 8 por modelo, mismas validaciones), reordenar con botones ↑/↓ (sin librerías nuevas) y elegir la portada.
- **Ficha pública:** galería con miniaturas y `alt` obligatorio.
- **Commit:** `feat(catalogo): galería de imágenes por modelo`

### F4.5 — Metadatos
- `<meta name="description">`, Open Graph (título, descripción e imagen de portada) y `<link rel="canonical">` en la home, las categorías y la ficha de producto.
- **Commit:** `feat(seo): metadatos y Open Graph`

---

## F5 — Datos de demostración y QA

### F5.1 — Catálogo demo
- Reescribe `DemoCatalogoSeeder` (se activa solo con `SEED_DEMO`, como ahora):
  - **Categorías:** Ternos, Smokings, Chalecos y Accesorios.
  - **6 modelos:** 4 por talla y a medida, 1 solo a medida y 1 accesorio solo por talla. Cada uno con 3 a 7 variantes y stock variado: algunos en 0 y otros por debajo del umbral, para que los reportes muestren datos.
  - **5 telas** con recargos 0, 25, 40, 60 y 90.
  - **Imágenes:** SVG de marcador generados por el seeder en `public/img/productos/demo/`, con la silueta de un terno en el color del modelo y su nombre. Nada de fotos de internet.
- **Commit:** `feat(seed): catálogo demo de ternos y telas`

### F5.2 — Prueba de extremo a extremo y repaso de la suite
- Revisa que cada flujo tenga pruebas de feature:
  - **talla:** registro → compra → stock → cancelación → reposición.
  - **medida:** perfil → encargo → copia de medidas → orden de trabajo.
  - **cita:** huecos → reserva → doble reserva → cancelación.
  - **admin:** variantes, telas, agenda, estados y bloqueos.
- Añade lo que falte.
- **Commit:** `test: cobertura de extremo a extremo de los tres flujos`

### F5.3 — Revisión responsive y de accesibilidad
- Revisa a 360, 768, 1024 y 1440 px:
  - home;
  - ficha de producto (las dos pestañas);
  - carrito;
  - checkout;
  - mis medidas;
  - nueva cita;
  - las pantallas nuevas del admin: variantes, telas, agenda y orden de trabajo, esta última también en impresión.
- Objetivos táctiles de 44 px, foco visible y formularios con `label`.
- Corrige y deja en la bitácora la lista de pantallas revisadas.
- **Commit:** `fix(ui): ajustes responsive y de accesibilidad`
- **Puerta Docker de F5:** todos los flujos.

---

## F6 — Despliegue

### F6.1 — Preparar el despliegue (solo documentación en el repo)
- `README.md`, sección "Despliegue": el servidor usa el patrón de `~/infra/apps/<app>` (repo clonado + compose sin `ports:` + `proxy-net` + nginx + túnel de Cloudflare). **No** escribas en el repo detalles internos del servidor (rutas reales, IPs ni subredes).
- **Commit:** `docs: notas de despliegue`

### F6.2 — Prompt para el agente del servidor
- **No lo hace este agente.** La persona pide a Claude el prompt de despliegue: se adapta el que ya se usó con la pescadería, con estos cambios:
  - `sastreria-app` y `sastreria-db`, con `DB_HOST=sastreria-db`.
  - Volumen adicional para `public/img/telas`.
  - Clave `age` propia, heartbeat propio y el hostname `<HOSTNAME_TERNOS>`.
- **Capacidad:** la pescadería usa unos 510 MB y la sastrería usará algo parecido. El servidor tiene margen de sobra (11 GiB).

---

## 7. Verificación en contenedor (puerta Docker)

Ejecuta desde la raíz del repo y pega la salida en la bitácora. **Limpia siempre** solo los recursos que crea, nunca con `prune`.

```bash
docker build -q --target prod -t sastreria:verif .
docker network create verif-net
docker run -d --name verif-db --network verif-net -e MYSQL_DATABASE=sastreria -e MYSQL_USER=sastreria \
  -e MYSQL_PASSWORD=verifpass -e MYSQL_ROOT_PASSWORD=verifroot mysql:8.4
docker run -d --name verif-app --network verif-net -p 8099:80 -e APP_ENV=production -e APP_DEBUG=false \
  -e APP_KEY="base64:$(head -c 32 /dev/urandom | base64)" -e LOG_CHANNEL=stderr -e DB_CONNECTION=mysql \
  -e DB_HOST=verif-db -e DB_DATABASE=sastreria -e DB_USERNAME=sastreria -e DB_PASSWORD=verifpass \
  -e SEED_DEMO=true -e SESSION_ENCRYPT=true -e INITIAL_ADMIN_USER=adminverif \
  -e INITIAL_ADMIN_PASSWORD='ClaveVerif2026!' -e INITIAL_ADMIN_EMAIL=admin@ejemplo.com sastreria:verif
# Esperar hasta 3 min a que /api/salud responda 200.
```

Con `curl` y un *cookie jar* (toma el `_token` de cada formulario):

| Flujo | Pasos y resultado esperado |
|---|---|
| **Básico** | `/api/salud` → `{"estado":"ok"}`; `/`, `/login`, `/registro`, `/ayuda`, `/politicas` y una ficha de producto por slug → 200; `/admin` sin sesión → 302. Todas las pantallas del admin con sesión de admin → 200. |
| **talla** | Registrar cliente → login → agregar una variante con `cantidad=2` → checkout → el stock de **esa variante** baja 2 (consulta con `mysql`) → el admin cancela → el stock vuelve → reactivar el pedido cancelado se rechaza. |
| **medida** | Crear un perfil de medidas → encargar a medida con tela → checkout → ningún stock cambia → `pedido_linea.medidas` contiene la copia → el admin pasa el pedido a `Confirmado` (se calcula `fecha_entrega_estimada`) y luego a `En confección` → la orden de trabajo responde 200 y contiene el valor de `pecho`. |
| **cita** | Pedir los huecos de mañana → reservar uno → reservar **el mismo** otra vez con otro cliente → rechazo → el admin la confirma → aparece en "Citas de hoy" si es del día. |
| **Sistema** | `docker exec -u www-data verif-app sh -c 'touch public/img/productos/w public/img/telas/w storage/framework/cache/w && rm public/img/productos/w public/img/telas/w storage/framework/cache/w'` sin errores; `docker logs verif-app 2>&1 \| grep -c production.ERROR` → `0`. |

```bash
docker rm -f verif-app verif-db && docker network rm verif-net && docker image rm sastreria:verif
```

---

## 8. Datos que debe aportar la dueña del negocio

Ninguno bloquea a F0–F5: se usan marcadores, pero el sitio **no debe publicarse** sin ellos.

| Marcador | Qué es | Dónde se usa |
|---|---|---|
| `<REPO_NUEVO>` | Nombre del repositorio en GitHub | F0.1 |
| `<NOMBRE_NEGOCIO>` | Nombre comercial | Título, logo, recibos, `APP_NAME` |
| RUC, dirección, teléfono, WhatsApp | Datos fiscales y de contacto | Configuración (desde el panel) |
| `<TALLAS>` | Tallas que maneja | Sugerencias en el admin y guía de tallas |
| Horario del taller | Días y horas | Configuración → Taller |
| Políticas | Cambios, ajustes y tiempos | `/politicas` |
| Historia y fotos | Quiénes somos y fotos reales de ternos | Contenido y catálogo real |
| `<PALETA>` | Si quiere otros colores | F4.1 |
| `<HOSTNAME_TERNOS>` | Dominio | F6 |
