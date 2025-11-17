# Overview

This repository contains a lightweight, educational PHP MVC framework intended to
demonstrate the core pieces of a front-controller style application.  Despite
its size, it exposes a complete set of public APIs for bootstrapping
controllers, rendering views, working with assets, and performing basic database
queries.

This document serves as comprehensive reference documentation for every public
component, class, method, and helper function.  Alongside each API you will find
usage details and runnable examples.

## 📚 Documentation

**Live Documentation:** [View Documentation](https://erselmetz.github.io/php-framework-docs/)

The complete documentation including version history, tutorials, and code examples is available online.

---

# Quick Start

```bash
git clone https://github.com/<you>/php-framework.git
cd php-framework
php -S localhost:8080
```

Then browse to <http://localhost:8080/php-framework/>.

To customise the base URL or database connection settings edit `config.php`.

---

# Application Flow

1. `index.php` loads configuration (`config.php`) and bootstraps the framework
   via `core/init.php`.
2. `core/init.php` registers the database utilities, model autoloader, and core
   classes.
3. Instantiating `core\App` reads `$_GET['url']`, resolves a controller class,
   determines the method to invoke, and dispatches the request.
4. Controllers extend `core\Controller`, which provides helpers for rendering
   views and redirecting unauthenticated users.
5. Views use the global `assets()` helper to reference cache-busted static files
   located beneath `public/`.

---

# Configuration (`config.php`)

| Symbol       | Type   | Description                                                    | Example                              |
|--------------|--------|----------------------------------------------------------------|--------------------------------------|
| `$RewriteBase` | string | Base path the framework is served from. Set to `/` for root deployments. | `/php-framework/`                    |
| `$database`  | array  | Credentials for the MySQL connection used by `Connection\Database`. | `["host"=>"localhost", …]`           |
| `$SQLite`    | string | Path to the SQLite database file used by `Connection\SQLite`.  | `test.sqlite`                        |

---

# Helper Functions

## `assets(string $relativePath): string`

Builds a public URL for an asset stored beneath `public/`. When the referenced
file exists, the helper appends a `v=<timestamp>` query parameter derived from
`filemtime`, enabling long-lived caching with automatic busting on deploys.

**Usage**

```php
<link rel="stylesheet" href="<?= assets('css/style.css') ?>">
<script src="<?= assets('js/app.js') ?>" defer></script>
```

**Behaviour**

- Mirrors the configured `$RewriteBase`.
- Returns the original URL when the file cannot be found (still useful for CDN
  hosted assets).

---

# Core Classes

## `core\App`

`App` is the front controller.  Construction of this class dispatches the
current HTTP request.

### Properties

| Property       | Access | Description                                                                      |
|----------------|--------|----------------------------------------------------------------------------------|
| `$controller`  | protected | Name of the active controller (defaults to `html`).                          |
| `$method`      | protected | Method to invoke on the controller (defaults to `index`).                    |
| `$params`      | protected | Sequential numeric array of remaining URL segments passed to the method.     |

### Public API

#### `__construct(): void`

Parses the request URL, resolves the controller and method, and executes the
target method via `call_user_func_array`.  This method is invoked implicitly
from `index.php`.

**Dispatch Algorithm**

1. Call `parseUrl()` to split `$_GET['url']` into path segments.
2. Treat the first segment as controller name.  If the file
   `app/controllers/<controller>.php` exists, set `$controller` accordingly.
3. Include the controller file and instantiate the class.
4. If the next segment matches a public method on the controller, select it.
5. Any additional segments are passed to the method as a single array argument.

```php
// index.php
require_once 'core/init.php';
$app = new App(); // dispatches immediately
```

#### `parseUrl(): ?array`

Reads the `url` query parameter, trims trailing slashes, sanitises it with
`FILTER_SANITIZE_URL`, and splits it into an array of segments.  Returns `null`
when no `url` query string is provided.

```php
// Given: http://localhost/php-framework/html/show/123
// $_GET['url'] === 'html/show/123'
$segments = $app->parseUrl(); // ['html', 'show', '123']
```

### URL Routing Examples

| URL path                                   | Controller | Method | Parameters (array)   |
|--------------------------------------------|------------|--------|----------------------|
| `/html/test1/params1/params2/params3`      | `html`     | `test1`| `['params1', …]`     |
| `/test1/params1/params2/params3`           | `html`     | `test1`| `['params1', …]`     |
| `/`                                        | `html`     | `index`| `[]`                  |

_Note_: Controller filenames must be lowercase (`html.php`) while class names
use standard PHP casing (`Html`).

---

## `core\Controller`

Base class for application controllers located in `app/controllers`.  Extend
this class to gain access to view rendering and authentication helpers.

### Public Methods

#### `view(string $view, array $data = []): void`

Includes `app/views/<view>.php`.  Use `$data` to pass variables by extracting
them prior to inclusion (custom extraction logic can be added in your own
controller).

```php
class Html extends Controller
{
    public function index(): void
    {
        $data = ['headline' => 'Welcome!'];
        extract($data);
        $this->view('home');
    }
}
```

#### `error_404(): void`

Sends a simple “Not found 404” response.  Controllers can call this to handle
unresolvable resources.

```php
public function show(array $params): void
{
    if (!$model->exists($params[0] ?? null)) {
        $this->error_404();
        return;
    }
}
```

#### `is_auth(): void`

Redirects to the `login` route when the current session lacks both `email` and
`password` entries.

```php
public function dashboard(): void
{
    $this->is_auth(); // redirect unauthenticated users
    $this->view('dashboard');
}
```

---

## `core\Post`

Utility class for sanitising POST input with an optional minimum length
constraint.  Designed for fluent usage.

### Public Static Methods

| Method                             | Description                                                |
|------------------------------------|------------------------------------------------------------|
| `require(string $key): Post`       | Sanitises `$_POST[$key]` if present, storing the result.   |
| `limit(int $length): void`         | Sets the minimum acceptable string length.                 |
| `get(): ?string`                   | Returns the processed value or an error message.           |

**Example**

```php
Post::limit(5);
$username = Post::require('username')->get();

if ($username === "value is limited only to 5") {
    // handle validation error
}
```

_Note_: The current implementation compares the POST value length using
`strlen(Post::$var >= Post::$limit)` which resolves to a boolean.  Adjust for
proper validation as needed.

---

## `core\Auth`

Simple session checker exposed as `Auth::user()`.

### `Auth::user(): bool`

Returns `true` when both `$_SESSION['email']` and `$_SESSION['password']` are
set; otherwise `false`.

```php
if (Auth::user()) {
    // show dashboard
} else {
    // show login form
}
```

---

# Database Layer (`core/database.php`)

Namespaced under `Connection`.  Two static query builders are provided: one for
MySQL (via `mysqli`) and one for SQLite (via `PDO`).  Both expose a minimal,
chainable API intended for demonstrative purposes.

## `Connection\Database`

### Constructor

- `__construct()`: Reads `$database` from `config.php` and stores credentials in
  static properties.  Instantiated automatically when chaining methods.

### Fluent Query API

| Method                    | Purpose                                           | Example                              |
|---------------------------|---------------------------------------------------|--------------------------------------|
| `Database::query($sql)`   | Run a raw SQL statement.                          | `Database::query("SELECT 1")->get();` |
| `Database::table($name)`  | Begin building a SELECT statement for the table.  | `Database::table('users')`           |
| `select($columns)`        | Set the columns clause (defaults to `*`).         | `->select('id, email')`              |
| `where($condition)`       | Append a `WHERE` clause.                          | `->where("id = 5")`                  |
| `andWhere($condition)`    | Append additional `AND` clauses.                  | `->andWhere("status = 'active'")`    |
| `orderBy($order)`         | Append an `ORDER BY` clause.                      | `->orderBy('created_at DESC')`       |
| `limit($limit)`           | Append a `LIMIT` clause.                          | `->limit(10)`                        |
| `get(): array`            | Execute the built query and return associative rows. | `Database::table('users')->get();` |

**Complete Example**

```php
use Connection\Database;

$users = Database::table('users')
    ->select('id, email')
    ->where("role = 'admin'")
    ->orderBy('created_at DESC')
    ->limit(20)
    ->get();

foreach ($users as $user) {
    echo $user['email'], PHP_EOL;
}
```

**Notes**

- Each call reuses static state.  Reset builder state between queries to avoid
  carrying over clauses.
- `get()` terminates the chain, returning an array of associative arrays.
- On connection failure `get()` prints an error and exits.

---

## `Connection\SQLite`

Mirrors the MySQL API but powered by `PDO`.

| Method              | Description                                            |
|---------------------|--------------------------------------------------------|
| `SQLite::query($sql)` | Execute raw SQL returning the `PDOStatement`.       |
| `SQLite::table($table)` | Build a SELECT query as with `Database`.          |
| `select`, `where`, `andWhere`, `orderBy`, `limit` | Same semantics as `Database`. |
| `get(): array`      | Returns all rows via `PDO::FETCH_ASSOC`.               |

```php
use Connection\SQLite;

$posts = SQLite::table('posts')
    ->select('title, body')
    ->orderBy('published_at DESC')
    ->get();
```

---

# Controllers (`app/controllers`)

Controllers must be named to match their file.  For example, `app/controllers/html.php`
contains:

```php
class Html extends Controller
{
    public function index(array $params = []): void
    {
        $this->view('home');
    }

    public function e404(): void
    {
        $this->view('error/404');
    }

    public function login(): void
    {
        echo "this is login page";
    }
}
```

## Creating New Controllers

1. Add a lowercase PHP file to `app/controllers`, e.g. `profile.php`.
2. Define a class with the same name but proper casing, extending `Controller`.
3. Implement public methods representing routes.

```php
// app/controllers/profile.php
class Profile extends Controller
{
    public function show(array $params): void
    {
        $userId = $params[0] ?? null;
        // Load user, then render a view
        $this->view('profile/show');
    }
}
```

Visiting `/profile/show/42` invokes `Profile::show(['42'])`.

---

# Views (`app/views`)

- Views are raw PHP templates and can call helper functions directly.
- Store shared layouts or partials as additional PHP files and include them.
- Use `assets()` to load CSS/JS from `public/`.

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?= assets('css/style.css') ?>">
</head>
<body>
    <h1><?= htmlspecialchars($headline ?? 'Hello') ?></h1>
    <script src="<?= assets('js/app.js') ?>" defer></script>
</body>
</html>
```

---

# Models (`app/models`)

An autoloader (`spl_autoload_register`) loads any PHP class placed under
`app/models`.  Use this directory to create domain objects or repositories that
leverage `Connection\Database` or `Connection\SQLite`.

```php
// app/models/User.php
use Connection\Database;

class User
{
    public static function all(): array
    {
        return Database::table('users')->get();
    }
}
```

---

# Static Assets (`public/`)

Place CSS, JavaScript, images, and other publicly accessible files beneath
`public/`.  The directory structure is mirrored when generating URLs via
`assets()`.

```
public/
├── css/
│   └── style.css
└── js/
    ├── app.js
    └── vendor/
```

Deploy static assets using your web server of choice; the PHP helper only
generates URLs.

---

# Extending the Framework

- **Routing**: Modify `App::parseUrl()` or introduce a router layer before
  instantiating controllers.
- **Middleware**: Override controller constructors and invoke shared logic before
  handling requests.
- **Templating**: Wrap `Controller::view()` to inject variables or integrate a
  third-party template engine.
- **Validation**: Expand `Post` to support richer validation rules and error
  collection.
- **Security**: Ensure session handling (`session_start()`) is in place, add CSRF
  protection, and sanitise database inputs with prepared statements for
  production use.

---

# Troubleshooting

| Symptom                                      | Likely Cause / Fix                                           |
|----------------------------------------------|--------------------------------------------------------------|
| Always redirected to `index` method          | Ensure controller file exists and class name matches file.   |
| Assets returning 404                          | Verify `RewriteBase` and that files sit under `public/`.     |
| Persistent DB filters between queries        | Manually reset `Database::$where`, `$andWhere`, etc.         |
| “Failed to connect to MySQL”                 | Confirm credentials in `config.php` and that MySQL is running.|

---

# License

This project is provided as-is for educational purposes.  Refer to `LICENSE` for
details.