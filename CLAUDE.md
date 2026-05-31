# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Purpose

This is a **Claude Code skill registry** — a manifest of installed agent capabilities/plugins. It is not a software application.

- `.skill-lock.json` — installed skills manifest (tracks skill name, source repo, install date, update timestamps)
- `skills/` — parent directory containing individual skill subdirectories

## Installed Skills

| Skill | Source |
|-------|--------|
| find-skills | vercel-labs/skills |
| frontend-design | bytedance/deer-flow |
| nielsen-usability-heuristics | dembrandt/dembrandt-skills |
| seo | addyosmani/web-quality-skills |
| vps-checkup | jmerta/codex-skills |

## Adding a New Skill

Skills are installed via the `/skills` command or by manually creating a subdirectory in `skills/` with a `SKILL.md` file containing:
- `name:` — skill identifier
- `description:` — what the skill does
- Implementation content

## No Build System

This directory has no `package.json`, Makefile, or build automation. It is a configuration directory only.