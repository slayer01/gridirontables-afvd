<?php
defined('ABSPATH') || exit;

class FootballData_Shortcodes {

    private $colors_enqueued = false;

    public function __construct() {
        add_shortcode('footballdata_standings', [$this, 'render_standings']);
        add_shortcode('footballdata_schedule', [$this, 'render_schedule']);

        // Deprecated aliases for backwards compatibility with the old afvdata_* prefix.
        add_shortcode('afvdata_standings', [$this, 'render_standings']);
        add_shortcode('afvdata_schedule', [$this, 'render_schedule']);
    }

    private function enqueue_styles() {
        wp_enqueue_style('footballdata-public', FOOTBALLDATA_PLUGIN_URL . 'public/css/football-data.css', [], FOOTBALLDATA_VERSION);

        if ($this->colors_enqueued) {
            return;
        }
        $this->colors_enqueued = true;

        $header_bg  = get_option('footballdata_color_header_bg', '#333333');
        $header_txt = get_option('footballdata_color_header_text', '#ffffff');
        $highlight  = get_option('footballdata_color_highlight_bg', '');

        $css = ':root{';
        $css .= '--footballdata-header-bg:' . esc_attr($header_bg) . ';';
        $css .= '--footballdata-header-text:' . esc_attr($header_txt) . ';';
        if ($highlight) {
            $css .= '--footballdata-highlight-bg:' . esc_attr($highlight) . ';';
        }
        $css .= '}';

        wp_add_inline_style('footballdata-public', $css);
    }

    /**
     * [footballdata_standings league="mensteam" group="A" highlight="Wetterau Bulls"]
     */
    public function render_standings($atts) {
        $atts = shortcode_atts([
            'league'    => '',
            'group'     => '',
            'highlight' => '',
            'class'     => '',
        ], $atts, 'footballdata_standings');

        $league_config = FootballData_Admin::get_league_by_slug($atts['league']);
        if (!$league_config) {
            return $this->error(sprintf(
                /* translators: %s: league identifier */
                __('League "%s" not found. Check the slug in Settings → FootballData → Leagues.', 'footballdata'),
                $atts['league']
            ));
        }

        $liga_code = $league_config['liga_code'];
        $highlight = $atts['highlight'] ?: ($league_config['team_name'] ?? '');

        $this->enqueue_styles();

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } else {
            $groups = FootballData_DB::get_standing_groups($liga_code);
        }

        $output = '';
        $league_name = FootballData_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'footballdata-standings-wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $rows = FootballData_DB::get_standings($liga_code, $gruppe);
                $output .= $this->build_standings_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = FootballData_DB::get_standings($liga_code);
            $output .= $this->build_standings_table($rows, $highlight);
        }

        $output .= '</div>';
        $output .= $this->disclaimer();

        return $output;
    }

    /**
     * [footballdata_schedule league="mensteam" group="A" home_only="1" show="upcoming" highlight="Wetterau Bulls"]
     */
    public function render_schedule($atts) {
        $atts = shortcode_atts([
            'league'    => '',
            'group'     => '',
            'home_only' => '',
            'show'      => 'all',
            'limit'     => 0,
            'highlight' => '',
            'class'     => '',
        ], $atts, 'footballdata_schedule');

        $league_config = FootballData_Admin::get_league_by_slug($atts['league']);
        if (!$league_config) {
            return $this->error(sprintf(
                /* translators: %s: league identifier */
                __('League "%s" not found. Check the slug in Settings → FootballData → Leagues.', 'footballdata'),
                $atts['league']
            ));
        }

        $liga_code = $league_config['liga_code'];
        $highlight = $atts['highlight'] ?: ($league_config['team_name'] ?? '');

        $this->enqueue_styles();

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } else {
            $groups = FootballData_DB::get_schedule_groups($liga_code);
        }

        $home_only  = !empty($atts['home_only']);
        $query_args = [
            'show'      => $atts['show'],
            'limit'     => (int) $atts['limit'],
            'team_name' => $home_only ? $highlight : '',
            'home_only' => $home_only,
        ];

        $output = '';
        $league_name = FootballData_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'footballdata-schedule-wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $args = array_merge($query_args, ['gruppe' => $gruppe]);
                $rows = FootballData_DB::get_schedule($liga_code, $args);
                if (empty($rows) && $home_only) {
                    continue;
                }
                $output .= $this->build_schedule_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = FootballData_DB::get_schedule($liga_code, $query_args);
            $output .= $this->build_schedule_table($rows, $highlight);
        }

        $output .= '</div>';
        $output .= $this->disclaimer();

        return $output;
    }

    /**
     * Build standings HTML table.
     */
    private function build_standings_table($rows, $highlight, $gruppe = null) {
        if (empty($rows)) {
            return '<p>' . esc_html__('No standings data available.', 'footballdata') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="footballdata-group-header">'
                /* translators: %s: group name/letter */
                . sprintf(esc_html__('Group %s', 'footballdata'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="footballdata-league-table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Rank', 'footballdata') . '</th>';
        $output .= '<th>' . esc_html__('Team', 'footballdata') . '</th>';
        $output .= '<th>' . esc_html__('P+', 'footballdata') . '</th>';
        $output .= '<th>' . esc_html__('P-', 'footballdata') . '</th>';
        $output .= '<th class="footballdata-nomobile">' . esc_html__('TD+', 'footballdata') . '</th>';
        $output .= '<th class="footballdata-nomobile">' . esc_html__('TD-', 'footballdata') . '</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        foreach ($rows as $i => $row) {
            $is_highlight = $highlight && false !== stripos($row['team'], $highlight);
            $row_class = [];
            if ($i % 2 === 1) {
                $row_class[] = 'odd';
            }
            if ($is_highlight) {
                $row_class[] = 'footballdata-highlight';
            }
            $class_attr = !empty($row_class) ? ' class="' . esc_attr(implode(' ', $row_class)) . '"' : '';

            $output .= '<tr' . $class_attr . '>';
            $output .= '<td>' . esc_html($row['platz']) . '</td>';
            $output .= '<td>' . esc_html($row['team']) . '</td>';
            $output .= '<td>' . esc_html($row['p_plus']) . '</td>';
            $output .= '<td>' . esc_html($row['p_minus']) . '</td>';
            $output .= '<td class="footballdata-nomobile">' . esc_html($row['td_plus']) . '</td>';
            $output .= '<td class="footballdata-nomobile">' . esc_html($row['td_minus']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    /**
     * Build schedule HTML table.
     */
    private function build_schedule_table($rows, $highlight, $gruppe = null) {
        if (empty($rows)) {
            return '<p>' . esc_html__('No schedule data available.', 'footballdata') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="footballdata-group-header">'
                /* translators: %s: group name/letter */
                . sprintf(esc_html__('Group %s', 'footballdata'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="footballdata-league-table footballdata-schedule-table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Date', 'footballdata') . '</th>';
        $output .= '<th>' . esc_html__('Kickoff', 'footballdata') . '</th>';
        $output .= '<th>' . esc_html__('Home', 'footballdata') . '</th>';
        $output .= '<th>' . esc_html__('Away', 'footballdata') . '</th>';
        $output .= '<th class="footballdata-nomobile">' . esc_html__('Score', 'footballdata') . '</th>';
        $output .= '<th>' . esc_html__('Stadium', 'footballdata') . '</th>';
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
            $output .= '<td data-label="' . esc_attr__('Date', 'footballdata') . '" class="footballdata-num">' . esc_html($date_display) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Kickoff', 'footballdata') . '" class="footballdata-num">' . esc_html($row['kickoff']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Home', 'footballdata') . '"' . ($home_highlight ? ' class="footballdata-highlight"' : '') . '>' . esc_html($row['heim']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Away', 'footballdata') . '"' . ($away_highlight ? ' class="footballdata-highlight"' : '') . '>' . esc_html($row['gast']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Score', 'footballdata') . '" class="footballdata-num footballdata-nomobile">' . esc_html($score) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Stadium', 'footballdata') . '">' . esc_html($row['stadion']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    /**
     * Disclaimer linking to the AFVD data source.
     */
    private function disclaimer() {
        return '<span class="footballdata-disclaimer">'
            . sprintf(
                /* translators: %s: link to AFVD */
                esc_html__('Data provided by %s', 'footballdata'),
                '<a href="https://www.afvd.de" target="_blank" rel="noopener">AFVD</a>'
            )
            . '</span>';
    }

    /**
     * Render a shortcode error (only visible to logged-in editors).
     */
    private function error($message) {
        if (current_user_can('edit_posts')) {
            return '<p class="footballdata-error"><strong>FootballData:</strong> ' . esc_html($message) . '</p>';
        }
        return '';
    }
}
