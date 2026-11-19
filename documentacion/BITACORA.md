# Bitácora — tienda de ternos

Plan: [`PLAN_TIENDA_TERNOS.md`](PLAN_TIENDA_TERNOS.md).
Una línea por subfase. La línea se añade en un commit `docs(bitacora)` posterior,
con el hash real del commit de la subfase.

## Progreso

<!-- F0 -->
- [x] F0.1 — `d2070c7` — plan y bitácora en el repo — pruebas: 88 passed
- [x] F0.2 — `a9fb0b9` — identidad técnica de la sastrería — pruebas: 88 passed
- [x] F0.3 — `2ce186e` — retira Caddy, imágenes y seeders de la pescadería — pruebas: 88 passed
- [x] F0.4 — `5805bb6` — README de la sastrería — pruebas: 88 passed

<!-- F1 -->
- [x] F1.1 — `e204890` — elimina unidades de medida y la venta por peso — pruebas: 83 passed
- [x] F1.2 — `ac75a29` — variantes por talla y color con stock propio — pruebas: 90 passed
- [x] F1.3 — `b233a3c` — compra por talla y líneas de pedido propias — pruebas: 94 passed
- [x] F1.4 — `cfb7f5b` — reportes de inventario por variante — pruebas: 97 passed

<!-- F2 -->
- [x] F2.1 — `24db689` — catálogo de telas — pruebas: 101 passed
- [x] F2.2 — `fa6d665` — perfiles de medidas del cliente — pruebas: 108 passed
- [x] F2.3 — `47d7ed4` — encargo a medida desde la ficha — pruebas: 112 passed
- [x] F2.4 — `1ad47c8` — líneas a medida con medidas y tela congeladas — pruebas: 118 passed
- [x] F2.5 — `8e61ba3` — flujo de confección y orden de trabajo — pruebas: 124 passed

<!-- F3 -->
- [x] F3.1 — `0f0ce23` — horario del taller y bloqueos configurables — pruebas: 127 passed
- [x] F3.2 — `a3cdccc` — disponibilidad y reserva transaccional — pruebas: 135 passed
- [x] F3.3 — `09e4dec` — reserva y cancelación para clientes — pruebas: 141 passed
- [x] F3.4 — `dc293a7` — agenda del taller en el panel — pruebas: 146 passed
- [x] F3.5 — `3ccf209` — enlaces de WhatsApp para pagos y citas — pruebas: 149 passed
- [x] F3.5b — `e208a5a` — corrige la compilación de los enlaces de WhatsApp — pruebas: 149 passed

## Puertas Docker

| Fase | Resultado |
|---|---|
| F1 | **OK.** Build `sastreria:verif`; `/api/salud` → `{"estado":"ok"}`. Básico: `/`, `/login`, `/registro`, `/ayuda` y ficha por slug → 200; `/admin` sin sesión → 302. Flujo talla: registro → login → variante 1 con cantidad 2 → checkout → stock 10→8 → admin cancela → 8→10 → reactivar a Entregado rechazado (sigue `Cancelado`). Permisos en `public/img/productos` y `storage/framework/cache` OK; `production.ERROR` = 0. `/politicas` y `public/img/telas` llegan en F4.3 y F2.1, así que no se probaron en esta puerta. |
| F2 | **OK.** Build `sastreria:verif`; `/api/salud` 200. Flujo talla cubierto en F1. Flujo medida: admin crea tela y modelo a medida → cliente se registra, crea perfil (pecho 101.5) → agrega encargo con tela → checkout → `pedido_linea.medidas` contiene la copia (pecho 101.5) → admin pasa a `Confirmado` y `En confección` → `fecha_entrega_estimada` = 2026-12-03 → orden de trabajo 200 y muestra 101.5 y la tela. Permisos en `public/img/telas` OK; `production.ERROR` = 0. |
| F3 | **OK.** Build `sastreria:verif`. Flujo talla: stock 10→8. Flujo medida: pedido con copia de medidas (101.5), admin lo confirma y pasa a `En confección`; orden de trabajo 200 con las medidas. Flujo cita: 12 huecos para el viernes; cliente A reserva 09:00; cliente B intenta el mismo hueco y queda 1 sola cita; el admin la confirma y la agenda del día la muestra. `production.ERROR` = 0. |
| F5 | pendiente |

## Datos pendientes de la dueña (`PENDIENTE DATO`)

| Marcador | Qué falta | Fase |
|---|---|---|
| `<REPO_NUEVO>` | Nombre del repositorio (creado como `SastreriaLaravel`) | F0.1 |
| `<NOMBRE_NEGOCIO>` | Nombre comercial | F0.2 |
| `<TALLAS>` | Tallas que maneja el negocio | F1.2 |
| `<PALETA>` | Confirmación o cambio de la paleta propuesta | F4.1 |
| `<HOSTNAME_TERNOS>` | Dominio de la tienda | F6 |

## Decisiones

- **2026-11-19 — F0.1:** el repositorio destino ya existía vacío (`origin` =
  `alexandervi1/SastreriaLaravel`). En lugar de volver a clonar, se añadió el remoto
  `pescaderia`, se hizo `fetch` y la rama `mejora/ternos` se creó desde
  `pescaderia/master`. El upstream se dejó sin asignar para que un `git push`
  accidental no apunte a la pescadería.
- **2026-11-19 — F0.3:** además de los archivos nombrados en el plan, se borró todo
  `deploy/` (`README.md`, `.env.prod.example`, `backup.sh` y `restore.sh`). El README
  no referenciaba directamente los scripts de respaldo y el resto solo existía para el
  despliegue con Caddy que se retira. Coste si es erróneo: los scripts de respaldo habrá
  que recrearlos en F6.
- **2026-11-19 — F0.3:** `public/img/icons/` también se eliminó: ninguna vista lo
  referenciaba.
- **2026-11-19 — F0.2:** los valores por defecto de marca en `AppServiceProvider` y
  `Configuracion` pasan al marcador `<NOMBRE_NEGOCIO>` y a `img/placeholder.svg`;
  los datos reales se configuran desde el panel.
- **2026-11-19 — F0.2/F0.3:** los nombres de prueba (`Pescadería Test`,
  `admin@pescaderia.example`) se adaptaron a la sastrería sin tocar aserciones de
  seguridad.
- **2026-11-19 — F1.2:** `producto.stock` se mantuvo hasta F1.3. Quitarlo en F1.2
  habría dejado el checkout sin stock que descontar y la puerta de calidad no podía
  pasar; el modelo destino se alcanza igual al cerrar F1. Coste si es erróneo:
  ninguna fila sobrante (la columna desaparece en F1.3).
- **2026-11-19 — F1.2/F1.3:** la regla «una variante con pedidos se desactiva, no se
  borra» se implementó en F1.3, cuando existe `pedido_linea.variante_id`. La prueba
  vive en `VarianteProductoTest`.
- **2026-11-19 — F1.3:** las migraciones se renumeraron para respetar las FK
  (`000010` variantes, `000011` carrito, `000012` pedido_linea) y se borró la
  migración y el modelo de `pedidoproducto`. Sigue dentro de la ventana editable de
  F1.
- **2026-11-19 — F1.3:** los reportes se adaptaron a variantes en el mismo commit
  para no dejar la suite roja; F1.4 añadió el desglose por talla en «más vendidos».
- **2026-11-19 — F1 (puerta Docker):** `/politicas` aún no existe (llega en F4.3) y
  `public/img/telas` tampoco (F2.1); la verificación de permisos se limitó a las
  carpetas existentes y se repetirá en las puertas siguientes.
- **2026-11-19 — F2.4:** carrito y pedido_linea se renumeraron a `000015` y `000016`
  para que sus FK a `tela` y `medida_cliente` existan al migrar. Sigue en la ventana
  editable de F2.
- **2026-11-19 — F2.5:** el estado `Enviado` se sustituye por el flujo de confección;
  las pruebas y los badges se adaptaron. El checkout mantiene `retiro` como valor
  por defecto cuando no se envía el campo, para no romper integraciones existentes.
- **2026-11-19 — F2.3/F2.4:** la ficha muestra telas y medidas solo a usuarios con
  sesión de cliente; un visitante ve el CTA de login. Coste si es erróneo: el visitante
  no ve el catálogo de telas antes de registrarse.
- **2026-11-19 — F3.1:** la actualización de Configuración acepta envíos parciales
  (`sometimes`) para que la pestaña Taller no exija los datos del negocio. La pestaña
  Negocio sigue validando sus campos cuando se envían.
- **2026-11-19 — F3.5:** `@php(...)` con paréntesis anidados compilaba mal en
  `admin/pedidos/show` (error 500). Se reemplazó por bloques `@php ... @endphp` y se
  añadió una prueba que renderiza esa vista; las puertas Docker lo detectaron porque
  la suite no visitaba el detalle del pedido en el panel.
