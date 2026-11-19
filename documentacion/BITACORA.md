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

## Puertas Docker

| Fase | Resultado |
|---|---|
| F1 | **OK.** Build `sastreria:verif`; `/api/salud` → `{"estado":"ok"}`. Básico: `/`, `/login`, `/registro`, `/ayuda` y ficha por slug → 200; `/admin` sin sesión → 302. Flujo talla: registro → login → variante 1 con cantidad 2 → checkout → stock 10→8 → admin cancela → 8→10 → reactivar a Entregado rechazado (sigue `Cancelado`). Permisos en `public/img/productos` y `storage/framework/cache` OK; `production.ERROR` = 0. `/politicas` y `public/img/telas` llegan en F4.3 y F2.1, así que no se probaron en esta puerta. |
| F2 | pendiente |
| F3 | pendiente |
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
