<?php
defined('ABSPATH') || exit;

class DSFooboo_Football_Data_Shortcodes {

    private $colors_enqueued = false;

    public function __construct() {
        add_shortcode('dsfooboo_football_data_standings', [$this, 'render_standings']);
        add_shortcode('dsfooboo_football_data_schedule', [$this, 'render_schedule']);

        // Deprecated aliases for backwards compatibility with the previous prefixes.
        add_shortcode('footballdata_standings', [$this, 'render_standings']);
        add_shortcode('footballdata_schedule', [$this, 'render_schedule']);
        add_shortcode('afvdata_standings', [$this, 'render_standings']);
        add_shortcode('afvdata_schedule', [$this, 'render_schedule']);
    }

    private function enqueue_styles() {
        wp_enqueue_style('dsfooboo_football_data_public', DSFOOBOO_FOOTBALL_DATA_PLUGIN_URL . 'public/css/dsfooboo-football-data.css', [], DSFOOBOO_FOOTBALL_DATA_VERSION);

        if ($this->colors_enqueued) {
            return;
        }
        $this->colors_enqueued = true;

        $header_bg  = get_option('dsfooboo_football_data_color_header_bg', '#333333');
        $header_txt = get_option('dsfooboo_football_data_color_header_text', '#ffffff');
        $highlight  = get_option('dsfooboo_football_data_color_highlight_bg', '');

        $css = ':root{';
        $css .= '--dsfooboo_football_data_header_bg:' . esc_attr($header_bg) . ';';
        $css .= '--dsfooboo_football_data_header_text:' . esc_attr($header_txt) . ';';
        if ($highlight) {
            $css .= '--dsfooboo_football_data_highlight_bg:' . esc_attr($highlight) . ';';
        }
        $css .= '}';

        wp_add_inline_style('dsfooboo_football_data_public', $css);
    }

    public function render_standings($atts) {
        $atts = shortcode_atts([
            'league'    => '',
            'group'     => '',
            'highlight' => '',
            'class'     => '',
        ], $atts, 'dsfooboo_football_data_standings');

        $league_config = DSFooboo_Football_Data_Admin::get_league_by_slug($atts['league']);
        if (!$league_config) {
            return $this->error(sprintf(
                /* translators: %s: league identifier */
                __('League "%s" not found. Check the slug in Settings → DSFOOBOO Football Data → Leagues.', 'dsfooboo_football_data'),
                $atts['league']
            ));
        }

        $liga_code = $league_config['liga_code'];
        $highlight = $atts['highlight'] ?: ($league_config['team_name'] ?? '');

        $this->enqueue_styles();

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } else {
            $groups = DSFooboo_Football_Data_DB::get_standing_groups($liga_code);
        }

        $output = '';
        $league_name = DSFooboo_Football_Data_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'dsfooboo_football_data_standings_wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $rows = DSFooboo_Football_Data_DB::get_standings($liga_code, $gruppe);
                $output .= $this->build_standings_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = DSFooboo_Football_Data_DB::get_standings($liga_code);
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
        ], $atts, 'dsfooboo_football_data_schedule');

        $league_config = DSFooboo_Football_Data_Admin::get_league_by_slug($atts['league']);
        if (!$league_config) {
            return $this->error(sprintf(
                /* translators: %s: league identifier */
                __('League "%s" not found. Check the slug in Settings → DSFOOBOO Football Data → Leagues.', 'dsfooboo_football_data'),
                $atts['league']
            ));
        }

        $liga_code = $league_config['liga_code'];
        $highlight = $atts['highlight'] ?: ($league_config['team_name'] ?? '');

        $this->enqueue_styles();

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } else {
            $groups = DSFooboo_Football_Data_DB::get_schedule_groups($liga_code);
        }

        $home_only  = !empty($atts['home_only']);
        $query_args = [
            'show'      => $atts['show'],
            'limit'     => (int) $atts['limit'],
            'team_name' => $home_only ? $highlight : '',
            'home_only' => $home_only,
        ];

        $output = '';
        $league_name = DSFooboo_Football_Data_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'dsfooboo_football_data_schedule_wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $args = array_merge($query_args, ['gruppe' => $gruppe]);
                $rows = DSFooboo_Football_Data_DB::get_schedule($liga_code, $args);
                if (empty($rows) && $home_only) {
                    continue;
                }
                $output .= $this->build_schedule_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = DSFooboo_Football_Data_DB::get_schedule($liga_code, $query_args);
            $output .= $this->build_schedule_table($rows, $highlight);
        }

        $output .= '</div>';
        $output .= $this->disclaimer();

        return $output;
    }

    private function build_standings_table($rows, $highlight, $gruppe = null) {
        if (empty($rows)) {
            return '<p>' . esc_html__('No standings data available.', 'dsfooboo_football_data') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="dsfooboo_football_data_group_header">'
                /* translators: %s: group name/letter */
                . sprintf(esc_html__('Group %s', 'dsfooboo_football_data'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="dsfooboo_football_data_league_table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Rank', 'dsfooboo_football_data') . '</th>';
        $output .= '<th>' . esc_html__('Team', 'dsfooboo_football_data') . '</th>';
        $output .= '<th>' . esc_html__('P+', 'dsfooboo_football_data') . '</th>';
        $output .= '<th>' . esc_html__('P-', 'dsfooboo_football_data') . '</th>';
        $output .= '<th class="dsfooboo_football_data_nomobile">' . esc_html__('TD+', 'dsfooboo_football_data') . '</th>';
        $output .= '<th class="dsfooboo_football_data_nomobile">' . esc_html__('TD-', 'dsfooboo_football_data') . '</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        foreach ($rows as $i => $row) {
            $is_highlight = $highlight && false !== stripos($row['team'], $highlight);
            $row_class = [];
            if ($i % 2 === 1) {
                $row_class[] = 'odd';
            }
            if ($is_highlight) {
                $row_class[] = 'dsfooboo_football_data_highlight';
            }
            $class_attr = !empty($row_class) ? ' class="' . esc_attr(implode(' ', $row_class)) . '"' : '';

            $output .= '<tr' . $class_attr . '>';
            $output .= '<td>' . esc_html($row['platz']) . '</td>';
            $output .= '<td>' . esc_html($row['team']) . '</td>';
            $output .= '<td>' . esc_html($row['p_plus']) . '</td>';
            $output .= '<td>' . esc_html($row['p_minus']) . '</td>';
            $output .= '<td class="dsfooboo_football_data_nomobile">' . esc_html($row['td_plus']) . '</td>';
            $output .= '<td class="dsfooboo_football_data_nomobile">' . esc_html($row['td_minus']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    private function build_schedule_table($rows, $highlight, $gruppe = null) {
        if (empty($rows)) {
            return '<p>' . esc_html__('No schedule data available.', 'dsfooboo_football_data') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="dsfooboo_football_data_group_header">'
                /* translators: %s: group name/letter */
                . sprintf(esc_html__('Group %s', 'dsfooboo_football_data'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="dsfooboo_football_data_league_table dsfooboo_football_data_schedule_table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Date', 'dsfooboo_football_data') . '</th>';
        $output .= '<th>' . esc_html__('Kickoff', 'dsfooboo_football_data') . '</th>';
        $output .= '<th>' . esc_html__('Home', 'dsfooboo_football_data') . '</th>';
        $output .= '<th>' . esc_html__('Away', 'dsfooboo_football_data') . '</th>';
        $output .= '<th class="dsfooboo_football_data_nomobile">' . esc_html__('Score', 'dsfooboo_football_data') . '</th>';
        $output .= '<th>' . esc_html__('Stadium', 'dsfooboo_football_data') . '</th>';
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
            $output .= '<td data-label="' . esc_attr__('Date', 'dsfooboo_football_data') . '" class="dsfooboo_football_data_num">' . esc_html($date_display) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Kickoff', 'dsfooboo_football_data') . '" class="dsfooboo_football_data_num">' . esc_html($row['kickoff']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Home', 'dsfooboo_football_data') . '"' . ($home_highlight ? ' class="dsfooboo_football_data_highlight"' : '') . '>' . esc_html($row['heim']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Away', 'dsfooboo_football_data') . '"' . ($away_highlight ? ' class="dsfooboo_football_data_highlight"' : '') . '>' . esc_html($row['gast']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Score', 'dsfooboo_football_data') . '" class="dsfooboo_football_data_num dsfooboo_football_data_nomobile">' . esc_html($score) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Stadium', 'dsfooboo_football_data') . '">' . esc_html($row['stadion']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    private function disclaimer() {
        return '<span class="dsfooboo_football_data_disclaimer">'
            . sprintf(
                /* translators: %s: link to AFVD */
                esc_html__('Data provided by %s', 'dsfooboo_football_data'),
                '<a href="https://www.afvd.de" target="_blank" rel="noopener">AFVD</a>'
            )
            . '</span>';
    }

    private function error($message) {
        if (current_user_can('edit_posts')) {
            return '<p class="dsfooboo_football_data_error"><strong>DSFOOBOO Football Data:</strong> ' . esc_html($message) . '</p>';
        }
        return '';
    }
}
