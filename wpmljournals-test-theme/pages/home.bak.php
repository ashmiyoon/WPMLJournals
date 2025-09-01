<?php
$cur_lang = apply_filters('wpml_current_language', null);
?>

<section>
    <h2><?php _e("What distinguishes our party", "icp-scratch") ?></h2>
    <ul>
        <li><?php _e("The line running from Marx to Lenin to the foundation of the Third International and the birth of the Communist Party of Italy at Livorno in 1921, and from there to the struggle of the Italian Communist Left against the degeneration in Moscow and to the rejection of popular fronts and coalitions of resistance groups.", "icp-scratch") ?></li>
        <li><?php _e("The tough work of restoring the revolutionary doctrine and the party organ, in contact with the working class, outside the realm of personal politics and electoral maneuvers.", "icp-scratch") ?></li>
    </ul>
</section>

<?php
$tz = new DateTimeZone("CDT");
$now = new DateTime("now", $tz);
$next = new DateTime("2025-08-30 13:00:00", $tz);
if ($now < $next) { ?>
    <?php if ($cur_lang == 'en'): ?>
        <section class="announcement">
            <h2>Public Meeting</h2>
            <p>Comrades, sympathizers, readers,<br>
            the next party meeting, open to all, will take place on <b>August 30th, Saturday, at <a href="https://time.is/compare/1300_30_August_2025_in_CT">1pm, US Central time</b></a>, or <b>8pm, European Central Time</b>.</p>
            <p>The theme of the talk will be on the issue of <b>Organic Centralism</b>:</p>
            <blockquote>Our Party claims to possess  a doctrine that has remained unique and intangible since the enunciation of its theoretical foundations with the <i>Manifesto of the Communist Party</i> of 1848. Through a series of party organizational forms, it became clear to our current (more than a century ago) that this condition, the only one to enable us to lead the class when the revolutionary conditions will occur, can be maintained only if the party adopts a structure such as not to allow deviations, revisions, opportunism. This structure of our work and of party internal life is Organic Centralism.</blockquote>
            <p>At the end a comrade will answer any possible question on the issue, and in general on the International Communist Party.</p>
            <p><b>Google Meet joining info</b>
            <br>Video call link: <a href="https://meet.google.com/yzp-skwh-wif">https://meet.google.com/yzp-skwh-wif</a>
            <br>Or dial: (IT) +39 02 8734 8723, PIN: 758 731 813 0140#
            <br>More phone numbers: <a href="https://tel.meet/yzp-skwh-wif?pin=7587318130140">https://tel.meet/yzp-skwh-wif?pin=7587318130140</a></p>
        </section>
    <?php elseif ($cur_lang == 'it'): ?>
        <section class="announcement">
            <h2>Riunione Pubblica</h2>
            <p>Compagni, simpatizzanti, lettori<br>
            La prossima conferenza di partito, in inglese e aperta a tutti, avrà luogo il giorno <b>30 agosto, Sabato, alle ore 20, <a href="https://time.is/compare/1300_30_August_2025_in_CT">ora Centro Europa</a></b>, o <b>ore 13, ora USA Centrale</b>.</p>
            <p>L’argomento sul quale parlerà un compagno sarà il <b>Centralismo Organico</b>.</p>
            <blockquote>Il nostro partito sostiene di possedere una dottrina che è rimasta unica e intangibile sin dalla proclamazione dei suoi fondamenti teorici con il <i>Manifesto del Partito Comunista</i> del 1848. Attraverso una serie di forme organizzative del partito, è diventato chiaro alla nostra attuale leadership (più di un secolo fa) che questa condizione, l'unica che ci permetta di guidare la classe quando si presenteranno le condizioni rivoluzionarie, può essere mantenuta solo se il partito adotta una struttura tale da non consentire deviazioni, revisioni, opportunismo. Questa struttura del nostro lavoro e della vita interna del partito è il centralismo organico.</blockquote>
            <p>Al termine il compagno risponderà a eventuali domande tese a chiarire aspetti dell’argomento trattato, e in genere sul Partito Comunista Internazionale.</p>
            <p><b>Come collegarsi al Google Meet:</b>
            <br>Link per il collegamento video: <a href="https://meet.google.com/yzp-skwh-wif">https://meet.google.com/yzp-skwh-wif</a>
            <br>O al telefono: (IT) +39 02 8734 8723, PIN: 758 731 813 0140#
            <br>Altri numeri di telefono: <a href="https://tel.meet/yzp-skwh-wif?pin=7587318130140">https://tel.meet/yzp-skwh-wif?pin=7587318130140</a></p>
        </section>
    <?php elseif ($cur_lang == 'ro'): ?>
        <section class="announcement">
            <h2>Întâlnire Publică</h2>
            <p>Tovarăși, simpatizanți, cititori,<br>
            următoarea întâlnire de partid, deschisă tuturor, va avea loc pe data de <b>30 August, Sâmbătă, la <a href="https://time.is/compare/1300_30_August_2025_in_CT">1pm, US Central Time</a></b>, sau la <b>8pm, European Central Time</b>.</p>
            <p>Tema prelegerii va fi <b>Centralismul Organic</b>:</p>
            <blockquote>Partidul nostru susține că deține o doctrină care a rămas unică și intangibilă de la enunțarea fundamentelor sale teoretice în Manifestul Partidului Comunist din 1848. Printr-o serie de forme organizaționale ale partidului, a devenit clar pentru actuala noastră conducere (acum mai bine de un secol) că această condiție, singura care ne permite să conducem clasa atunci când vor apărea condițiile revoluționare, poate fi menținută numai dacă partidul adoptă o structură care să nu permită abateri, revizuiri, oportunism. Această structură a muncii noastre și a vieții interne a partidului este centralismul organic.</blockquote>
            <p>La final, un tovarăș va răspunde la orice întrebare posibilă pe această temă și, în general, despre Partidul Comunist Internațional.</p>
            <p><b>Informații pentru Google Meet</b>
            <br>Linkul pentru apelul video: <a href="https://meet.google.com/yzp-skwh-wif">https://meet.google.com/yzp-skwh-wif</a>
            <br>Sau apelați: (IT) +39 02 8734 8723, PIN: 758 731 813 0140#
            <br>Mai multe numere de telefon: <a href="https://tel.meet/yzp-skwh-wif?pin=7587318130140">https://tel.meet/yzp-skwh-wif?pin=7587318130140</a></p>
        </section>
    <?php elseif ($cur_lang == 'es'): ?>
        <section class="announcement">
            <h2>Reunión Pública</h2>
            <p>Compañeros, simpatizantes, lectores,<br>
            La próxima reunión de partido, en <b>inglés</b> y abierta a todos, tendrá lugar el <b>sábado, 30 de agosto, a <a href="https://time.is/compare/1300_30_August_2025_in_CT">las 20, hora central europea</a></b>, o a las <b>13, hora central EEUU</b>.</p>
            <p>El tema de la presentación será el <b>Centralismo Orgánico</b>:</p>
            <blockquote>Nuestro partido sostiene poseer una doctrina que ha permanecido única e intangible desde la proclamación de sus fundamentos teóricos en el Manifiesto del Partido Comunista de 1848. Tras una serie de diferentes formas organizativas del partido, nos ha quedado claro a nuestra corriente —hace más de un siglo— que esta condición, la única que nos permitirá guiar a la clase cuando se presenten las condiciones revolucionarias, solo se puede mantener si el partido adopta una estructura que no permita ni desviaciones, ni revisiones, ni oportunismo. Esta estructura de nuestro trabajo y de la vida interna del partido es el <i>centralismo orgánico</i>.</blockquote>
            <p>Al final, nuestro compañero responderá a las posibles preguntas que puedan surgir sobre el tema y sobre el Partido Comunista Internacional en general.</p>
            <p><b>Cómo unirse al Google Meet</b>
            <br>Enlace para unirse a la videoconferencia: <a href="https://meet.google.com/yzp-skwh-wif">https://meet.google.com/yzp-skwh-wif</a>
            <br>O llama al: (IT) +39 02 8734 8723, PIN: 758 731 813 0140#
            <br>Otros números de teléfono: <a href="https://tel.meet/yzp-skwh-wif?pin=7587318130140">https://tel.meet/yzp-skwh-wif?pin=7587318130140</a></p>
        </section>
    <?php elseif ($cur_lang == 'fr'): ?>
        <section class="announcement">
            <h2>Réunion publique</h2>
            <p>Camarades, sympathisants, lecteurs,<br>
            la prochaine réunion du parti, ouverte à tous, aura lieu le <b>samedi 30 août à <a href="https://time.is/compare/1300_30_August_2025_in_CT">13 h, heure centrale des États-Unis</a></b>, ou <b>20 h, heure centrale européenne</b>.</p>
            <p>Le thème de la discussion portera sur la question du <b>centralisme organique</b> :</p>
            <blockquote>Notre parti affirme posséder une doctrine qui est restée unique et intangible depuis l'énoncé de ses fondements théoriques dans le <i>Manifeste du Parti communiste</i> de 1848. À travers une série de formes d'organisation du parti, il est apparu clairement à notre courant (il y a plus d'un siècle) que cette condition, la seule qui nous permette de diriger la classe lorsque les conditions révolutionnaires se présenteront, ne peut être maintenue que si le parti adopte une structure qui ne permette pas les déviations, les révisions et l'opportunisme. Cette structure de notre travail et de la vie interne du parti est le Centralisme Organique.</blockquote>
            <p>À la fin, un camarade répondra à toutes les questions éventuelles sur le sujet et, de manière générale, sur le Parti communiste international.</p>
            <p><b>Informations pour rejoindre Google Meet :</b>
            <br>Lien pour l'appel vidéo : <a href="https://meet.google.com/yzp-skwh-wif">https://meet.google.com/yzp-skwh-wif</a>
            <br>Ou composez le : (IT) +39 02 8734 8723, PIN: 758 731 813 0140#
            <br>Autres numéros de téléphone : <a href="https://tel.meet/yzp-skwh-wif?pin=7587318130140">https://tel.meet/yzp-skwh-wif?pin=7587318130140</a></p>
        </section>
    <?php endif; ?>
<?php } ?>

<section class="additions-heading">
    <h2><?php _e("Latest additions", "icp-scratch") ?></h2>
    <a href="<?php echo all_publications_url() ?>"><?php _e("In all languages", "icp-scratch") ?></a>
</section>

<?php
global $wpdb;
$query_limit = 30;
$article_query = $wpdb->get_results($wpdb->prepare(
    <<<SQL
        SELECT ID, post_name, post_title, post_date
        FROM wp_posts INNER JOIN wp_icl_translations ON ID = wp_icl_translations.element_id
        WHERE post_type='article' AND element_type='post_article' AND wp_icl_translations.language_code = %s AND post_status = 'publish'
        ORDER BY post_date DESC
        LIMIT $query_limit
    SQL, $cur_lang
));
?>

<?php get_template_part('parts/text-listing', null, array('pubs' => $article_query)) ?>

<style>
    .announcement {
        border: black 1px solid;
        padding-left: 2rem;
        padding-right: 2rem;
    }

    .see-more {
        border: gray 1px solid;
        padding: 4px;
        display: flex;
        flex-direction: flex-row;
        justify-content: center;
        text-align: center;
    }

    .see-more a {
        flex-grow: 1;
        padding: 2px;
    }

    .see-more:hover {
        background-color: #00000008;
    }
</style>

<?php if (count($article_query) == $query_limit) { ?>
    <div class="see-more">
        <a href="<?php echo all_texts_url() ?>"><?php _e("See full list...", "icp-scratch") ?></a>
    </div>
<?php } ?>