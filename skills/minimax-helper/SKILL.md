---
name: minimax-helper
description: >
  Augment Claude with MiniMax capabilities — image understanding, web search,
  and text generation. Use when the user asks to analyze an image, search the
  web for current information, generate text with MiniMax models, or upload
  files as context. Wraps the `mmx` CLI (MiniMax v1.0.15+).
---

# MiniMax Helper Skill

## When to Use This Skill

Invoke this skill when the task involves any of:

- **Image analysis**: extracting text from screenshots, identifying objects/scenes,
  understanding diagrams, describing photos, reading charts
- **Web search**: the user needs current information beyond Claude's training cutoff,
  fact-checking, recent news, live data
- **Text generation**: drafting content, code generation, translation, summarization
  using MiniMax models as an alternative or complement to Claude
- **File upload**: sending local files (images, documents) to MiniMax storage for
  use as context in subsequent vision or chat calls

---

## Prerequisites

### CLI Location

The `mmx` CLI must be installed and accessible. Default path:

```
/home/richard/.local/share/pnpm/bin/mmx
```

Set a shell alias or use the full path. To check availability:

```bash
mmx --version
# or
/home/richard/.local/share/pnpm/bin/mmx --version
```

If not found, install with:

```bash
npm install -g @minimax/mmx
# or
pnpm add -g @minimax/mmx
```

### Authentication

Log in once before using any command:

```bash
mmx auth login
```

Verify auth status (use this at the start of every agent session):

```bash
mmx auth status --output json --non-interactive
```

Expected output when authenticated:

```json
{
  "authenticated": true,
  "user": { "email": "...", "id": "..." }
}
```

If `authenticated` is `false` or the command errors, stop and tell the user:
> "MiniMax is not authenticated. Please run `mmx auth login` in your terminal."

---

## Tools (Commands)

### 1. `mmx_vision_describe` — Image Analysis

Analyzes images using the MiniMax Vision Language Model.

**Use for:**
- Extracting text from screenshots or scanned documents (OCR-like)
- Identifying objects, people, scenes in photos
- Understanding architecture diagrams, flowcharts, wireframes
- Analyzing charts, graphs, or data visualizations
- Describing UI screenshots for accessibility or documentation

**Command (local file):**

```bash
mmx vision describe \
  --image "<absolute-path-to-image>" \
  --prompt "<your question about the image>" \
  --output json \
  --non-interactive
```

**Command (URL):**

```bash
mmx vision describe \
  --image "<https://example.com/image.jpg>" \
  --prompt "<your question about the image>" \
  --output json \
  --non-interactive
```

**Command (uploaded file via file_id):**

```bash
mmx vision describe \
  --file-id "<file_id-from-upload>" \
  --prompt "<your question>" \
  --output json \
  --non-interactive
```

**Output field to extract:** `description`

```json
{
  "description": "The image shows a React component tree with..."
}
```

**Supported formats:** PNG, JPG, JPEG, WebP, GIF (static)

---

### 2. `mmx_search_query` — Web Search

Performs a live web search via MiniMax. Returns organic results with titles,
URLs, snippets, and publication dates.

**Use for:**
- Current events, news, recent releases
- Looking up documentation versions, changelogs, package versions
- Fact-checking information that may have changed
- Finding official sources, pricing, availability

**Command:**

```bash
mmx search query \
  --q "<search query>" \
  --output json \
  --non-interactive
```

**Output fields to extract from `organic[]`:**

```json
{
  "organic": [
    {
      "title": "Result title",
      "link": "https://...",
      "snippet": "Short description of the page...",
      "date": "2024-01-15"
    }
  ]
}
```

**Presentation pattern:** Format results as a numbered list with title, URL,
and snippet. Example:

```
1. **Result Title** — https://example.com
   Short description snippet here. (2024-01-15)
```

---

### 3. `mmx_text_chat` — Text Generation

Generates text using MiniMax language models. Use as a complement to Claude
for tasks requiring a second model opinion, or when the user explicitly requests
MiniMax output.

**Use for:**
- Generating alternative drafts or rewrites
- Producing content in specific formats/styles
- Code generation with MiniMax models
- Summarization, translation, Q&A

**Command:**

```bash
mmx text chat \
  --message "<user message or prompt>" \
  --model MiniMax-M2.7 \
  --system "<optional system prompt>" \
  --output json \
  --non-interactive
```

**Output field to extract:** `choices[0].message.content`

```json
{
  "choices": [
    {
      "message": {
        "role": "assistant",
        "content": "Generated text here..."
      }
    }
  ]
}
```

**Available models:**
- `MiniMax-M2.7` — Default, good balance of speed and quality
- Check `mmx text models --output json --non-interactive` for the full list

---

### 4. `mmx_file_upload` — File Upload

Uploads a local file to MiniMax storage and returns a `file_id` for use in
subsequent vision or chat calls. Required when you need to analyze a local
file that is too large to pass inline, or when you plan to reference it
multiple times.

**Use for:**
- Uploading local images before vision analysis
- Sending documents as context for chat
- Batch processing: upload once, reference multiple times

**Command:**

```bash
mmx file upload \
  --file "<absolute-path-to-file>" \
  --purpose vision \
  --output json \
  --non-interactive
```

**Output field to extract:** `file_id`

```json
{
  "file_id": "file_abc123xyz",
  "filename": "diagram.png",
  "size": 204800,
  "purpose": "vision"
}
```

**After upload:** Use the returned `file_id` with `mmx vision describe --file-id`
or in chat messages as a file reference.

---

## Workflows

### Workflow A: Analyze a Local Image

```bash
# Step 1: Describe the image
mmx vision describe \
  --image "/path/to/screenshot.png" \
  --prompt "What does this UI show? List all visible buttons and fields." \
  --output json \
  --non-interactive

# Extract: .description
```

### Workflow B: Analyze a Remote Image (URL)

```bash
mmx vision describe \
  --image "https://example.com/diagram.jpg" \
  --prompt "Explain the architecture shown in this diagram." \
  --output json \
  --non-interactive
```

### Workflow C: Web Search → Natural Language Answer

```bash
# Step 1: Search
mmx search query \
  --q "latest stable version of Node.js 2024" \
  --output json \
  --non-interactive

# Step 2: Parse organic[] array
# Step 3: Synthesize answer from titles + snippets + links
```

### Workflow D: Upload File → Vision Analysis

```bash
# Step 1: Upload
mmx file upload \
  --file "/home/user/design.png" \
  --purpose vision \
  --output json \
  --non-interactive
# → extract file_id: "file_abc123"

# Step 2: Analyze with file_id
mmx vision describe \
  --file-id "file_abc123" \
  --prompt "Describe the design patterns used in this mockup." \
  --output json \
  --non-interactive
```

### Workflow E: Multi-step Research

```bash
# Step 1: Search for context
mmx search query --q "<topic>" --output json --non-interactive
# → collect top 3 snippets

# Step 2: Generate a synthesis using MiniMax text
mmx text chat \
  --message "Summarize these search results: <paste snippets>" \
  --model MiniMax-M2.7 \
  --system "You are a research assistant. Be concise and cite sources." \
  --output json \
  --non-interactive
```

---

## Output Parsing Reference

| Command | JSON field to extract |
|---|---|
| `vision describe` | `.description` |
| `search query` | `.organic[].title`, `.organic[].link`, `.organic[].snippet`, `.organic[].date` |
| `text chat` | `.choices[0].message.content` |
| `file upload` | `.file_id` |
| `auth status` | `.authenticated` |

**Parsing pattern (bash):**

```bash
OUTPUT=$(mmx vision describe --image "$IMG" --prompt "$Q" --output json --non-interactive)
DESCRIPTION=$(echo "$OUTPUT" | python3 -c "import sys,json; print(json.load(sys.stdin)['description'])")
```

Or with `jq`:

```bash
OUTPUT=$(mmx search query --q "$QUERY" --output json --non-interactive)
echo "$OUTPUT" | jq -r '.organic[] | "**\(.title)**\n\(.link)\n\(.snippet)\n"'
```

---

## Error Handling

| Condition | Detection | Response |
|---|---|---|
| Not authenticated | `auth status` returns `{"authenticated": false}` or exit code ≠ 0 | Tell user: "Run `mmx auth login` first" |
| `mmx` not found | `which mmx` returns empty / exit 127 | "mmx CLI not found. Install: `npm install -g @minimax/mmx`" |
| File not found | Exit code ≠ 0, stderr contains "not found" or "ENOENT" | "File not found: `<path>`. Check the path and try again." |
| Unsupported image format | stderr contains "unsupported format" | "Unsupported format. Use PNG, JPG, JPEG, or WebP." |
| Rate limit | HTTP 429 / stderr contains "rate limit" | "Rate limit reached. Wait a moment and retry." |
| Network error | stderr contains "ECONNREFUSED", "timeout", "network" | "Network error contacting MiniMax. Check your connection." |
| Invalid JSON output | `json.JSONDecodeError` or `jq` parse failure | Log raw output, tell user: "Unexpected response from mmx. Raw output: `<first 200 chars>`" |
| Auth token expired | `authenticated: false` after previously working | "MiniMax session expired. Run `mmx auth login` to re-authenticate." |

**Always check auth first** at the start of any workflow:

```bash
mmx auth status --output json --non-interactive
```

---

## Notes & Best Practices

1. **Always use `--output json --non-interactive`** for all commands in agent/automation contexts. This ensures parseable output and prevents interactive prompts from blocking execution.

2. **Use absolute paths** for `--image` and `--file`. Relative paths may fail depending on the working directory.

3. **Vision prompt quality matters**: Be specific in `--prompt`. Instead of "describe this", use "List all text visible in this screenshot" or "Identify the database tables and their relationships in this diagram."

4. **Search query optimization**: Keep queries concise and specific. For technical topics, include version numbers or dates: `"React 18 concurrent mode release date"`.

5. **File upload is persistent**: Uploaded files remain in MiniMax storage. Reuse `file_id` values across multiple calls within a session to avoid redundant uploads.

6. **Model selection for text chat**: `MiniMax-M2.7` is the default. For tasks requiring longer context or higher quality, check available models with `mmx text models`.

7. **Combining tools**: The most powerful workflows combine search (for current data) + text chat (for synthesis) + vision (for image context) in sequence.
