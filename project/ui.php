<?php

function ui_render_head(string $title, array $options = []): void
{
    $withBoxIcons = $options['boxicons'] ?? true;
    $withJquery = $options['jquery'] ?? false;

    echo '<meta charset="UTF-8">' . PHP_EOL;
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
    echo '<title>' . htmlspecialchars($title, ENT_QUOTES) . '</title>' . PHP_EOL;
    echo '<script src="https://cdn.tailwindcss.com"></script>' . PHP_EOL;
    echo '<script>' . PHP_EOL;
    echo 'tailwind.config = {' . PHP_EOL;
    echo "    darkMode: 'class'," . PHP_EOL;
    echo '    theme: { extend: { fontFamily: { sans: [\'Inter\', \'ui-sans-serif\', \'system-ui\', \'sans-serif\'] } } }' . PHP_EOL;
    echo '};' . PHP_EOL;
    echo '</script>' . PHP_EOL;
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL;
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL;
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">' . PHP_EOL;

    if ($withBoxIcons) {
        echo "<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>" . PHP_EOL;
    }

    if ($withJquery) {
        echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>' . PHP_EOL;
    }
}

function ui_dark_toggle_button(string $id = 'darkModeBtn'): string
{
    return '<button id="' . htmlspecialchars($id, ENT_QUOTES) . '" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 text-slate-700 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"><i class="bx bx-moon text-lg"></i></button>';
}

function ui_darkmode_script(string $id = 'darkModeBtn'): void
{
    $safeId = htmlspecialchars($id, ENT_QUOTES);
    echo '<script>' . PHP_EOL;
    echo 'const darkModeBtn = document.getElementById("' . $safeId . '");' . PHP_EOL;
    echo 'if (darkModeBtn) {' . PHP_EOL;
    echo '    if (localStorage.getItem("loglog-dark-mode") === "1") {' . PHP_EOL;
    echo '        document.documentElement.classList.add("dark");' . PHP_EOL;
    echo '        darkModeBtn.innerHTML = "<i class=\'bx bx-sun text-lg\'></i>";' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '    darkModeBtn.addEventListener("click", () => {' . PHP_EOL;
    echo '        const isDark = document.documentElement.classList.toggle("dark");' . PHP_EOL;
    echo '        localStorage.setItem("loglog-dark-mode", isDark ? "1" : "0");' . PHP_EOL;
    echo '        darkModeBtn.innerHTML = isDark ? "<i class=\'bx bx-sun text-lg\'></i>" : "<i class=\'bx bx-moon text-lg\'></i>";' . PHP_EOL;
    echo '    });' . PHP_EOL;
    echo '}' . PHP_EOL;
    echo '</script>' . PHP_EOL;
}

function ui_card_classes(string $extra = ''): string
{
    return trim('rounded-xl border border-emerald-100 p-4 dark:border-slate-700 ' . $extra);
}

function ui_input_classes(string $extra = ''): string
{
    return trim('w-full rounded-xl border border-emerald-100 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-300/40 dark:border-slate-700 dark:bg-slate-800 ' . $extra);
}

function ui_primary_button_classes(string $extra = ''): string
{
    return trim('rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 ' . $extra);
}
