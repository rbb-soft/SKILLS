# Changelog

Todos los cambios notables de este proyecto se documentan aquí.
Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/).

---

## [v0.8.0] — 2026-06-05

### Added
- Skill `minimax-helper` registrado en `.skill-lock.json` con `source: local`
  (el skill existía en `skills/minimax-helper/` pero no figuraba en el manifest)
- Documentación de nuevos recursos del CLI `mmx` 1.0.16 en `SKILL.md`:
  `speech synthesize`, `image generate`, `video generate`, `music generate`,
  `quota show`, `config show/set/export-schema`, `update`
- Sección "Other Available Resources" en `SKILL.md` con quick-reference de los
  recursos nuevos

### Changed
- `SKILL.md` (minimax-helper) actualizado para `mmx` 1.0.16: bumped reference
  de `v1.0.15+` a `v1.0.16+`; instrucciones de install ahora son pnpm-primary;
  `auth status` documenta el quota snapshot; `text chat` documenta los flags
  nuevos (`--stream`, `--messages-file`, `--max-tokens`, `--temperature`,
  `--top-p`, `--tool`); se documenta el modelo `MiniMax-M2.7-highspeed`
- Frontmatter `description:` de `minimax-helper` ampliada para reflejar las
  capacidades nuevas (speech, image/video/music, quota, update)
- Error Handling: comando de install cambiado de `npm install -g @minimax/mmx`
  a `pnpm add -g @minimax/mmx` (coincide con la instalación real; `npm` no
  resuelve el paquete en el registry público)

### Removed
- Referencia rota a `mmx text models` (el comando fue eliminado en 1.0.16)

---

## [v0.7.0] — 2026-06-01

### Added
- Skill `pdf-extraction` instalado para extracción de texto, tablas y metadata de PDFs

---

## [v0.6.1] — 2026-05-31

### Added
- Regla genérica de referencias cruzadas manifest ↔ documentación en `documentar-version-control`: ahora detecta cuando un componente nuevo en un manifest (`.skill-lock.json`, `package.json`, etc.) falta en lists/trees de archivos `.md` y lo inserta automáticamente

---

## [v0.6.0] — 2026-05-31

### Added
- Symlinks agregados para skills que apuntan a la instalación centralizada en `~/.agents/skills/` (`frontend-design`, `skill-creator`, `web-design-guidelines`)

---

## [v0.5.0] — 2026-05-31

### Added
- Skill `skill-creator` instalado y registrado en `.skill-lock.json`

---

## [v0.4.0] — 2026-05-31

## [v0.3.1] — 2026-05-31

### Changed
- Skill `frontend-design` actualizado a nuevo source: `anthropics/skills`
- Documentación de `frontend-design` simplificada: removidos requisitos de branding y output requirements

### Removed
- Eliminado symlink `skills/frontend-design/frontend-design`

---

## [v0.3.0] — 2026-05-31

### Added
- README.md creado para documentar el propósito y estructura del skill registry
- Skill `documentar-version-control` registrado en `.skill-lock.json` con `source: local`

### Changed
- `.skill-lock.json` ahora incluye todas las entradas de skills presentes en el repositorio

---

## [v0.2.0] — 2026-05-31

### Added
- Skill `documentar-version-control` ahora soporta creación y actualización de `README.md` cuando no existe o tiene secciones de versión/estado/instalación

### Changed
- Regla para README.md: se agregó lógica para crear README.md desde cero si no existe, inferido del nombre del repositorio

---

## [v0.1.0] — 2026-05-31

### Added
- Estado inicial del proyecto: skill registry de Claude Code con skills instalados (find-skills, frontend-design, nielsen-usability-heuristics, seo, vps-checkup)
- Registro de skills en `.skill-lock.json` con metadata de instalación
- Documentación del repositorio en `CLAUDE.md`