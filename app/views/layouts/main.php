<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Simple PHP Framework', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= assets('css/style.css') ?>">
</head>
<body>
    <header>
        <nav>
            <a href="<?= route('home') ?? '/' ?>">Home</a>
            <a href="<?= route('login.show') ?? '/login' ?>">Login</a>
        </nav>
    </header>

    <main>
        <?= $content ?? '' ?>
    </main>

    <footer>
        <small>&copy; <?= date('Y') ?> Simple PHP Framework</small>
    </footer>

    <script src="<?= assets('js/app.js') ?>" defer></script>
</body>
</html>

