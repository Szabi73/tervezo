<?php
/**
 * Plugin Name: Tervezo Shortcode
 * Description: Kronobiológia form and result display as a shortcode.
 * Version: 1.1.0
 * Author: Codex
 */

if (!defined('ABSPATH')) {
    exit;
}

function tervezo_shortcode_render(): string
{
    wp_enqueue_style(
        'tervezo-shortcode',
        plugin_dir_url(__FILE__) . 'assets/tervezo-shortcode.css',
        [],
        '1.0.0'
    );
    wp_enqueue_script(
        'tervezo-shortcode',
        plugin_dir_url(__FILE__) . 'assets/tervezo-shortcode.js',
        [],
        '1.0.0',
        true
    );

    $submitted = false;
    $errors = [];
    $data = [
        'year' => '',
        'month' => '',
        'day' => '',
        'name' => '',
    ];
    $result_html = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tervezo_shortcode_nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['tervezo_shortcode_nonce']));
        if (wp_verify_nonce($nonce, 'tervezo_shortcode_submit')) {
            $data['year'] = sanitize_text_field(wp_unslash($_POST['year'] ?? ''));
            $data['month'] = sanitize_text_field(wp_unslash($_POST['month'] ?? ''));
            $data['day'] = sanitize_text_field(wp_unslash($_POST['day'] ?? ''));
            $data['name'] = sanitize_text_field(wp_unslash($_POST['nev'] ?? ''));

            if ($data['year'] === '') {
                $errors[] = 'Nem adtad meg a születési évet!';
            } elseif (!is_numeric($data['year'])) {
                $errors[] = 'Az évszámot számmal írd be.';
            }

            if ($data['month'] === '') {
                $errors[] = 'Nem adtad meg a születési hónapot!';
            } elseif (!is_numeric($data['month'])) {
                $errors[] = 'A hónap számát számmal írd be.';
            }

            if ($data['day'] === '') {
                $errors[] = 'Nem adtad meg a születési napot!';
            } elseif (!is_numeric($data['day'])) {
                $errors[] = 'A napot számmal írd be.';
            }

            $year = (int) $data['year'];
            $month = (int) $data['month'];
            $day = (int) $data['day'];

            if ($data['year'] !== '' && ($year < 1902 || $year > 2037)) {
                $errors[] = 'A születési évnek 1902 és 2037 közé kell esnie.';
            }
            if ($data['month'] !== '' && ($month < 1 || $month > 12)) {
                $errors[] = 'A születési hónapnak 1 és 12 közé kell esnie.';
            }
            if ($data['day'] !== '' && ($day < 1 || $day > 31)) {
                $errors[] = 'A születési napnak 1 és 31 közé kell esnie.';
            }
            if ($data['name'] === '') {
                $errors[] = 'Nem adtad meg a nevet! Becenév is lehet.';
            }

            if (!$errors) {
                $submitted = true;
                $result_html = tervezo_dl(1, $year, $month, $day, $data['name']);
            }
        }
    }

    $year_list = [];
    for ($i = 1902; $i <= 2037; $i++) {
        $year_list[] = (string) $i;
    }

    $allowed_table_html = [
        'table' => [
            'cellpadding' => true,
            'cellspacing' => true,
        ],
        'tr' => [
            'bgcolor' => true,
        ],
        'td' => [
            'bgcolor' => true,
            'align' => true,
            'colspan' => true,
        ],
        'b' => [],
        'br' => [],
    ];

    wp_add_inline_script(
        'tervezo-shortcode',
        'const tervezoYearList = ' . wp_json_encode($year_list) . ';',
        'before'
    );

    ob_start();
    ?>
    <div class="tervezo-shortcode container-fluid">
        <?php if ($submitted) : ?>
            <table border="0" cellpadding="10">
                <tr>
                    <td>Név: <b><?php echo esc_html($data['name']); ?></b></td>
                </tr>
                <tr>
                    <td>
                        Születés napja:
                        <b><?php echo esc_html($year); ?>. <?php echo esc_html($month); ?>. <?php echo esc_html($day); ?>.</b>
                    </td>
                </tr>
            </table>
            <br><br>
            <?php echo wp_kses($result_html, $allowed_table_html); ?>
            <br>
            <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-primary">Új elemzés</a>
        <?php else : ?>
            <form method="post">
                <?php wp_nonce_field('tervezo_shortcode_submit', 'tervezo_shortcode_nonce'); ?>
                <div class="border border-2 rounded" style="padding:10px">
                    <br>Születés napja: <i>(pl: 2002-10-28)</i><br>
                    Év (1902-2037):
                    <span class="autocomplete" style="width:80px;">
                        <input id="year" name="year" type="text" size="4" maxlength="4" inputmode="numeric" value="<?php echo esc_attr($data['year']); ?>" autocomplete="off" onblur="completeyear()">
                    </span>
                    Hónap:
                    <input id="month" name="month" type="text" size="2" maxlength="2" inputmode="numeric" value="<?php echo esc_attr($data['month']); ?>">
                    Nap:
                    <input id="day" name="day" type="text" size="2" maxlength="2" inputmode="numeric" value="<?php echo esc_attr($data['day']); ?>">
                    <br><br>

                    Név:
                    <input type="text" id="nev" class="form-control" name="nev" value="<?php echo esc_attr($data['name']); ?>">
                    <br>

                    <input type="submit" value="Elemzés" class="btn btn-primary" name="order" onclick="return check()">
                </div>
            </form>

            <?php if ($errors) : ?>
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}

function tervezo_dl(int $ret = 0, int $ev = 0, int $ho = 0, int $nap = 0, string $nev = ''): string
{
    $temp = "1900\t7\t17\t17\t1
1901\t10\t16\t15\t0
1902\t13\t15\t13\t0
1903\t16\t14\t11\t0
1904\t18\t12\t8\t1
1905\t21\t11\t6\t0
1906\t1\t10\t4\t0
1907\t4\t9\t2\t0
1908\t6\t7\t32\t1
1909\t9\t6\t30\t0
1910\t12\t5\t28\t0
1911\t15\t4\t26\t0
1912\t17\t2\t23\t1
1913\t20\t1\t21\t0
1914\t0\t0\t19\t0
1915\t3\t27\t17\t0
1916\t5\t25\t14\t1
1917\t8\t24\t12\t0
1918\t11\t23\t10\t0
1919\t14\t22\t8\t0
1920\t16\t20\t5\t1
1921\t19\t19\t3\t0
1922\t22\t18\t1\t0
1923\t2\t17\t32\t0
1924\t4\t15\t29\t1
1925\t7\t14\t27\t0
1926\t10\t13\t25\t0
1927\t13\t12\t23\t0
1928\t15\t10\t20\t1
1929\t18\t9\t18\t0
1930\t21\t8\t16\t0
1931\t1\t7\t14\t0
1932\t3\t5\t11\t1
1933\t6\t4\t9\t0
1934\t9\t3\t7\t0
1935\t12\t2\t5\t0
1936\t14\t0\t2\t1
1937\t17\t27\t0\t0
1938\t20\t26\t31\t0
1939\t0\t25\t29\t0
1940\t2\t23\t26\t1
1941\t5\t22\t24\t0
1942\t8\t21\t22\t0
1943\t11\t20\t20\t0
1944\t13\t18\t17\t1
1945\t16\t17\t15\t0
1946\t19\t16\t13\t0
1947\t22\t15\t11\t0
1948\t1\t13\t8\t1
1949\t4\t12\t6\t0
1950\t7\t11\t4\t0
1951\t10\t10\t2\t0
1952\t12\t8\t32\t1
1953\t15\t7\t30\t0
1954\t18\t6\t28\t0
1955\t21\t5\t26\t0
1956\t0\t3\t23\t1
1957\t3\t2\t21\t0
1958\t6\t1\t19\t0
1959\t9\t0\t17\t0
1960\t11\t26\t14\t1
1961\t14\t25\t12\t0
1962\t17\t24\t10\t0
1963\t20\t23\t8\t0
1964\t22\t21\t5\t1
1965\t2\t20\t3\t0
1966\t5\t19\t1\t0
1967\t8\t18\t32\t0
1968\t10\t16\t29\t1
1969\t13\t15\t27\t0
1970\t16\t14\t25\t0
1971\t19\t13\t23\t0
1972\t21\t11\t20\t1
1973\t1\t10\t18\t0
1974\t4\t9\t16\t0
1975\t7\t8\t14\t0
1976\t9\t6\t11\t1
1977\t12\t5\t9\t0
1978\t15\t4\t7\t0
1979\t18\t3\t5\t0
1980\t20\t1\t2\t1
1981\t0\t0\t0\t0
1982\t3\t27\t31\t0
1983\t6\t26\t29\t0
1984\t8\t24\t26\t1
1985\t11\t23\t24\t0
1986\t14\t22\t22\t0
1987\t17\t21\t20\t0
1988\t19\t19\t17\t1
1989\t22\t18\t15\t0
1990\t2\t17\t13\t0
1991\t5\t16\t11\t0
1992\t7\t14\t8\t1
1993\t10\t13\t6\t0
1994\t13\t12\t4\t0
1995\t16\t11\t2\t0
1996\t18\t9\t32\t1
1997\t21\t8\t30\t0
1998\t1\t7\t28\t0
1999\t4\t6\t26\t0
2000\t6\t4\t23\t1
2001\t9\t3\t21\t0
2002\t12\t2\t19\t0
2003\t15\t1\t17\t0
2004\t17\t27\t14\t1
2005\t20\t26\t12\t0
2006\t0\t25\t10\t0
2007\t3\t24\t8\t0
2008\t5\t22\t5\t1
2009\t8\t21\t3\t0
2010\t11\t20\t1\t0
2011\t14\t19\t32\t0
2012\t16\t17\t29\t1
2013\t19\t16\t27\t0
2014\t22\t15\t25\t0
2015\t2\t14\t23\t0
2016\t4\t12\t20\t1
2017\t7\t11\t18\t0
2018\t10\t10\t16\t0
2019\t13\t9\t14\t0
2020\t15\t7\t11\t1
2021\t18\t6\t9\t0
2022\t21\t5\t7\t0
2023\t1\t4\t5\t0
2024\t3\t2\t2\t1
2025\t6\t1\t0\t0
2026\t9\t0\t31\t0
2027\t12\t27\t29\t0
2028\t14\t25\t26\t1
2029\t17\t24\t24\t0
2030\t20\t23\t22\t0
2031\t0\t22\t20\t0
2032\t2\t20\t17\t1
2033\t5\t19\t15\t0
2034\t8\t18\t13\t0
2035\t11\t17\t11\t0
2036\t13\t15\t8\t1
2037\t16\t14\t6\t0";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $evszam[$temp3[0]] = [(int) $temp3[1], (int) $temp3[2], (int) $temp3[3], (int) $temp3[4]];
    }

    $temp = "1\tJanuár\t31\t12\t26\t4
2\tFebruár\t28\t7\t26\t9
3\tMárcius\t31\t22\t23\t11
4\tÁprilis\t30\t15\t21\t14
5\tMájus\t31\t7\t18\t16
6\tJúnius\t30\t0\t16\t19
7\tJúlius\t31\t15\t13\t21
8\tAugusztus\t31\t7\t10\t23
9\tSzeptember\t30\t0\t8\t26
10\tOktóber\t31\t15\t5\t28
11\tNovember\t30\t8\t3\t31
12\tDecember\t31\t0\t0\t0
13\tJanuár-szökőév\t31\t13\t27\t5
14\tFebruár-szökőév\t29\t7\t26\t9";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $honap[$temp3[0]] = [$temp3[1], (int) $temp3[2], (int) $temp3[3], (int) $temp3[4], (int) $temp3[5]];
    }

    $e7 = $evszam[$ev][0];
    $f7 = $evszam[$ev][1];
    $g7 = $evszam[$ev][2];

    $d8 = ($ho < 3 && $evszam[$ev][3] ? 12 : 0);
    $e8 = $honap[$ho + $d8][2];
    $f8 = $honap[$ho + $d8][3];
    $g8 = $honap[$ho + $d8][4];

    $h8 = $honap[$ho + $d8][1];

    $e9 = $h8 - $nap;
    $f9 = $h8 - $nap;
    $g9 = $h8 - $nap;

    $e10 = $e7 + $e8 + $e9;
    $f10 = $f7 + $f8 + $f9;
    $g10 = $g7 + $g8 + $g9;

    $e11 = ($e10 < 24 ? 0 : -23);
    $f11 = ($f10 < 29 ? 0 : -28);
    $g11 = ($g10 < 34 ? 0 : -33);

    $e12 = $e10 + $e11;
    $f12 = $f10 + $f11;
    $g12 = $g10 + $g11;

    $e13 = ($e12 > 23 ? $e12 - 23 : $e12);
    $f13 = ($f12 > 28 ? $f12 - 28 : $f12);
    $g13 = ($g12 > 33 ? $g12 - 33 : $g12);

    $e13 = ($e13 > 23 ? $e13 - 23 : $e13);
    $f13 = ($f13 > 28 ? $f13 - 28 : $f13);
    $g13 = ($g13 > 33 ? $g13 - 33 : $g13);

    $marker1 = $e13;
    $marker2 = $f13;
    $marker3 = $g13;

    $temp = "1\t64\t35\t\tProduktív Művészi
2\t21\t65\t\tGondolkodó Harmónikus
3\t21\t65\t\tGondolkodó Harmónikus
4\t93\t82\t1\tProduktív Vegyes
5\t43\t41\t\tHarmónikus Vegyes
6\t00\t99\t1\tGondolkodó Harmónikus
7\t57\t88\t\tProduktív Gondolkodó
8\t07\t41\t\tGyakorlati Gondolkodó
9\t29\t35\t\tGyakorlati Gondolkodó
10\t86\t35\t\tProduktív Művészi
11\t29\t82\t1\tProduktív Gondolkodó
12\t86\t41\t\tProduktív Művészi
13\t14\t71\t\tGondolkodó Harmónikus
14\t50\t59\t\tHarmónikus Vegyes
15\t78\t65\t\tProduktív Vegyes
16\t93\t24\t\tProduktív Művészi
17\t84\t71\t\tProduktív Vegyes
18\t29\t41\t\tGyakorlati Gondolkodó
19\t26\t71\t\tGondolkodó Harmónikus
20\t99\t82\t1\tProduktív Vegyes
21\t07\t76\t\tGondolkodó Harmónikus
22\t14\t35\t\tGyakorlati Gondolkodó
23\t50\t65\t\tProduktív Gondolkodó
24\t26\t18\t\tGyakorlati Művészi
25\t29\t88\t1\tProduktív Gondolkodó
26\t93\t59\t\tProduktív Művészi
27\t57\t82\t\tProduktív Gondolkodó
28\t29\t29\t\tGyakorlati Vegyes
29\t29\t88\t1\tProduktív Gondolkodó
30\t71\t47\t\tProduktív Művészi
31\t07\t35\t\tGyakorlati Gondolkodó
32\t64\t59\t\tProduktív Vegyes
33\t29\t82\t1\tProduktív Gondolkodó";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $ize1[$temp3[0]] = [$temp3[1], (int) $temp3[2], (int) $temp3[3], $temp3[4]];
    }

    $d16 = $ize1[$g13][1];
    $d17 = $ize1[$g13][0];

    $temp = "1\t69\t95\tSzenvedélyes
2\t75\t27\tEgoisztikus vezető
3\t31\t45\tEmpatikus
4\t62\t77\tSzenvedélyes
5\t50\t59\tEmpatikus
6\t44\t68\tEmpatikus
7\t12\t45\tÖnfeláldozó
8\t06\t23\tHideg önfeláldozó
9\t81\t54\tEgoisztikus vezető
10\t25\t77\tÖnfeláldozó
11\t18\t50\tÖnfeláldozó
12\t44\t59\tEmpatikus
13\t25\t68\tÖnfeláldozó
14\t50\t77\tSzenvedélyes
15\t50\t99\tSzenvedélyes
16\t50\t36\tSzentimentális
17\t56\t41\tSzentimentális
18\t31\t14\tHideg
19\t99\t54\tEgoisztikus vezető
20\t44\t32\tSzentimentális
21\t62\t41\tSzentimentális
22\t25\t18\tHideg
23\t69\t59\tEgoisztikus vezető
24\t56\t41\tSzentimentális
25\t44\t68\tEmpatikus
26\t37\t41\tEmpatikus
27\t56\t73\tSzenvedélyes
28\t44\t73\tEmpatikus";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $ize2[$temp3[0]] = [(int) $temp3[1], (int) $temp3[2], $temp3[3]];
    }

    $d18 = $ize2[$f13][1];
    $d19 = $ize2[$f13][0];

    $temp = "1\t33\t55\tKözepes szangvinikus
2\t55\t72\tSzangvinikus
3\t15\t65\tKolerikus
4\t50\t72\tSzangvinikus
5\t30\t41\tKözepes szangvinikus
6\t75\t21\tFlegmatikus
7\t45\t72\tSzangvinikus
8\t60\t22\tFlegmatikus
9\t35\t28\tMelankólikus
10\t35\t49\tKözepes szangvinikus
11\t95\t22\tFlegmatikus
12\t30\t99\tKolerikus
13\t40\t61\tSzangvinikus
14\t20\t55\tKolerikus
15\t40\t28\tMelankólikus
16\t90\t21\tFlegmatikus
17\t50\t83\tSzangvinikus
18\t10\t45\tÉrzékeny kolerikus
19\t99\t55\tFlegmatikus szangvinikus
20\t30\t52\tKözepes szangvinikus
21\t20\t79\tKolerikus
22\t80\t69\tFlegmanikus szangvinikus
23\t25\t21\tMelankólikus";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $ize3[$temp3[0]] = [(int) $temp3[1], (int) $temp3[2], $temp3[3]];
    }

    $d20 = $ize3[$e13][1];
    $d21 = $ize3[$e13][0];

    $d22 = round(($d16 + $d17 + $d18 + $d19 + $d20 + $d21) / 6);

    $h16 = $d20 + $d21;
    $h17 = $d18 + $d19;
    $h18 = $d16 + $d17;
    $h19 = $h16 + $h17 + $h18;
    $h21 = $d17 + $d19 + $d21;
    $h22 = $d16 + $d18 + $d20;

    $e25 = $e13;
    $f25 = $f13;
    $g25 = $g13;

    $d28 = $d21;
    $e28 = $d20;
    $f28 = $d28 + $e28;

    $temp = "33-55\t1\t33\t55\tKözepes szangvinikus
55-72\t2\t55\t72\tSzangvinikus
15-65\t3\t15\t65\tKolerikus
50-72\t4\t50\t72\tSzangvinikus
30-41\t5\t30\t41\tKözepes szangvinikus
75-21\t6\t75\t21\tFlegmatikus
45-72\t7\t45\t72\tSzangvinikus
60-22\t8\t60\t22\tFlegmatikus
35-28\t9\t35\t28\tMelankólikus
35-49\t10\t35\t49\tKözepes szangvinikus
95-22\t11\t95\t22\tFlegmatikus
30-99\t12\t30\t99\tKolerikus
40-61\t13\t40\t61\tSzangvinikus
20-55\t14\t20\t55\tKolerikus
40-28\t15\t40\t28\tMelankólikus
90-21\t16\t90\t21\tFlegmatikus
50-83\t17\t50\t83\tSzangvinikus
10-45\t18\t10\t45\tÉrzékeny kolerikus
99-55\t19\t99\t55\tFlegmatikus szangvinikus
30-52\t20\t30\t52\tKözepes szangvinikus
20-79\t21\t20\t79\tKolerikus
80-69\t22\t80\t69\tFlegmanikus szangvinikus
25-21\t23\t25\t21\tMelankólikus";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $ize4[$temp3[0]] = [(int) $temp3[1], (int) $temp3[2], (int) $temp3[3], $temp3[4]];
    }

    $j28 = $d28 . "-" . $e28;
    $g28 = $ize4[$j28][3];

    $d29 = $d19;
    $e29 = $d18;
    $f29 = $d29 + $e29;

    $temp = "69-95\t1\t69\t95\tSzenvedélyes
75-27\t2\t75\t27\tEgoisztikus vezető
31-45\t3\t31\t45\tEmpatikus
62-77\t4\t62\t77\tSzenvedélyes
50-59\t5\t50\t59\tEmpatikus
44-68\t6\t44\t68\tEmpatikus
12-45\t7\t12\t45\tÖnfeláldozó
06-23\t8\t06\t23\tHideg önfeláldozó
81-54\t9\t81\t54\tEgoisztikus vezető
25-77\t10\t25\t77\tÖnfeláldozó
18-50\t11\t18\t50\tÖnfeláldozó
44-59\t12\t44\t59\tEmpatikus
25-68\t13\t25\t68\tÖnfeláldozó
50-77\t14\t50\t77\tSzenvedélyes
50-99\t15\t50\t99\tSzenvedélyes
50-36\t16\t50\t36\tSzentimentális
56-41\t17\t56\t41\tSzentimentális
31-14\t18\t31\t14\tHideg
99-54\t19\t99\t54\tEgoisztikus vezető
44-32\t20\t44\t32\tSzentimentális
62-41\t21\t62\t41\tSzentimentális
25-18\t22\t25\t18\tHideg
69-59\t23\t69\t59\tEgoisztikus vezető
56-41\t24\t56\t41\tSzentimentális
44-68\t25\t44\t68\tEmpatikus
37-41\t26\t37\t41\tEmpatikus
56-73\t27\t56\t73\tSzenvedélyes
44-73\t28\t44\t73\tEmpatikus";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $ize5[$temp3[0]] = [(int) $temp3[1], (int) $temp3[2], (int) $temp3[3], $temp3[4]];
    }

    $j29 = $d29 . "-" . $e29;
    $g29 = $ize5[$j29][3];

    $d30 = $d17;
    $e30 = $d16;
    $f30 = $d30 + $e30;

    $j30 = $d30 . "-" . $e30;

    $temp = "64-35\t1\t64\t35\t\tProduktív Művészi
21-65\t2\t21\t65\t\tGondolkodó Harmónikus
21-65\t3\t21\t65\t\tGondolkodó Harmónikus
93-82\t4\t93\t82\t1\tProduktív Vegyes
43-41\t5\t43\t41\t\tHarmónikus Vegyes
00-99\t6\t00\t99\t1\tGondolkodó Harmónikus
57-88\t7\t57\t88\t\tProduktív Gondolkodó
07-41\t8\t07\t41\t\tGyakorlati Gondolkodó
29-35\t9\t29\t35\t\tGyakorlati Gondolkodó
86-35\t10\t86\t35\t\tProduktív Művészi
29-82\t11\t29\t82\t1\tProduktív Gondolkodó
86-41\t12\t86\t41\t\tProduktív Művészi
14-71\t13\t14\t71\t\tGondolkodó Harmónikus
50-59\t14\t50\t59\t\tHarmónikus Vegyes
78-65\t15\t78\t65\t\tProduktív Vegyes
93-24\t16\t93\t24\t\tProduktív Művészi
84-71\t17\t84\t71\t\tProduktív Vegyes
29-41\t18\t29\t41\t\tGyakorlati Gondolkodó
26-71\t19\t26\t71\t\tGondolkodó Harmónikus
99-82\t20\t99\t82\t1\tProduktív Vegyes
07-76\t21\t07\t76\t\tGondolkodó Harmónikus
14-35\t22\t14\t35\t\tGyakorlati Gondolkodó
50-65\t23\t50\t65\t\tProduktív Gondolkodó
26-18\t24\t26\t18\t\tGyakorlati Művészi
29-88\t25\t29\t88\t1\tProduktív Gondolkodó
93-59\t26\t93\t59\t\tProduktív Művészi
57-82\t27\t57\t82\t\tProduktív Gondolkodó
29-29\t28\t29\t29\t\tGyakorlati Vegyes
29-88\t29\t29\t88\t1\tProduktív Gondolkodó
71-47\t30\t71\t47\t\tProduktív Művészi
07-35\t31\t07\t35\t\tGyakorlati Gondolkodó
64-59\t32\t64\t59\t\tProduktív Vegyes
29-82\t33\t29\t82\t1\tProduktív Gondolkodó";

    $temp1 = explode("\n", $temp);

    foreach ($temp1 as $temp2) {
        $temp3 = explode("\t", $temp2);
        $ize6[$temp3[0]] = [(int) $temp3[1], $temp3[2], $temp3[3], (int) $temp3[4], $temp3[5]];
    }

    $g30 = $ize6[$j30][4];
    $i30 = ($ize6[$j30][3] == 1 ? "Mesterszám" : "");

    $d31 = $d28 + $d29 + $d30;
    $e31 = $e28 + $e29 + $e30;
    $f31 = $f28 + $f29 + $f30;

    $pdf = "<table cellpadding=10 cellspacing=0>
<tr><td bgcolor=#ffccff>7. Korona csakra:</td><td bgcolor=#ffccff align=right>$d16</td><td>&nbsp;</td><td>Fizikai energia</td><td>$h16</td></tr>
<tr><td bgcolor=#cc66ff>6. Homlok csakra:</td><td bgcolor=#cc66ff align=right>$d17</td><td>&nbsp;</td><td>Érzelmi energia</td><td>$h17</td></tr>
<tr><td bgcolor=#00b0f0>5. Torok csakra:</td><td bgcolor=#00b0f0 align=right>$d18</td><td>&nbsp;</td><td>Szellemi energia</td><td>$h18</td></tr>
<tr><td bgcolor=#00b050>4. Szív csakra:</td><td bgcolor=#00b050 align=right>$d19</td><td>&nbsp;</td><td><b>Életenergia</b></td><td>$h19</td></tr>
<tr><td bgcolor=yellow>3. Köldök csakra:</td><td bgcolor=yellow align=right>$d20</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td bgcolor=#ffc000>2. Szakrális csakra:</td><td bgcolor=#ffc000 align=right>$d21</td><td>&nbsp;</td><td>Jobb agyfélteke</td><td>$h21</td></tr>
<tr><td bgcolor=red>1. Gyökér csakra:</td><td bgcolor=red align=right>$d22</td><td>&nbsp;</td><td>Bal agyfélteke</td><td>$h22</td></tr>
<tr><td colspan=5>&nbsp;</td></tr>";

    $pdf .= "<tr bgcolor=#e7e6e6><td colspan=2>&nbsp;</td><td align=center><b>Fizikai</b></td><td align=center><b>Érzelmi</b></td><td align=center><b>Intellektuális</b></td></tr>
<tr bgcolor=#e7e6e6><td colspan=2>A bioritmus markerei</td><td align=center><b>$e25</b></td><td align=center><b>$f25</b></td><td align=center><b>$g25</b></td></tr>
<tr bgcolor=#e7e6e6><td colspan=5>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td align=center><b>Jobb oldal</b></td><td align=center><b>Bal oldal</b></td><td colspan=2>&nbsp;</td></tr>
<tr><td>Fizikai kontúr</td><td align=center><b>$d28</b></td><td align=center><b>$e28</b></td><td>$f28</td><td>$g28</td></tr>
<tr><td>Érzelmi kontúr</td><td align=center><b>$d29</b></td><td align=center><b>$e29</b></td><td>$f29</td><td>$g29</td></tr>
<tr><td>Szellemi kontúr</td><td align=center><b>$d30</b></td><td align=center><b>$e30</b></td><td>$f30</td><td>$g30</td></tr>
<tr><td>&nbsp;</td><td align=center>$d31</td><td align=center>$e31</td><td>$f31</td><td>&nbsp;</td></tr>
<tr><td colspan=5>&nbsp;</td></tr>";
    if ($i30) {
        $pdf .= "<tr><td colspan=4>&nbsp;</td><td><b>\"Mesterszám\"</b></td></tr>";
    }

    $pdf .= "</table>";

    return $pdf;
}

add_shortcode('tervezo_form', 'tervezo_shortcode_render');
