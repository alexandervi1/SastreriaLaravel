# Bitácora — tienda de ternos

Plan: [`PLAN_TIENDA_TERNOS.md`](PLAN_TIENDA_TERNOS.md).
Una línea por subfase. La línea se añade en un commit `docs(bitacora)` posterior,
con el hash real del commit de la subfase.

## Progreso

<!-- F0 -->
- [ ] F0.1 — Crear el repositorio conservando el historial

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
