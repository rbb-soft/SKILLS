---
name: documentar-version-control
description: >
  Analiza los cambios de un proyecto con git diff (staged y unstaged), documenta automáticamente
  los archivos *.md afectados (actualizando CHANGELOG.md si existe), y luego hace commit y push
  con un mensaje semántico. Usar este skill cuando el usuario pida documentar cambios, actualizar
  el CHANGELOG, registrar una versión, preparar un release, o hacer commit+push de documentación.
  También activar si dicen "documentá los cambios", "registrá la versión", "actualizá el CHANGELOG",
  "commitear la doc", "pushear los cambios", o similares.
---

# Skill: documentar_version_control

Documenta automáticamente los cambios de un proyecto revisando `git diff`, actualiza los archivos
`*.md` correspondientes (especialmente `CHANGELOG.md`), y luego hace commit y push de forma automática.

---

## Flujo completo

```
1. Detectar cambios con git
2. Analizar qué archivos *.md corresponde actualizar
3. Actualizar los *.md según las reglas de documentación (crear CHANGELOG.md si no existe)
4. git add → git commit → git pull --rebase → git push
5. Mostrar resumen final al usuario con lo que se hizo
```

---

## Paso 1 — Detectar cambios con git

```bash
# Ver rama y estado actual
git status
git branch --show-current

# Diff completo (staged + unstaged)
git diff HEAD
```

Capturar:
- Archivos modificados (M), agregados (A), eliminados (D), renombrados (R)
- Contenido del diff por archivo
- Si hay conflictos pendientes → **DETENER y reportar al usuario**

> ⚠️ Si `git diff HEAD` devuelve vacío pero hay archivos en staging, usar `git diff --cached`.

---

## Paso 2 — Analizar qué *.md actualizar

Leer los archivos `.md` existentes en el repo:

```bash
find . -name "*.md" \
  -not -path "./.git/*" \
  -not -name "*.pdf" \
  | sort
```

**Reglas de selección:**

| Situación | Acción |
|-----------|--------|
| Existe `CHANGELOG.md` | Siempre actualizar con la nueva entrada al tope |
| **No existe `CHANGELOG.md`** | **Crearlo desde cero** con la primera entrada (ver formato más abajo) |
| Existe `README.md` con sección `## Versión` o `## Estado` | Actualizar esa sección con la versión nueva |
| **No existe `README.md`** | **Crearlo desde cero** con la estructura mínima (ver formato más abajo) |
| Hay un `.md` específico del módulo modificado | Revisar si necesita actualización |

**Nunca modificar:**
- Archivos `*.pdf`
- Archivos binarios o de imagen
- `.md` dentro de `node_modules/`, `.git/`, `dist/`, `build/`

---

## Paso 3 — Actualizar los *.md

### Regla principal: CHANGELOG.md

Siempre agregar la entrada más nueva **al inicio del archivo**, con este formato exacto:

```markdown
## [vX.Y.Z] — YYYY-MM-DD

### Added
- Descripción concisa de lo agregado

### Changed
- Descripción concisa de lo modificado

### Fixed
- Descripción concisa de correcciones

### Removed
- Descripción concisa de lo eliminado
```

- Omitir las secciones que no aplican (no dejar secciones vacías).
- La versión `vX.Y.Z` se determina así:
  - Si el usuario especificó una versión → usarla
  - Si hay una versión previa en el CHANGELOG → incrementar según tipo de cambio:
    - Nuevas features → incrementar MINOR (`v1.2.0` → `v1.3.0`)
    - Solo fixes/docs → incrementar PATCH (`v1.2.0` → `v1.2.1`)
    - Breaking changes → incrementar MAJOR (`v1.2.0` → `v2.0.0`)
  - Si no hay versión previa → empezar en `v0.1.0`
- La fecha es siempre la del día actual (usar `date +%Y-%m-%d`).

### Crear CHANGELOG.md desde cero

Si no existe `CHANGELOG.md` en el proyecto, crearlo con esta estructura inicial y ubicarlo en la raíz del repo:

```markdown
# Changelog

Todos los cambios notables de este proyecto se documentan aquí.
Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/).

---

## [v0.1.0] — YYYY-MM-DD

### Added
- Estado inicial del proyecto
```

La primera versión siempre es `v0.1.0` salvo que el usuario indique otra.

### Crear README.md desde cero

Si no existe `README.md` en el proyecto, crearlo en la raíz con esta estructura, inferiendo el contenido del nombre del repositorio y los archivos presentes:

```markdown
# <nombre del repositorio>

<Descripción breve inferida del proyecto — 1 o 2 líneas>

## Instalación

> _Completar con los pasos de instalación._

## Uso

> _Completar con ejemplos de uso._

## Versión

vX.Y.Z
```

- El título se toma del nombre del directorio raíz del repo (o del `name` en `package.json` / `pyproject.toml` si existe).
- La descripción se infiere del diff y los archivos del proyecto; si no hay suficiente contexto, dejar un placeholder claro.
- La versión debe coincidir con la entrada recién creada en `CHANGELOG.md`.

### Regla para README.md existente

Buscar en el `README.md` si existe alguna de estas secciones y actualizarla:

| Sección en README.md | Qué actualizar |
|----------------------|----------------|
| `## Versión` o `## Version` | Cambiar el número de versión al nuevo |
| `## Estado` o `## Status` | Actualizar el estado del proyecto si cambió |
| `## Instalación` o `## Uso` | Actualizar si el diff modifica comandos, flags o pasos documentados |
| `## Changelog` (inline) | Agregar entrada igual que en CHANGELOG.md |

Si el `README.md` no tiene ninguna de estas secciones → no modificarlo.
**No reescribir todo el archivo**, solo la sección relevante.

### Regla adicional: Referencias cruzadas manifest ↔ documentación

1. **Identificar archivos "manifest"** en el diff: archivos que listan componentes/dependencias
   - `.skill-lock.json`, `package.json`, `requirements.txt`, `go.mod`, `Cargo.toml`, `*.lock`, `Gemfile`, `composer.json`
   - Cualquier `.json` o archivo de lock en la raíz o subdirectorios comunes

2. **Para cada nombre de componente nuevo o modificado en el manifest:**
   a. Extraer el identificador (ej: `skill-creator`, `my-package`, `module-name`)
   b. Buscar en todos los `.md` del proyecto si ese identificador aparece
   c. Si aparece en una sección de lista/árbol pero no está documentado correctamente:
      - Detectar el formato de la lista (tree `├──`, bullets `-`, tabla, etc.)
      - Insertar el nuevo componente en la posición correcta
      - Mantener el formato existente

3. **Patrones a detectar:**
   | Pattern | Qué hacer |
   |---------|-----------|
   | Tree style `├── name/` | Agregar nuevo item manteniendo indentación y format |
   | Bullet list `- name` | Agregar nuevo item con `- ` prefix |
   | Tabla con columna de nombres | Agregar row manteniendo alignment |
   | Section con nombre como header | No modificar (es documentación, no listing) |

4. **Solo modificar si:**
   - El item está faltando Y
   - El item aparece en el diff del manifest Y
   - La sección es un listing enumerativo (no descripción prose)

### Regla para sincronizar ASCII trees con estructura de archivos

Esta regla detecta trees ASCII en archivos `.md` y los mantiene sincronizados con la estructura real de directorios del proyecto.

**Trigger:** Se ejecuta siempre como paso adicional en "Paso 2 — Analizar qué *.md actualizar", sin necesidad de que el diff toque un manifest.

**1. Detectar blocks tree en archivos `.md`:**
- Buscar patrones: `├──`, `└──`, `│   ├──`, `│   └──`
- El tree puede estar en un bloque de código markdown (```) o en texto plano
- Identificar la ruta base que representa el tree (ej: `skills/` o el directorio padre del `.md`)

**2. Comparar tree vs estructura real:**
- Para cada item en el tree, verificar si el directorio existe realmente
- Detectar:
  - **Items faltantes**: directorios que existen en el filesystem pero no están en el tree
  - **Items obsoletos**: items en el tree cuyo directorio ya no existe

**3. Actualización del tree:**
- Agregar items faltantes en posición alfabética, manteniendo el formato (`├──` para items intermedios, `└──` para el último)
- Eliminar items obsoletos
- Preservar comentarios después del nombre (ej: `# desc` al final de la línea)
- Mantener indentación de 4 espacios por nivel

**4. Formatos a detectar y preservar:**

| Formato | Ejemplo | Acción |
|---------|---------|--------|
| Tree intermedio | `├── find-skills/` | ✅ detectar y preservar |
| Tree final | `└── documentar-version-control/` | ✅ detectar y preservar |
| Con descripción | `├── skill-creator/  # crear skills` | ✅ preservar descripción |
| Nombres desnormalized | `├── find-skills/` | ✅ normalizar a lowercase si el dir existe así |
| Comentarios inline | `├── seo/ #搜索引擎优化` | ✅ preservar |

**5. Condiciones para modificar:**
- El tree tiene items faltantes u obsoletos respecto a la estructura real
- El bloque usa formato tree reconocible (no prose ni bullets simples sin `├──`/`└──`)
- El tree vive en un archivo `.md` que no sea de un submodule externo

**Ejemplo de sync automático:**
```
# BEFORE (README.md tree desactualizado):
skills/
├── find-skills/
├── frontend-design/
└── seo/

# AFTER (después de ejecutar el skill, porque existen:
#   skills/minimax-helper/ y skills/php-api-skeleton/):
skills/
├── find-skills/
├── frontend-design/
├── minimax-helper/
├── php-api-skeleton/
└── seo/
```

### Regla para otros *.md

Buscar en el diff si el cambio afecta funcionalidad documentada en otros `.md`.
Si una sección del `.md` describe algo que fue modificado en el diff → actualizar esa sección.
**No reescribir todo el archivo**, solo la sección relevante.

---

## Paso 4 — Commit, pull y push (automático, sin confirmación)

```bash
# 1. Agregar archivos modificados (incluyendo los *.md actualizados)
git add -A

# 2. Verificar que no haya conflictos antes de commitear
git status

# 3. Hacer el commit con mensaje semántico
git commit -m "<tipo>: <descripción>"

# 4. Pull con rebase para detectar conflictos del remoto ANTES de pushear
git pull --rebase origin <rama-actual>

# 5. Si hay conflictos tras el rebase → DETENER y reportar al usuario
# 6. Si no hay conflictos → push normal
git push origin <rama-actual>
```

> 🚫 **Nunca usar `git push --force`** bajo ninguna circunstancia.

Una vez completado el push, mostrar al usuario un resumen de lo ejecutado:

```
✅ Documentación y push completados
────────────────────────────────────
Archivos *.md actualizados:
  • CHANGELOG.md  → nueva entrada v1.3.0

Commit: docs: actualizar CHANGELOG con entrada v1.3.0
Push:   origin/<rama> ✓
```

---

## Tipos de mensaje de commit

| Prefijo | Cuándo usarlo |
|---------|---------------|
| `feat:` | Se documentan nuevas funcionalidades |
| `fix:` | Se documentan correcciones de bugs |
| `docs:` | Solo cambios en documentación (*.md, CHANGELOG) |
| `refactor:` | Refactors sin cambio de comportamiento observable |

El mensaje debe ser descriptivo. Ejemplos:
- `docs: actualizar CHANGELOG con entrada v1.3.0 — nuevo parser binario`
- `feat: agregar módulo de autenticación y documentar en CHANGELOG`
- `fix: corregir flujo de reintento y registrar en CHANGELOG v1.2.1`

Si el diff mezcla tipos (ej: feat + fix), usar el tipo dominante o el más impactante.

---

## Manejo de errores y casos especiales

| Situación | Acción |
|-----------|--------|
| Conflictos en el remoto (tras pull --rebase) | Abortar con `git rebase --abort`, reportar al usuario con detalle de los archivos en conflicto |
| Repo sin remote configurado | Informar al usuario y omitir el push |
| Rama sin upstream | Usar `git push --set-upstream origin <rama>` |
| Working tree limpio (sin cambios) | Informar al usuario que no hay nada para commitear |
| Usuario no proporcionó versión | Inferir según reglas de semver descritas en Paso 3 |
| CHANGELOG.md no existe | Crearlo desde cero con la primera entrada |

---

## Referencia rápida de comandos git usados

```bash
git status
git branch --show-current
git diff HEAD
git diff --cached
git add -A
git commit -m "..."
git pull --rebase origin <rama>
git push origin <rama>
git push --set-upstream origin <rama>
git rebase --abort          # solo en caso de conflictos
date +%Y-%m-%d              # para la fecha del CHANGELOG
```

---

## Restricciones absolutas

- ❌ Nunca `git push --force`
- ❌ Nunca modificar `*.pdf` ni archivos binarios/imágenes
- ❌ Nunca continuar si hay conflictos sin reportarlos al usuario
- ✅ Ejecutar commit y push automáticamente, sin pedir confirmación
- ✅ Crear `CHANGELOG.md` si no existe, nunca omitir este paso
- ✅ Siempre hacer `pull --rebase` antes del `push`
- ✅ Mostrar resumen de lo ejecutado **al final**, una vez completado todo
