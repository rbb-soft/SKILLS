# Changelog

Todos los cambios notables de este proyecto se documentan aquí.
Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/).

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