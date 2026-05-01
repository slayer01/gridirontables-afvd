<?php
defined('ABSPATH') || exit;

class Gridirontables_AFVD_Shortcodes {

    private $colors_enqueued = false;

    public function __construct() {
        add_shortcode('gridirontables_afvd_standings', [$this, 'render_standings']);
        add_shortcode('gridirontables_afvd_schedule', [$this, 'render_schedule']);

        // Deprecated aliases for backwards compatibility with the previous prefixes.
        add_shortcode('dsfooboo_football_data_standings', [$this, 'render_standings']);
        add_shortcode('dsfooboo_football_data_schedule', [$this, 'render_schedule']);
        add_shortcode('footballdata_standings', [$this, 'render_standings']);
        add_shortcode('footballdata_schedule', [$this, 'render_schedule']);
        add_shortcode('afvdata_standings', [$this, 'render_standings']);
        add_shortcode('afvdata_schedule', [$this, 'render_schedule']);
    }

    private function enqueue_styles() {
        wp_enqueue_style('gridirontables_afvd_public', GRIDIRONTABLES_AFVD_PLUGIN_URL . 'public/css/gridirontables-afvd.css', [], GRIDIRONTABLES_AFVD_VERSION);

        if ($this->colors_enqueued) {
            return;
        }
        $this->colors_enqueued = true;

        $header_bg  = get_option('gridirontables_afvd_color_header_bg', '#333333');
        $header_txt = get_option('gridirontables_afvd_color_header_text', '#ffffff');
        $highlight  = get_option('gridirontables_afvd_color_highlight_bg', '');

        $css = ':root{';
        $css .= '--gridirontables_afvd_header_bg:' . esc_attr($header_bg) . ';';
        $css .= '--gridirontables_afvd_header_text:' . esc_attr($header_txt) . ';';
        if ($highlight) {
            $css .= '--gridirontables_afvd_highlight_bg:' . esc_attr($highlight) . ';';
        }
        $css .= '}';

        wp_add_inline_style('gridirontables_afvd_public', $css);
    }

    public function render_standings($atts) {
        $atts = shortcode_atts([
            'league'    => '',
            'group'     => '',
            'highlight' => '',
            'class'     => '',
        ], $atts, 'gridirontables_afvd_standings');

        $league_config = Gridirontables_AFVD_Admin::get_league_by_slug($atts['league']);
        if (!$league_config) {
            return $this->error(sprintf(
                /* translators: %s: league identifier */
                __('League "%s" not found. Check the slug in Settings → Gridirontables AFVD → Leagues.', 'gridirontables-afvd'),
                $atts['league']
            ));
        }

        $liga_code = $league_config['liga_code'];
        $highlight = $atts['highlight'] ?: ($league_config['team_name'] ?? '');

        $this->enqueue_styles();

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } else {
            $groups = Gridirontables_AFVD_DB::get_standing_groups($liga_code);
        }

        $output = '';
        $league_name = Gridirontables_AFVD_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'gridirontables_afvd_standings_wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $rows = Gridirontables_AFVD_DB::get_standings($liga_code, $gruppe);
                $output .= $this->build_standings_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = Gridirontables_AFVD_DB::get_standings($liga_code);
            $output .= $this->build_standings_table($rows, $highlight);
        }

        $output .= '</div>';
        $output .= $this->disclaimer();

        return $output;
    }

    public function render_schedule($atts) {
        $atts = shortcode_atts([
            'league'    => '',
            'group'     => '',
            'home_only' => '',
            'show'      => 'all',
            'limit'     => 0,
            'highlight' => '',
            'class'     => '',
        ], $atts, 'gridirontables_afvd_schedule');

        $league_config = Gridirontables_AFVD_Admin::get_league_by_slug($atts['league']);
        if (!$league_config) {
            return $this->error(sprintf(
                /* translators: %s: league identifier */
                __('League "%s" not found. Check the slug in Settings → Gridirontables AFVD → Leagues.', 'gridirontables-afvd'),
                $atts['league']
            ));
        }

        $liga_code = $league_config['liga_code'];
        $highlight = $atts['highlight'] ?: ($league_config['team_name'] ?? '');

        $this->enqueue_styles();

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } else {
            $groups = Gridirontables_AFVD_DB::get_schedule_groups($liga_code);
        }

        $home_only  = !empty($atts['home_only']);
        $query_args = [
            'show'      => $atts['show'],
            'limit'     => (int) $atts['limit'],
            'team_name' => $home_only ? $highlight : '',
            'home_only' => $home_only,
        ];

        $output = '';
        $league_name = Gridirontables_AFVD_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'gridirontables_afvd_schedule_wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $args = array_merge($query_args, ['gruppe' => $gruppe]);
                $rows = Gridirontables_AFVD_DB::get_schedule($liga_code, $args);
                if (empty($rows) && $home_only) {
                    continue;
                }
                $output .= $this->build_schedule_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = Gridirontables_AFVD_DB::get_schedule($liga_code, $query_args);
            $output .= $this->build_schedule_table($rows, $highlight);
        }

        $output .= '</div>';
        $output .= $this->disclaimer();

        return $output;
    }

    private function build_standings_table($rows, $highlight, $gruppe = null) {
        if (empty($rows)) {
            return '<p>' . esc_html__('No standings data available.', 'gridirontables-afvd') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="gridirontables_afvd_group_header">'
                /* translators: %s: group name/letter */
                . sprintf(esc_html__('Group %s', 'gridirontables-afvd'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="gridirontables_afvd_league_table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Rank', 'gridirontables-afvd') . '</th>';
        $output .= '<th>' . esc_html__('Team', 'gridirontables-afvd') . '</th>';
        $output .= '<th>' . esc_html__('P+', 'gridirontables-afvd') . '</th>';
        $output .= '<th>' . esc_html__('P-', 'gridirontables-afvd') . '</th>';
        $output .= '<th class="gridirontables_afvd_nomobile">' . esc_html__('TD+', 'gridirontables-afvd') . '</th>';
        $output .= '<th class="gridirontables_afvd_nomobile">' . esc_html__('TD-', 'gridirontables-afvd') . '</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        foreach ($rows as $i => $row) {
            $is_highlight = $highlight && false !== stripos($row['team'], $highlight);
            $row_class = [];
            if ($i % 2 === 1) {
                $row_class[] = 'odd';
            }
            if ($is_highlight) {
                $row_class[] = 'gridirontables_afvd_highlight';
            }
            $class_attr = !empty($row_class) ? ' class="' . esc_attr(implode(' ', $row_class)) . '"' : '';

            $output .= '<tr' . $class_attr . '>';
            $output .= '<td>' . esc_html($row['platz']) . '</td>';
            $output .= '<td>' . esc_html($row['team']) . '</td>';
            $output .= '<td>' . esc_html($row['p_plus']) . '</td>';
            $output .= '<td>' . esc_html($row['p_minus']) . '</td>';
            $output .= '<td class="gridirontables_afvd_nomobile">' . esc_html($row['td_plus']) . '</td>';
            $output .= '<td class="gridirontables_afvd_nomobile">' . esc_html($row['td_minus']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    private function build_schedule_table($rows, $highlight, $gruppe = null) {
        if (empty($rows)) {
            return '<p>' . esc_html__('No schedule data available.', 'gridirontables-afvd') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="gridirontables_afvd_group_header">'
                /* translators: %s: group name/letter */
                . sprintf(esc_html__('Group %s', 'gridirontables-afvd'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="gridirontables_afvd_league_table gridirontables_afvd_schedule_table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Date', 'gridirontables-afvd') . '</th>';
        $output .= '<th>' . esc_html__('Kickoff', 'gridirontables-afvd') . '</th>';
        $output .= '<th>' . esc_html__('Home', 'gridirontables-afvd') . '</th>';
        $output .= '<th>' . esc_html__('Away', 'gridirontables-afvd') . '</th>';
        $output .= '<th class="gridirontables_afvd_nomobile">' . esc_html__('Score', 'gridirontables-afvd') . '</th>';
        $output .= '<th>' . esc_html__('Stadium', 'gridirontables-afvd') . '</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        foreach ($rows as $i => $row) {
            $home_highlight = $highlight && false !== stripos($row['heim'], $highlight);
            $away_highlight = $highlight && false !== stripos($row['gast'], $highlight);

            $row_class = ($i % 2 === 1) ? ' class="odd"' : '';

            $date_display = $row['datum1'] ? wp_date('j. F Y', strtotime($row['datum1'])) : '';

            $score = (int) $row['td_heim'] . ':' . (int) $row['td_gast'];
            if (0 === (int) $row['td_heim'] && 0 === (int) $row['td_gast'] && !empty($row['datum1']) && strtotime($row['datum1']) > time()) {
                $score = '-:-';
            }

            $output .= '<tr' . $row_class . '>';
            $output .= '<td data-label="' . esc_attr__('Date', 'gridirontables-afvd') . '" class="gridirontables_afvd_num">' . esc_html($date_display) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Kickoff', 'gridirontables-afvd') . '" class="gridirontables_afvd_num">' . esc_html($row['kickoff']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Home', 'gridirontables-afvd') . '"' . ($home_highlight ? ' class="gridirontables_afvd_highlight"' : '') . '>' . esc_html($row['heim']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Away', 'gridirontables-afvd') . '"' . ($away_highlight ? ' class="gridirontables_afvd_highlight"' : '') . '>' . esc_html($row['gast']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Score', 'gridirontables-afvd') . '" class="gridirontables_afvd_num gridirontables_afvd_nomobile">' . esc_html($score) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Stadium', 'gridirontables-afvd') . '">' . esc_html($row['stadion']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    private function disclaimer() {
        return '<span class="gridirontables_afvd_disclaimer">'
            . sprintf(
                /* translators: %s: link to AFVD */
                esc_html__('Data provided by %s', 'gridirontables-afvd'),
                '<a href="https://www.afvd.de" target="_blank" rel="noopener">AFVD</a>'
            )
            . '</span>';
    }

    private function error($message) {
        if (current_user_can('edit_posts')) {
            return '<p class="gridirontables_afvd_error"><strong>Gridirontables AFVD:</strong> ' . esc_html($message) . '</p>';
        }
        return '';
    }
}
