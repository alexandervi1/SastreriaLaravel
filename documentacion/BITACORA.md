# Bitácora — Entretela

Plan: [`PLAN_TIENDA_TERNOS.md`](PLAN_TIENDA_TERNOS.md) · Adenda: [`ADENDA_A.md`](ADENDA_A.md).
Una línea por subfase. La línea se añade en un commit `docs(bitacora)` posterior,
con el hash real del commit de la subfase.

## Progreso

<!-- F0 -->
- [x] F0.1 — `bd5e0dd` — plan y bitácora en el repo — pruebas: 88 passed
- [x] F0.2 — `ac5232e` — identidad técnica de la sastrería — pruebas: 88 passed
- [x] F0.3 — `57246cb` — retira Caddy, imágenes y seeders de la pescadería — pruebas: 88 passed
- [x] F0.4 — `8d9f4d1` — README de la sastrería — pruebas: 88 passed

<!-- F1 -->
- [x] F1.1 — `6841d8f` — elimina unidades de medida y la venta por peso — pruebas: 83 passed
- [x] F1.2 — `e609f39` — variantes por talla y color con stock propio — pruebas: 90 passed
- [x] F1.3 — `62170ca` — compra por talla y líneas de pedido propias — pruebas: 94 passed
- [x] F1.4 — `46a4d59` — reportes de inventario por variante — pruebas: 97 passed

<!-- F2 -->
- [x] F2.1 — `f5046e5` — catálogo de telas — pruebas: 101 passed
- [x] F2.2 — `0c173e8` — perfiles de medidas del cliente — pruebas: 108 passed
- [x] F2.3 — `62c3ea9` — encargo a medida desde la ficha — pruebas: 112 passed
- [x] F2.4 — `dd50cd3` — líneas a medida con medidas y tela congeladas — pruebas: 118 passed
- [x] F2.5 — `25afed7` — flujo de confección y orden de trabajo — pruebas: 124 passed

<!-- F3 -->
- [x] F3.1 — `f5147db` — horario del taller y bloqueos configurables — pruebas: 127 passed
- [x] F3.2 — `95dd927` — disponibilidad y reserva transaccional — pruebas: 135 passed
- [x] F3.3 — `e225e7d` — reserva y cancelación para clientes — pruebas: 141 passed
- [x] F3.4 — `956079c` — agenda del taller en el panel — pruebas: 146 passed
- [x] F3.5 — `79d752c` — enlaces de WhatsApp para pagos y citas — pruebas: 149 passed
- [x] F3.5b — `ac65184` — corrige la compilación de los enlaces de WhatsApp — pruebas: 149 passed

<!-- F4 -->
- [x] F4.1 — `d5e9f13` — paleta y tipografía de sastrería — pruebas: 158 passed
- [x] F4.2 — `17bee84` — nombre, logo y favicon configurables — pruebas: 158 passed
- [x] F4.3 — `c7a2c2b` — home, guía de tallas, medidas y políticas — pruebas: 158 passed
- [x] F4.4 — `a1ec21b` — galería de imágenes por modelo — pruebas: 158 passed
- [x] F4.5 — `d4ea97f` — metadatos y Open Graph — pruebas: 158 passed

<!-- F5 -->
- [x] F5.1 — `ae9115c` — catálogo demo de ternos y telas — pruebas: 158 passed
- [x] F5.2 — `86b8dfc` — cobertura de extremo a extremo de los tres flujos — pruebas: 160 passed
- [x] F5.3 — `99fdc89` + `6e315b7` — ajustes responsive y de accesibilidad — pruebas: 160 passed

<!-- F6 -->
- [x] F6.1 — `e775f58` — notas de despliegue — pruebas: 160 passed
- [ ] F6.2 — prompt para el agente del servidor (lo hace la persona)

<!-- Adenda A -->
- [x] A1 — `a1ba7b6` — venta por talla tras interruptor, apagada por defecto — pruebas: 164 passed
- [x] A2 — `7e8be0a` — tipo de prenda y público — pruebas: 169 passed
- [x] A3 — `db1b510` — patrones de caballero, dama y niño — pruebas: 176 passed
- [x] A4 — `3e3d6cf` — privacidad del taller y citas a domicilio — pruebas: 183 passed
- [x] A5 — `53ae875` — fecha de entrega según capacidad del taller — pruebas: 186 passed
- [x] A6 — `4486475` — precio desglosado en hechura y tela — pruebas: 189 passed
- [x] A7 — `241c2af` — identidad Entretela, tema de gala y colibrí — pruebas: 194 passed
- [x] F4.3 — `28db7b1` — contenido y políticas solo a medida — pruebas: 199 passed
- [x] F5.1 — `f55d96b` — catálogo demo solo a medida — pruebas: 199 passed
- [x] A8 — `45ef630` — documentación heredada y pendientes — pruebas: 199 passed
- [x] A9 — puerta Docker final — pruebas: 199 passed; `production.ERROR` = 0
- [x] A10 — push de `mejora/ternos` a `origin` (revisión y PR pendientes de la persona)

## Puertas Docker

| Fase | Resultado |
|---|---|
| F1 | **OK.** Build `sastreria:verif`; `/api/salud` → `{"estado":"ok"}`. Básico: `/`, `/login`, `/registro`, `/ayuda` y ficha por slug → 200; `/admin` sin sesión → 302. Flujo talla: registro → login → variante 1 con cantidad 2 → checkout → stock 10→8 → admin cancela → 8→10 → reactivar a Entregado rechazado (sigue `Cancelado`). Permisos en `public/img/productos` y `storage/framework/cache` OK; `production.ERROR` = 0. `/politicas` y `public/img/telas` llegan en F4.3 y F2.1, así que no se probaron en esta puerta. |
| F2 | **OK.** Build `sastreria:verif`; `/api/salud` 200. Flujo talla cubierto en F1. Flujo medida: admin crea tela y modelo a medida → cliente se registra, crea perfil (pecho 101.5) → agrega encargo con tela → checkout → `pedido_linea.medidas` contiene la copia (pecho 101.5) → admin pasa a `Confirmado` y `En confección` → `fecha_entrega_estimada` = 2026-12-03 → orden de trabajo 200 y muestra 101.5 y la tela. Permisos en `public/img/telas` OK; `production.ERROR` = 0. |
| F3 | **OK.** Build `sastreria:verif`. Flujo talla: stock 10→8. Flujo medida: pedido con copia de medidas (101.5), admin lo confirma y pasa a `En confección`; orden de trabajo 200 con las medidas. Flujo cita: 12 huecos para el viernes; cliente A reserva 09:00; cliente B intenta el mismo hueco y queda 1 sola cita; el admin la confirma y la agenda del día la muestra. `production.ERROR` = 0. |
| F5 | **OK.** Build `sastreria:verif` con el catálogo demo. Básico: `/`, `/login`, `/registro`, `/ayuda`, `/politicas`, `/terminos` y ficha por slug → 200; `/admin` sin sesión → 302. Talla: variante 1 con cantidad 2 → stock 4→2; el admin cancela → 4. Medida: perfil con pecho 101.5 → encargo con tela → `pedido_linea.medidas` conserva la copia; confirmado y en confección, fecha 2026-12-17; orden de trabajo 200 con 101.5. Cita: 12 huecos; cliente A reserva 09:00; otro cliente no puede repetir el hueco; el admin la confirma y aparece en la agenda. Permisos en `public/img/productos`, `public/img/telas` y caché OK; `production.ERROR` = 0. |
| A9 | **OK.** Build `sastreria-laravel:local` con volumen nuevo. `/api/salud` 200; básicos `/`, `/login`, `/registro`, `/ayuda`, `/politicas`, `/terminos`, ficha y `/favicon.svg` → 200. Solo medida (defecto): la ficha no muestra «Por talla»; el carrito rechaza una variante y el checkout rechaza una línea de talla. Medida: hechura 340 + tela 60 = 400; pedido 460 con IVA; medidas y desglose congelados. Talla con `venta_por_talla=1` por SQL y caché limpiada: cantidad 2 → stock 5→3. Citas: taller 09:00–09:45; domicilio 10:30–12:00 (dos huecos). Privacidad: «Calle Secreta 123» no aparece en home, ayuda, políticas, términos, quiénes somos, cita solicitada ni cita de otra cliente; sí aparece para la dueña con cita confirmada y en la agenda del admin; el recibo usa la dirección pública. `production.ERROR` = 0. |

## Contrastes medidos (WCAG)

Tema oscuro de la tienda:

| Fondo / texto | Ratio | Uso |
|---|---|---|
| `#121110` / `#F3EFE6` | 16.44 | texto principal |
| `#121110` / `#9A948A` | 6.27 | texto secundario |
| `#121110` / `#CDB991` | 9.83 | texto dorado pequeño |
| `#A88E5C` / `#121110` | 6.01 | botón principal |
| `#1C1A18` / `#F3EFE6` | 15.12 | tarjetas |
| `#1C1A18` / `#A88E5C` | 5.53 | acento sobre superficie |

Panel, recibo y orden de trabajo en claro:

| Fondo / texto | Ratio | Uso |
|---|---|---|
| `#F3EFE6` / `#2A2622` | 13.08 | texto principal |
| `#F3EFE6` / `#5C564D` | 6.32 | texto secundario |
| `#F3EFE6` / `#7A6438` | 4.94 | detalles y enlaces |
| `#7A6438` / `#FFFDF9` | 5.58 | botón sobre superficie |

## Revisión visual (F5.3)

Anchos revisados: 360, 768, 1024 y 1440 px.

* Home: 360, 768 y 1440.
* Ficha de producto (pestañas por talla y a medida): 360 y 768.
* Carrito con una línea: 360.
* Nuevo perfil de medidas: 360.
* Nueva cita con huecos: 360.
* Agenda del taller: 1024 y 1440.
* Edición de producto con variantes y galería: 768 y 1440.
* Panel: 1440.

Corregido tras la revisión: enlaces del footer sin recorte, sidebar legible a 1024, columnas de
precio/subtotal ocultas en el carrito móvil, acciones del catálogo según el modo de venta,
iconos de sastre y textos de producto. La orden de trabajo se validó por HTTP en la puerta
Docker, no con captura visual.

### Revisión A7/A9 (Entretela)

* Home: 1440 y 390 px.
* Ficha de producto: 1344 px.
* Carrito: 1365 px.
* Panel y agenda: 1365 px.
* Recibo: 1259 px.

Corregido: duplicado del título, acciones de tarjeta que aún decían «Ver tallas» con el
interruptor apagado, cabecera del panel en marfil con colibrí en tinta, tarjeta de ingresos
con texto oscuro y recibo sin el marco oscuro del sitio. Los contrastes de los dos temas
quedaron medidos arriba.

## Datos pendientes de la dueña (`PENDIENTE DATO`)

Resueltos: `<NOMBRE_NEGOCIO>` = **Entretela**; `<PALETA>` = negro gala y oro viejo.
`<TALLAS>` ya no aplica: la tienda es solo a medida.

| Marcador | Qué falta | Dónde se usa |
|---|---|---|
| RUC, teléfono, WhatsApp, dirección pública y exacta | Datos fiscales, de contacto y privacidad del taller | Configuración → Negocio y Taller |
| `<HOSTNAME_TERNOS>` | Dominio de la tienda | F6 |
| `[COMPLETAR %]` | Porcentaje del anticipo | Políticas y términos |
| Historia y fotos | Texto y fotos reales del taller | Quiénes somos y catálogo |

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
- **2026-11-19 — F4:** el detector de Impeccable marca Inter como tipografía
  sobreusada; se mantiene porque el plan la fija explícitamente. Coste si es erróneo:
  menor diferenciación tipográfica, sin impacto funcional.
- **2026-11-19 — F4.3/F4.5:** la metadata de la home viajó en el commit de contenido
  porque comparte archivo; F4.5 conserva la prueba y la metadata de categoría.
- **2026-11-19 — F5.3:** se eliminaron del seeder el nombre, correo, dirección y
  teléfono reales del administrador heredados de la pescadería; ahora son
  `Administrador` y `admin@example.com`. Coste si es erróneo: ninguno; los datos
  productivos se definen por entorno.
- **2026-11-19 — F5.3:** en móvil el carrito oculta precio y subtotal por línea; el
  resumen y el total siguen visibles. Coste si es erróneo: menos detalle por línea en
  pantallas pequeñas.
- **2026-11-19 — F6.1:** el despliegue se documenta como patrón (`~/infra/apps/<app>`),
  sin rutas, IPs ni subredes del servidor.
- **2026-11-19 — A1:** la venta por talla queda apagada por defecto. Las pruebas que
  ejercitan talla, variantes y stock la encienden en su `setUp`; las nuevas cubren el
  rechazo de ficha, carrito, checkout y reportes. Coste si es erróneo: una función
  latente que no se ve en la tienda.
- **2026-11-19 — A2:** `tipo_prenda` y `publico` viven en el producto y filtran el
  catálogo; las filas heredadas quedan como terno de 2 piezas de caballero.
- **2026-11-19 — A3:** los mínimos se calculan por patrón y por prenda. Un abrigo no
  exige pierna; un terno sí. El patrón se congela en la copia de medidas.
- **2026-11-19 — A4:** la dirección exacta se separa de la pública. Solo se renderiza
  para el dueño de una cita confirmada en el taller y para el admin; una visita a
  domicilio ocupa dos bloques consecutivos con bloqueo transaccional.
- **2026-11-19 — A5:** la fecha de entrega usa días hábiles más `floor(cola /
  capacidad_semanal)` semanas. La cola son líneas a medida en pedidos confirmados o
  en confección; la misma función alimenta la ficha.
- **2026-11-19 — A6:** `precio_hechura` y `precio_tela` se congelan en la línea del
  pedido para que recibo y orden de trabajo no dependan de precios futuros.
- **2026-11-19 — A7:** Inter y Cormorant se sustituyen por Jost y EB Garamond; Cinzel
  queda reservada al sello. La tienda usa negro gala y oro viejo; el panel, recibo y
  orden de trabajo siguen en marfil y tinta. Coste si es erróneo: dos temas que hay
  que mantener, con contraste medido en ambos.
- **2026-11-19 — F5.1:** el catálogo demo deja de tener variantes con stock; siembra
  nueve piezas a medida para los tres públicos. Las pruebas de talla crean sus propias
  variantes.
- **2026-11-19 — A8:** se retiran los tres documentos de la pescadería; siguen en el
  historial de git. La bitácora conserva el plan y la adenda como fuente.
