# Product Requirement Document (PRD) — DAM Studio (Production Edition)

## 1. Project Summary & System Vision
**DAM Studio** is a 3D asset management platform, interactive gallery, and automated web-based conversion system inspired by the best mechanics of *Sketchfab*. The entire interface is designed following *Apple Design System* principles, prioritizing product visuals (photography-first), clean, and minimalist aesthetics.

This platform solves the core problem faced by 3D creators who want to showcase or sell raw files without forcing clients or casual visitors to download massive files or install specialized 3D software (like Blender, Maya, or 3ds Max). The platform implements a **Dual-Path Storage Architecture**:
* **Viewer Path (Compressed):** Raw files (`.blend`, `.fbx`, `.obj`) are automatically converted through a background worker pipeline into a lightweight, responsive `.glb` binary format for interactive rendering on a WebGL canvas (Three.js) in the browser.
* **Master Path (Download/Marketplace):** The original raw files, along with all their dependencies (texture maps, baking data), are stored in `.zip` format on a secure backend server, protected by strict access control (ACL) for download or purchase purposes.

---

## 2. Feature Scope & Page Specifications

### A. Landing Page (Pre-Login) — A Museum-Style Public Showcase
* **Interactive Hero Section:** A 100vh canvas area at the very top displaying one curated 3D model, fully interactive. General visitors can immediately interact (*click-and-hold to orbit, scroll to zoom*) before logging in. Follows the *"Show, Don't Tell"* principle.
* **The Discovery Grid (Public Gallery):** A multidimensional grid displaying public assets with `completed` status that have been curated by an Admin. Hovering over an asset card triggers a mini 360-degree turntable preview animation automatically.
* **Horizontal Tag Filter System (Asynchronous):** A horizontally scrollable row of pill-shaped tag buttons (e.g., `#Animals`, `#Vehicles`, `#LowPoly`). Clicking a tag instantly updates the grid data in real-time via client-side state, without triggering a full page reload (*zero hard-refresh*).
* **Guest Access Restriction (Guest Gateway):** General visitors can enter the detail page to view visuals and inspect the model structure. However, if they click a transactional action button (such as downloading the master file or social features), the system immediately displays an overlay directing them to sign up.

### B. Main Dashboard (Post-Login) — The Creator's Workspace
* **Dropzone Pipeline:** A dominant drag-and-drop file area on the dashboard page. Once a `.blend`, `.fbx`, or `.obj` file is dropped, the frontend splits the file into chunks (*chunk upload*) and displays a real-time progress bar and transfer speed.
* **Shimmering Skeleton State:** While the background worker performs conversion on the backend, the asset card in the creator's dashboard displays a pulsing placeholder animation (*skeleton view*). This gives a clear visual signal that the server is actively processing their file.
* **Smart Tag Auto-Suggestion:** A tag input field integrated with asynchronous database search. If a creator types `ani`, the system automatically suggests existing tags like `#Animals` to prevent tag fragmentation or duplication (such as splitting `#animal` and `#animals`).
* **Instant Visibility Control:** Provides a toggle switch for creators to instantly change an asset's access status between `Private` (personal storage) or `Public` (visible in the global community feed).

### C. Detail Page & Interactive Viewport — High-Level Inspection
* **Floating Three.js Control Bar:** A floating, transparent control menu positioned at the bottom of the 3D viewer canvas:
    * *Mesh Wireframe Mode:* Toggles the object's rendering into a wireframe edge structure with sharp lighting, allowing fellow artists or buyers to assess 3D topology cleanliness.
    * *Lighting Studio Presets:* Instantly changes the room's environment map with 3 options: `Daylight Studio`, `Studio Neon`, and `Cyberpunk Dark`.
    * *Reset Camera:* Returns the camera transform to its default coordinates if the user gets lost while zooming or panning.
* **Technical Information Panel:** A metrics box displaying accurate technical data extracted from the file: `Polygon Count`, `Vertex Count`, and `File Size`.
* **Separated Download Actions:** Provides two contrasting action buttons; a secondary button (capsule style) to directly download the web `.glb` file, and a primary button (Action Blue color) to request authorization to download the original master `.zip` file.

### D. Admin Control Panel (Curator Dashboard)
* **Staff Pick Toggle Switch:** A dedicated page for Administrator accounts to monitor all public assets entering the system. Admins simply activate a single toggle switch on assets deemed high quality to automatically feature them on the front Landing Page grid, with no database schema changes required.

---

## 3. Asynchronous Pipeline Architecture & Database Schema

### A. Database Schema Blueprint (Laravel Migrations)
To support tag management, conversion lifecycle status, and clean file separation without hardcoding, the database structure must follow this relational model:

```sql
-- 1. Users Table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user' NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 2. Assets Table
CREATE TABLE assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    visibility ENUM('private', 'public') DEFAULT 'private' NOT NULL,
    status ENUM('queued', 'processing', 'completed', 'failed') DEFAULT 'queued' NOT NULL,
    is_staff_pick BOOLEAN DEFAULT false NOT NULL,
    master_zip_path VARCHAR(255) NOT NULL,          -- Secure path to the raw .zip file
    viewer_glb_path VARCHAR(255) NULL,               -- Public path to the converted .glb file
    thumbnail_path VARCHAR(255) NULL,                -- Path to the preview image (.webp)
    polygon_count INT UNSIGNED DEFAULT 0,
    vertex_count INT UNSIGNED DEFAULT 0,
    file_size_bytes BIGINT UNSIGNED DEFAULT 0,
    error_log TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 3. Tags Table
CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP NULL
);

-- 4. Asset_Tag Pivot Table
CREATE TABLE asset_tag (
    asset_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (asset_id, tag_id),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- 5. Asset_Versions Table (Versioning Feature)
CREATE TABLE asset_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    master_zip_path VARCHAR(255) NOT NULL,      -- Master file for this version
    viewer_glb_path VARCHAR(255) NULL,          -- Converted .glb result for this version
    thumbnail_path VARCHAR(255) NULL,
    polygon_count INT UNSIGNED DEFAULT 0,
    vertex_count INT UNSIGNED DEFAULT 0,
    file_size_bytes BIGINT UNSIGNED DEFAULT 0,
    status ENUM('queued', 'processing', 'completed', 'failed') DEFAULT 'queued' NOT NULL,
    error_log TEXT NULL,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    UNIQUE (asset_id, version_number),
    created_at TIMESTAMP NULL
);
```

### B. Conversion Pipeline Workflow (Laravel Background Queue)

All heavy file processing must run asynchronously to prevent server timeouts.

1. Once a file is fully uploaded, the Controller stores the raw file in a secure directory, creates a record in the `assets` table (and a corresponding `asset_versions` row) with status `queued`, then dispatches a job to the queue (`ConvertAssetJob`).
2. The queue worker picks up the job, changes the asset version status to `processing`, then executes an external Python script that calls the **Blender CLI** in headless/background mode:

```bash
blender --background --python app/Scripts/convert_pipeline.py -- --input path/to/raw.blend --output path/to/target.glb
```

3. The Python script is responsible for:
   * Reading the scene's geometry buffer to extract `polygon` and `vertex` counts.
   * Compressing the mesh and exporting the entire scene into a single `.glb` file.
   * Auto-positioning the camera and rendering a single-frame snapshot to `.webp` format for use as a thumbnail image.
4. If the process succeeds, the script returns exit code `0`, the worker updates the version status to `completed`, and stores the geometry metrics in the database. If the process fails (*crash*), the handler catches the error, changes the status to `failed`, and logs the stack trace to the `error_log` column.

### C. Asset Versioning Policy

* **Immutable Per Version:** Every time a creator re-uploads a new raw file for an existing asset, the system does NOT overwrite old data. The system must create a new row in `asset_versions` with `version_number` automatically incremented (`+1` from the highest existing version).
* **Single Source of Truth:** The `master_zip_path`, `viewer_glb_path`, `thumbnail_path`, `polygon_count`, and `vertex_count` columns on the `assets` table are ALWAYS kept in sync to point to the **latest version with `completed` status**. The `assets` table acts as a cache/pointer to the active version, while `asset_versions` stores the full history.
* **Per-Version Conversion Pipeline:** `ConvertAssetJob` is dispatched per `asset_versions` row, not per `assets` row. The `queued/processing/completed/failed` status and `error_log` are recorded at the version level, so a failure on one re-upload does not affect the history of previously successful versions.
* **Version History UI:** The Asset Detail Page (Section 2.C) displays a version history list, allowing creators to view technical metrics for each version or download the master file from a specific version (not just the latest one).

---

## 4. Development Rules & Code Constraints (Do's and Don'ts)

> **Versioning Note:** Since `asset_versions` is now an official feature, every change to `ConvertAssetJob` and `ConversionService` must operate at the version level (one job per `asset_versions` row), rather than directly overwriting columns on the `assets` table.

### DO'S (Mandatory)

* **Use Queues for Heavy Processes:** All external CLI execution, 3D processing scripts, zip extraction, and file compression must be placed inside a Laravel Queue (`ConvertAssetJob`).
* **Mass Assignment Protection:** Lock down all Eloquent model properties that accept write input using strict `$fillable` property declarations.
* **Isolated File Storage:** Store all original master files outside the publicly accessible directory (`storage/app/secure_assets`). Only use symbolic links (`php artisan storage:link`) for viewer `.glb` files and thumbnails for security purposes.

### DON'TS (Absolute Rules — Violations Result in Code Rejection)

1. **Never Access `.env` Directly:** It is strictly forbidden to call the `env()` helper function inside Controllers, Repositories, Services, or Blade files. All environment variables must be registered in a configuration file under the `config/` folder first, and accessed only through the `config()` wrapper function.
2. **Never Hardcode Values into the Database Layer:** It is strictly forbidden to insert static IDs, raw status strings, or permanent logic values directly (*hardcoded*) into SQL queries or Eloquent methods. The code architecture must rely on object relations, class constants, or strictly-typed data structures such as PHP *Enums*.
3. **Never Test Directly in the Browser:** Testing of backend logic functionality — especially the file conversion flow and data extraction from the Blender script — must first be validated using automated testing functionality (*Feature/Unit Tests*) via `php artisan test` or the interactive Laravel Tinker console. Repeatedly reloading the browser page (*refresh page*) to test background logic is strictly forbidden.
4. **No Need to Add Comments on Every Line of Code:** Write expressive, clean (*self-documenting*) code. Writing comments merely to explain basic language syntax is not allowed. Comments are only permitted to explain complex architectural technical decisions or specific reasoning behind a particular line of code (explaining the *"Why"*, not the *"How"*).

---

## 5. Design System Specification (Apple-Inspired UI)

Following the guidelines from `DESIGN-apple.md`, the interface must act as a clean, unobtrusive "museum wall," letting the product/3D asset inside it remain the main focus.

### A. Color Tokens

```yaml
colors:
  primary: "#0066cc"                # Action Blue: The single interactive signal for links & primary buttons
  primary-focus: "#0071e3"          # Focus Blue: Border for keyboard navigation focus
  primary-on-dark: "#2997ff"        # Sky Link Blue: Link text on dark tile surfaces
  ink: "#1d1d1f"                    # Near-Black Ink: Primary text color on light surfaces
  body: "#1d1d1f"                   # Default paragraph text color
  body-on-dark: "#ffffff"           # White text color on dark surfaces
  body-muted: "#cccccc"              # Secondary text color on dark surfaces
  ink-muted-80: "#333333"           # Text color on buttons or pearl components
  ink-muted-48: "#7a7a7a"           # Disabled text and legal footnotes
  divider-soft: "#f0f0f0"           # Subtle divider shadow between components
  hairline: "#e0e0e0"               # 1px thin border line for grid cards
  canvas: "#ffffff"                 # Pure White: Dominant light surface base color
  canvas-parchment: "#f5f5f7"       # Parchment: Off-white color for section/footer rhythm alternation
  surface-pearl: "#fafafc"          # Pearl: Background color for ghost-style secondary buttons
  surface-tile-1: "#272729"         # Near-Black Tile 1: Base surface color for dark sections
  surface-tile-2: "#2a2a2c"         # Near-Black Tile 2: Slight contrast variation for adjacent dark tiles
  surface-tile-3: "#252527"         # Near-Black Tile 3: Darkest background specifically for the 3D viewer frame
  surface-black: "#000000"          # Pure Black: Top-level global navigation menu background
  surface-chip-translucent: "#d2d2d7" # Translucent gray background for floating viewer control buttons
  on-primary: "#ffffff"
  on-dark: "#ffffff"
```

### B. Standard Typography Hierarchy

* Large headings must apply negative letter-spacing to create the dense, distinctive Apple visual rhythm. Font-weight value `500` is excluded from the entire system.

```yaml
typography:
  hero-display:
    fontFamily: "SF Pro Display, system-ui, -apple-system, sans-serif"
    fontSize: 56px
    fontWeight: 600
    lineHeight: 1.07
    letterSpacing: -0.28px          # Landing Page Hero Main Title
  display-lg:
    fontFamily: "SF Pro Display, system-ui, -apple-system, sans-serif"
    fontSize: 40px
    fontWeight: 600
    lineHeight: 1.10
    letterSpacing: 0                # Main Title Above Full-Bleed Section
  lead:
    fontFamily: "SF Pro Display, system-ui, -apple-system, sans-serif"
    fontSize: 28px
    fontWeight: 400
    lineHeight: 1.14
    letterSpacing: 0.196px          # Description Subtitle and Card Sub-info
  body-strong:
    fontFamily: "SF Pro Text, system-ui, -apple-system, sans-serif"
    fontSize: 17px
    fontWeight: 600
    lineHeight: 1.24
    letterSpacing: -0.374px         # Bold/inline text emphasis
  body:
    fontFamily: "SF Pro Text, system-ui, -apple-system, sans-serif"
    fontSize: 17px
    fontWeight: 400
    lineHeight: 1.47                # Standard paragraph reading text (Must be 17px, not 16px)
    letterSpacing: -0.374px
  dense-link:
    fontFamily: "SF Pro Text, system-ui, -apple-system, sans-serif"
    fontSize: 17px
    fontWeight: 400
    lineHeight: 2.41                # Loose line spacing specific to footer menu link stacks
    letterSpacing: 0
  caption:
    fontFamily: "SF Pro Text, system-ui, -apple-system, sans-serif"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.43
    letterSpacing: -0.224px         # Secondary descriptions and small button text labels
  nav-link:
    fontFamily: "SF Pro Text, system-ui, -apple-system, sans-serif"
    fontSize: 12px
    fontWeight: 400
    lineHeight: 1.0
    letterSpacing: -0.12px          # Top-level global navigation menu links
```

### C. Shape & Corner Radius Scale

* `{rounded.none}` (0px): Used for large container tiles (*section tiles*) that touch the full left-right screen edges (*full-bleed*).
* `{rounded.sm}` (8px): Small dashboard utility buttons, internal image arrangement elements.
* `{rounded.md}` (11px): Square capsule shape for secondary action containers using the Pearl color.
* `{rounded.lg}` (18px): Main dashboard modules, portfolio grid cards, and detail information containers.
* `{rounded.pill}` (9999px): Primary Action Blue buttons, horizontal topic filter pills, and search input fields.

### D. Zero-Chrome Philosophy & Shadows

* **No Decorative UI Shadows:** It is strictly forbidden to use drop-shadow effects, subtle shadows, or color gradients on interface components such as text, menu headers, buttons, or card containers.
* **The Only Shadow (The Product Pedestal):** Only **one shadow token** is configured in the CSS system. This shadow is specifically placed behind the transparent photo/3D canvas asset preview visual so the object appears grounded and has real physical weight:

```css
box-shadow: rgba(0, 0, 0, 0.22) 3px 5px 30px 0;
```

* **Natural Page Separators:** Boundaries between page sections are created purely by vertically stacking square, full-bleed container tiles with rhythmically alternating background colors between bright white (`#ffffff`), off-white (`#f5f5f7`), and dark tile black (`#272729`). This background color change acts as the genuine visual boundary.

---

## 6. Repository Commit Management Rules (Git Rules)

To keep the code tracking history clean, organized, and easily isolated in case of errors, all developers must adhere to the following standards.

### Commit Message Guidelines

* **Immediate Commit Obligation:** A commit must be executed immediately upon completing one technical function or one independent logic block. It is forbidden to accumulate too many changes across different environments into a single large commit (e.g., combining database migrations and CSS styling changes into one ambiguous commit).
* **Conventional Format (Conventional Commits):** The commit message format must follow this structure:

```text
<type>(<scope>): <description>
```

#### Allowed Commit Types (`type`):

* `feat`: Adding a new component function or user-accessible page flow (e.g., `feat(landing): add horizontal tag filters for guest users`).
* `fix`: Fixing a bug, resolving a program crash, or resolving an error log (e.g., `fix(pipeline): resolve zip extraction timeout for large fbx files`).
* `refactor`: Changing internal code structure or optimizing a function without changing external visual performance (e.g., `refactor(database): separate master files from web-optimized formats`).
* `chore`: Repository maintenance tasks, updating vendor dependency packages, or changing build environment configuration.

### Standard Git Workflow Execution Example:

```bash
# 1. Check which files have been modified so far
git status

# 2. Stage a specific component once its logical function is complete
git add app/Jobs/ConvertAssetJob.php

# 3. Execute the commit immediately with a clear, structured message
git commit -m "feat(pipeline): implement automated poly and vertex count extraction from blender script"
```