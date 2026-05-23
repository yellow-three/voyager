# JSON BREAD Format Reference

> Voyager v3 stores BREAD (Browse, Read, Edit, Add, Delete) definitions as JSON files in `storage/voyager/breads/`. This document describes the JSON format.

---

## File Location

```
storage/voyager/breads/
├── users.json
├── posts.json
└── categories.json
```

Each file corresponds to one BREAD definition (one database table / Eloquent model).

---

## Top-Level Structure

```json
{
    "slug": "posts",
    "name": "Posts",
    "display_name": "Blog Posts",
    "display_name_plural": "Blog Posts",
    "model": "App\\Models\\Post",
    "controller": null,
    "policy_name": null,
    "icon": "news",
    "global_search_field": "title",
    "description": "Manage blog posts and articles",
    "generate_permissions": true,
    "server_side": false,
    "layouts": [...]
}
```

### Top-Level Fields

| Field | Type | Required | Description |
|---|---|---|---|
| `slug` | string | ✅ | URL slug, must match filename |
| `name` | string | ✅ | Internal identifier |
| `display_name` | string | ✅ | Singular display name (UI) |
| `display_name_plural` | string | — | Plural display name |
| `model` | string | ✅ | Fully qualified Eloquent model class |
| `controller` | string\|null | — | Custom controller class (null = use default) |
| `policy_name` | string\|null | — | Custom policy class |
| `icon` | string | — | Icon name (Heroicons) |
| `global_search_field` | string | — | Column used for global search |
| `description` | string | — | Description shown in admin |
| `generate_permissions` | bool | — | Auto-generate CRUD permissions |
| `server_side` | bool | — | Enable server-side pagination |
| `layouts` | array | ✅ | Array of layout definitions |

---

## Layout Structure

```json
{
    "name": "Default List",
    "type": "list",
    "scope": null,
    "formfields": [...]
}
```

### Layout Types

| Type | Description |
|---|---|
| `list` | Browse/index view |
| `view` | Read/show view |
| `edit` | Edit/update view |
| `add` | Create/store view |

### Layout Fields

| Field | Type | Description |
|---|---|---|
| `name` | string | Human-readable layout name |
| `type` | string | Layout type (`list`, `view`, `edit`, `add`) |
| `scope` | string\|null | Optional Eloquent scope (e.g., `published`) |
| `formfields` | array | Array of formfield definitions |

---

## FormField Definition

```json
{
    "column": "title",
    "type": "text",
    "display_name": "Post Title",
    "required": true,
    "searchable": true,
    "browse": true,
    "read": true,
    "edit": true,
    "add": true,
    "delete": false,
    "details": {},
    "validation": "required|string|max:255",
    "order": 1
}
```

### FormField Fields

| Field | Type | Default | Description |
|---|---|---|---|
| `column` | string | — | Database column or accessor name |
| `type` | string | — | FormField handler codename |
| `display_name` | string | — | Label shown in UI |
| `required` | bool | `false` | Whether field is required |
| `searchable` | bool | `false` | Include in search |
| `browse` | bool | `true` | Show in list view |
| `read` | bool | `true` | Show in detail view |
| `edit` | bool | `true` | Show in edit form |
| `add` | bool | `true` | Show in create form |
| `delete` | bool | `false` | Used for soft delete flag |
| `details` | object | `{}` | Handler-specific options |
| `validation` | string | — | Laravel validation rules |
| `order` | int | — | Display order |

---

## Available FormField Types

| Type | Handler | Description |
|---|---|---|
| `text` | TextHandler | Single-line text |
| `text_area` | TextareaHandler | Multi-line text |
| `number` | NumberHandler | Numeric input |
| `password` | PasswordHandler | Password (hashed on save) |
| `checkbox` | CheckboxHandler | Boolean checkbox |
| `radio` | RadioHandler | Radio button group |
| `toggle` | ToggleHandler | Toggle switch |
| `select_dropdown` | SelectHandler | Single-select dropdown |
| `select_multiple` | SelectMultipleHandler | Multi-select |
| `image` | ImageHandler | Single image upload |
| `multiple_images` | MultipleImagesHandler | Multiple image upload |
| `media_picker` | MediaPickerHandler | Media manager picker |
| `file` | FileHandler | File upload |
| `rich_text_box` | RichTextBoxHandler | TinyMCE editor |
| `code_editor` | CodeEditorHandler | CodeMirror 6 |
| `markdown` | MarkdownEditorHandler | Markdown editor |
| `date` | DateHandler | Date picker |
| `time` | TimeHandler | Time picker |
| `timestamp` | TimestampHandler | DateTime picker |
| `hidden` | HiddenHandler | Hidden field |
| `coordinates` | CoordinatesHandler | Map coordinates |
| `color` | ColorHandler | Color picker |
| `slug` | SlugHandler | Auto-generated slug |
| `tags` | TagsHandler | Tag input |
| `simple_array` | SimpleArrayHandler | Comma-separated array |
| `repeater` | RepeaterHandler | Repeatable field group |
| `slider` | SliderHandler | Range slider |

---

## Full Example

`storage/voyager/breads/posts.json`:

```json
{
    "slug": "posts",
    "name": "Posts",
    "display_name": "Post",
    "display_name_plural": "Posts",
    "model": "App\\Models\\Post",
    "controller": null,
    "policy_name": null,
    "icon": "news",
    "global_search_field": "title",
    "generate_permissions": true,
    "server_side": false,
    "layouts": [
        {
            "name": "Default List",
            "type": "list",
            "scope": null,
            "formfields": [
                { "column": "image",      "type": "image",    "display_name": "Image",      "browse": true, "read": true,  "edit": true,  "add": true,  "searchable": false },
                { "column": "title",      "type": "text",     "display_name": "Title",      "browse": true, "read": true,  "edit": true,  "add": true,  "searchable": true  },
                { "column": "category",   "type": "select_dropdown", "display_name": "Category", "browse": true, "read": true, "edit": true, "add": true, "searchable": true },
                { "column": "status",     "type": "select_dropdown", "display_name": "Status",   "browse": true, "read": false, "edit": true, "add": true, "searchable": false, "details": { "options": { "DRAFT": "Draft", "PUBLISHED": "Published" } } },
                { "column": "created_at", "type": "timestamp", "display_name": "Created At", "browse": true, "read": false, "edit": false, "add": false, "searchable": false }
            ]
        },
        {
            "name": "Default Edit/Add",
            "type": "edit",
            "scope": null,
            "formfields": [
                { "column": "title",   "type": "text",         "display_name": "Title",   "required": true, "validation": "required|string|max:255" },
                { "column": "slug",    "type": "slug",         "display_name": "Slug",    "details": { "from": "title" } },
                { "column": "body",    "type": "rich_text_box","display_name": "Content", "required": true },
                { "column": "image",   "type": "image",        "display_name": "Image",   "required": false },
                { "column": "status",  "type": "select_dropdown","display_name": "Status","details": { "options": { "DRAFT": "Draft", "PUBLISHED": "Published" } } }
            ]
        }
    ]
}
```

---

## Backup / Rollback

When saving a BREAD definition, Voyager automatically creates a timestamped backup:

```
storage/voyager/breads/
├── posts.json
└── posts.backup.2025-05-24@10-30-00.json
```

To restore:
```bash
cp storage/voyager/breads/posts.backup.2025-05-24@10-30-00.json storage/voyager/breads/posts.json
```

Or use the import command:
```bash
php artisan voyager:import-breads --slug=posts
```
