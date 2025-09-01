<style>
    .lang-switcher {
        position: relative;
        display: block;
        padding: 4px;
        border: gray 1px solid;
        width: 14em;
        line-height: 1.15;
    }

    .lang-switcher::after {
        content: "▾";
        position: absolute;
        right: 10px;
        top: calc(50% - .175em - 7.5px);
    }

    .lang-switcher-content {
        padding: 4px;
        visibility: hidden;
        position: absolute;
        background-color: Canvas;
        border: gray 1px solid;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 101;
        margin: 0;
    }

    .lang-switcher-content ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .lang-switcher-content a {
        display: block;
        padding-top: 4px;
        padding-bottom: 4px;
    }

    .lang-switcher-content a:hover {
        display: block;
        background-color: #00000008;
    }

    .lang-switcher:hover .lang-switcher-content {
        visibility: visible;
    }

    @media (prefers-color-scheme: dark) {
        .lang-switcher-content a:hover {
            background-color: #ffffff20;
        }
    }
</style>

<?php
$cur_lang = $cur_lang = apply_filters('wpml_current_language', null);

$lang_codes = LISTED_LANGS;
$lang_codes_content = implode(',', array_map(function($x) {
    return "'$x'";
}, $lang_codes));

global $wpdb;
$langs = $wpdb->get_results($wpdb->prepare(<<<SQL
    SELECT language_code, name
    FROM wp_icl_languages_translations
    WHERE display_language_code = language_code AND language_code IN ($lang_codes_content)
SQL, $cur_lang));

$lang_names = array();
foreach ($langs as $row) {
    $lang_names[$row->language_code] = $row->name;
}

$cur_lang_name = $lang_names[$cur_lang] ?? "Null";
?>

<nav class="lang-switcher">
    <div class="lang-switcher-cur"><?php echo $cur_lang_name ?></div>
    <div class="lang-switcher-content">
        <ul><?php
            foreach ($lang_codes as $lang_code) {
                if ($lang_code == $cur_lang) continue;
                $url = $_SERVER['REQUEST_URI'];
                $needle = "/{$cur_lang}/";
                $pos = strpos($url, $needle);
                $url = substr_replace($url, "/{$lang_code}/", $pos, strlen($needle));
                $name = $lang_names[$lang_code];
                echo "<li><a href='$url'>$name</a></li>";
            }
        ?></ul>
    </div>
</nav>