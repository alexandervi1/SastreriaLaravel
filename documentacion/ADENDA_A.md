# Adenda A al plan de la tienda de ternos — solo a medida, taller en casa

> **Para:** el agente que ejecuta `PLAN_TIENDA_TERNOS.md` en `SastreriaLaravel`.
> **Cuándo:** **ahora**. El plan F0–F6.1 ya está terminado, pero esta adenda no se aplicó. Ejecuta en este orden, sobre la rama `mejora/ternos`: A1 → A6, luego A7, luego los «Ajustes a F4 y F5» del final (un commit por punto: contenido F4.3 y catálogo demo F5.1), y por último A8 → A10. Copia este archivo a `documentacion/ADENDA_A.md`.
> **Ojo:** F4 ya se hizo con la paleta provisional del plan y con contenido de venta por talla. Las secciones «Ajustes a F4 y F5» y A7 **corrigen ese trabajo ya hecho**: no son opcionales.
> **Prevalece** sobre el plan donde se contradigan. Las reglas de la sección 0 del plan siguen vigentes: las migraciones son inmutables (todo va en migraciones nuevas), hay una subfase por commit, la puerta de calidad va tras cada subfase y la bitácora sigue al día.

## Contexto que cambia

- La sastre trabaja **sola, desde su casa en Riobamba** (sala con máquina de coser y overlock). Tiene 30 años de oficio; su especialidad son **sacos, americanas, chaquetas y abrigos**. Confecciona para **caballero, dama y niño**.
- **Solo vende a medida.** La venta por talla con stock (F1.2–F1.4) **no se usa por ahora**, pero el código **no se borra**: queda detrás de un interruptor, por si en el futuro vende piezas confeccionadas.
- **Precio orientativo:** hechura de 80 a 100 USD, con la tela como recargo aparte (ya soportado por `tela.recargo`).

---

## A1 — Interruptor de venta por talla (apagado por defecto)
- **Configuración:** clave `venta_por_talla` (`0` o `1`, por defecto `0`), con `Configuracion::ventaPorTallaActiva(): bool` y un checkbox en **Configuración → Negocio**.
- **Migración nueva:** `producto.vende_por_talla` pasa a default `false`, y `vende_a_medida` a default `true`. Actualiza las filas existentes en el `up()`. Implementa `down()`.
- **Con el interruptor apagado:**
  - La ficha pública muestra **solo** el encargo a medida, sin pestañas.
  - El admin oculta la sección "Tallas y colores", el campo "Vende por talla", los reportes "Bajo stock" y "Stock por categoría" (menú y rutas: un 404 es aceptable) y la tarjeta de stock del dashboard.
  - `POST /carrito/agregar` con `variante_id` responde con error de validación ("La venta por talla no está disponible").
  - `OrderService::processOrder` rechaza las líneas de tipo `talla`.
  - La guía de tallas de F4.3 se sustituye por la guía de medidas (A3).
- **Pruebas:**
  - Las pruebas existentes de talla, variantes y stock **siguen igual**, activando el interruptor en su `setUp`.
  - Pruebas nuevas con el interruptor apagado: la ficha sin pestaña de talla, el carrito rechaza una variante, el checkout rechaza una línea de talla, y los reportes de stock no aparecen en el menú.
- **Commit:** `feat(config): venta por talla tras un interruptor, apagada por defecto`

## A2 — Tipo de prenda y público
- **Migración nueva** en `producto`: `tipo_prenda` string(30) y `publico` string(10).
  - `tipo_prenda` admite: `terno_2p`, `terno_3p`, `saco`, `chaqueta`, `abrigo`, `chaleco`, `pantalon` o `falda`.
  - `publico` admite: `caballero`, `dama` o `nino`.
  - Las filas existentes quedan como `terno_2p` y `caballero`.
- Constantes con etiquetas legibles en el modelo (p. ej. `terno_3p` → "Terno de 3 piezas", `nino` → "Niño"). Validación con `Rule::in`.
- **Catálogo público:** filtros por público y por tipo de prenda. En la home, accesos "Caballero", "Dama" y "Niño".
- **Commit:** `feat(catalogo): tipo de prenda y público`

## A3 — Medidas por patrón (caballero, dama, niño)
- **Migración nueva** en `medida_cliente`:
  - `patron` string(10), default `caballero`.
  - Columnas decimal(5,1) nullable: `busto`, `talle_delantero`, `talle_espalda` y `largo_falda`.
- **Medidas mínimas para usar un perfil en un pedido** (regla en el servicio, como en F2.2):
  - **caballero:** `pecho`, `cintura`, `hombros`, `largo_manga`, `largo_saco`, y además `entrepierna` y `largo_pantalon` si la prenda lleva pantalón.
  - **dama:** `busto`, `cintura`, `cadera`, `hombros`, `largo_manga`, `talle_delantero`, `talle_espalda`, `largo_saco`, y además `largo_pantalon` o `largo_falda` según la prenda.
  - **nino:** `pecho`, `cintura`, `largo_manga`, `largo_saco` y `largo_pantalon`.
  - **Solo saco, chaqueta, abrigo o chaleco:** no se exigen medidas de pantalón ni de falda. Usa `tipo_prenda` de A2.
- **Formulario `/mis-medidas`:** se elige el patrón primero y se muestran solo sus campos (JavaScript mínimo, y sin JavaScript se ven todos).
- **Orden de trabajo:** imprime solo las medidas del patrón, con el patrón y el tipo de prenda en la cabecera.
- **Pruebas:**
  - Mínimos por patrón.
  - Un abrigo de dama no exige `largo_falda`.
  - La copia de medidas incluye `patron`.
  - La orden de trabajo de dama muestra `busto` y no `pecho`.
- **Commit:** `feat(medidas): patrones de caballero, dama y niño`

## A4 — Taller en casa: privacidad y citas a domicilio
- **La dirección real del taller nunca es pública.** Se separa en dos claves:
  - `negocio_direccion_publica` (p. ej. "Riobamba, sector [COMPLETAR]"), visible en la home, el footer, "Quiénes somos" y los recibos.
  - `negocio_direccion` (la exacta), visible **solo** para el cliente dueño de una cita **Confirmada** con `lugar = taller`, en `/citas` y en el texto de WhatsApp de esa cita, y para el admin.
  - Prueba: la dirección exacta no aparece en ninguna página pública ni en citas `Solicitada` o de otro cliente.
- **Migración nueva** en `cita`: `lugar` string(10) default `taller` (`taller` o `domicilio`) y `direccion_cliente` string(255) nullable, obligatoria si `domicilio`.
- **Configuración:** `cita_domicilio_activa` (default `1`) y `cita_domicilio_zona` (texto, p. ej. "Solo dentro de Riobamba urbano").
- La reserva permite elegir el lugar. La agenda del admin muestra el lugar y la dirección.
- **Duración:** una cita a domicilio ocupa **dos huecos** consecutivos, por el traslado. `AgendaService` debe comprobar ambos huecos con el mismo bloqueo transaccional, y una prueba lo cubre.
- **Commit:** `feat(citas): privacidad del taller en casa y citas a domicilio`

## A5 — Capacidad de una sola persona
- **Configuración:** `capacidad_semanal` (prendas a medida por semana, default `3`).
- **`fecha_entrega_estimada`** (F2.5) = hoy + `dias_confeccion` días hábiles + `floor(cola / capacidad_semanal)` semanas.
  - `cola` es el número de líneas a medida en pedidos `Confirmado` o `En confección`.
  - Centraliza el cálculo en un método de servicio y úsalo también para la estimación de la ficha: "Entrega estimada: desde el <fecha>".
- **Pruebas:** cola 0, cola igual a la capacidad (suma una semana) y fin de semana intermedio.
- **Commit:** `feat(pedidos): fecha de entrega según la capacidad del taller`

## A6 — Precio "hechura + tela"
- Ficha y tarjetas: "Hechura desde $<precio_medida>" y, al elegir tela, "Hechura $X + tela $Y = $Z (IVA incluido o no, según el desglose actual de `PricingService`)".
- Mismo desglose en el carrito, el recibo y la orden de trabajo.
- **Commit:** `feat(tienda): precio desglosado en hechura y tela`

## A7 — Marca Entretela (reemplaza la paleta provisional de F4.1 y F4.2)
- **Fuente de verdad:** `C:\Users\Workstation 2040\Documents\Entretela-marca\LEEME.md` y los SVG de esa carpeta. Tablero visual: https://claude.ai/artifact/CCLJRU44wgbTQQLvaFSKRX
- **Nombre:** `<NOMBRE_NEGOCIO>` = **Entretela**. Lema: «Lo que no se ve es lo que da forma.» Bajada: «Sastrería de gala · Riobamba». Reemplaza el marcador en seeder, `.env.example` (`APP_NAME`), `AppServiceProvider`, `Configuracion`, títulos y metadatos.
- **Archivos:**
  - copia `favicon.svg` a `public/favicon.svg`;
  - copia `colibri-oro.svg`, `colibri-icono-oro.svg`, `colibri-tinta.svg` y `sello-negro-oro.svg` a `public/img/negocio/`;
  - `negocio_logo` por defecto = `img/negocio/colibri-oro.svg`;
  - borra el `logo-placeholder.svg` y los iconos provisionales que ya no se usen.
- **Paleta:** negro gala y oro viejo (tabla del `LEEME.md`). En `public/css/style.css` sustituye los primitivos `--brand-*` y los tokens semánticos.
  - **La tienda pública es tema oscuro de gala:** fondo `#121110`, superficies `#1C1A18`, texto `#F3EFE6`, secundario `#9A948A`, acento y botón principal `#A88E5C` con texto `#121110`, texto dorado pequeño `#CDB991`.
  - **El panel de administración, el recibo y la orden de trabajo** se quedan en fondo claro: marfil `#F3EFE6` y tinta `#2A2622`, con `colibri-tinta.svg`. Detalles dorados en `#7A6438`, porque `#A88E5C` sobre marfil no llega al contraste mínimo.
  - Mide de nuevo los contrastes de los dos temas y reemplaza la tabla de la bitácora.
- **Tipografía:** sustituye Inter y Cormorant Garamond por:
  - **Jost 300**, para el nombre ENTRETELA en mayúsculas con `letter-spacing: .3em`, el menú y el texto;
  - **EB Garamond**, para los títulos;
  - **Cinzel 600**, solo si se muestra el sello con texto.

  Todas desde Google Fonts con `display=swap`.
- **Cabecera de la tienda:** `colibri-icono-oro.svg` junto a ENTRETELA. La portada sigue el ejemplo «Así se vería la portada» del tablero:
  - titular «Tu noche, *a medida.*»;
  - botón dorado «Reservar cita de medidas» y botón de contorno «Ver modelos»;
  - **sin precios en la portada**;
  - un menú de 4 a 5 opciones: A medida, Telas, El taller, Citas.
- **Pruebas:**
  - `PagesSmokeTest` comprueba que la portada contiene «Entretela» y no contiene `<NOMBRE_NEGOCIO>`;
  - una prueba recorre `resources/views` y falla si encuentra `<NOMBRE_NEGOCIO>`, «pescader» o «Don Walter».
- **Commit:** `feat(marca): identidad Entretela, tema de gala y colibrí`

## A8 — Limpieza de documentación heredada
- Borra `documentacion/PLAN_BITACORA.md`, `documentacion/PLAN_MEJORA_PRODUCCION.md` y `documentacion/SRS_PescaderiaLaravel.md` (F0.3 lo pedía y quedaron). Siguen disponibles en el historial de git.
- En la tabla «Datos pendientes de la dueña» de la bitácora:
  - marca como resueltos `<NOMBRE_NEGOCIO>` (Entretela) y `<PALETA>` (negro y oro viejo);
  - elimina `<TALLAS>`, que ya no aplica con la venta solo a medida.
- **Commit:** `docs: retira documentos de la pescadería y actualiza pendientes`

## A9 — Puerta Docker final
- Ejecuta la verificación en contenedor (sección 7 del plan) con todos los flujos: básico, solo medida (interruptor apagado), talla (interruptor encendido por SQL), cita y cita a domicilio.
- Comprueba además que:
  - la dirección exacta del taller **no aparece** en ninguna página pública;
  - `/favicon.svg` responde 200.
- Pega la salida en la bitácora.

## A10 — Subir a GitHub
- `git push -u origin mejora/ternos` (**solo** a `origin` = SastreriaLaravel; nunca al remoto `pescaderia`).
- No abras el PR ni fusiones: lo revisa la persona. Detente aquí y entrega el resumen de A1–A10 con las verificaciones.

## Ajustes a F4 y F5 del plan (corrigen lo ya hecho)
- **F4.1:** sustituido por A7.
- **F4.3:**
  - "Así funciona" en 4 pasos: eliges la prenda y la tela → tomamos tus medidas en el taller o en tu casa → prueba con hilvanes → entrega.
  - Ayuda: guía de medidas por patrón, en lugar de la guía de tallas.
  - Políticas por defecto, marcadas `[REVISAR]`: las prendas a medida no admiten devolución; los ajustes son gratuitos dentro de 15 días; se pide un anticipo para comenzar la confección (`[COMPLETAR %]`).
  - "Quiénes somos" con marcadores: `[COMPLETAR: aprendiz en la sastrería familiar, 30 años de oficio en sacos y abrigos, licenciada en Diseño de Modas]`. **No pongas el nombre real de la sastre** en el sitio: la marca será distinta de su nombre.
- **F5.1:** el catálogo demo es **solo a medida**. Deben aparecer los tres públicos y, como mínimo, un terno de 2 piezas y uno de 3 piezas de caballero, un terno de dama, un abrigo de dama, un saco de caballero y un terno de niño. Sin variantes con stock.
- **Verificación en contenedor:** el flujo "talla" se ejecuta con `venta_por_talla=1` puesto por SQL en el contenedor desechable, y se añade un flujo "solo medida" con el interruptor apagado (caso por defecto).
