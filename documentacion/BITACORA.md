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

## Puertas Docker

| Fase | Resultado |
|---|---|
| F1 | pendiente |
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
