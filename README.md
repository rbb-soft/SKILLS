# SKILLS

Registry de skills para Claude Code — agent capabilities/plugins instalados en este repositorio.

## Descripción

Este repositorio contiene un conjunto de skills que extienden las capacidades del agent Claude Code. Cada skill está contenido en su propio subdirectorio dentro de `skills/`.

## Estructura

```
skills/
├── find-skills/              # Buscar e instalar skills adicionales
├── frontend-design/          # Diseño de interfaces frontend
├── nielsen-usability-heuristics/  # Evaluación de usabilidad
├── seo/                      # Optimización para buscadores
├── vps-checkup/              # Health check de VPS Ubuntu/Docker
├── web-design-guidelines/    # Revisión de diseño web y accesibilidad
└── documentar-version-control/   # Documentación automática y versionado
```

## Agregar un nuevo skill

Los skills se instalan via el comando `/skills` o creando manualmente un subdirectorio en `skills/` con un archivo `SKILL.md` que contenga:
- `name:` — identificador del skill
- `description:` — qué hace el skill

## Archivos principales

- `.skill-lock.json` — manifest de skills instalados (nombre, source, fecha de instalación)
- `CLAUDE.md` — guía para Claude Code trabajando en este repo
- `CHANGELOG.md` — historial de cambios del proyecto

## Versión

v0.4.0