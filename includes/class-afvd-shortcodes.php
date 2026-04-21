<?php
defined('ABSPATH') || exit;

class AFVD_Shortcodes {

    private $team_name;

    public function __construct() {
        $this->team_name = get_option('afvd_data_team_name', '');

        add_shortcode('afvd_standings', [$this, 'render_standings']);
        add_shortcode('afvd_schedule', [$this, 'render_schedule']);
    }

    /**
     * [afvd_standings league="mensteam" group="A" highlight="Wetterau Bulls"]
     */
    public function render_standings($atts) {
        $atts = shortcode_atts([
            'league'    => '',
            'group'     => '',
            'highlight' => '',
            'class'     => '',
        ], $atts, 'afvd_standings');

        $liga_code = $this->resolve_liga_code($atts['league']);
        if (!$liga_code) {
            return $this->error(__('League not found.', 'afvd-data'));
        }

        $highlight = $atts['highlight'] ?: $this->team_name;

        wp_enqueue_style('afvd-data-public', AFVD_DATA_PLUGIN_URL . 'public/css/afvd-data.css', [], AFVD_DATA_VERSION);

        // Determine groups to render
        $league_config = AFVD_Admin::get_league_by_slug($atts['league']);
        $groups = [];

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } elseif ($league_config && !empty($league_config['groups'])) {
            $groups = array_map('trim', explode(',', $league_config['groups']));
        }

        $output = '';
        $league_name = AFVD_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'afvd-standings-wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $rows = AFVD_DB::get_standings($liga_code, $gruppe);
                $output .= $this->build_standings_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = AFVD_DB::get_standings($liga_code);
            $output .= $this->build_standings_table($rows, $highlight);
        }

        $output .= '</div>';
        $output .= $this->disclaimer();

        return $output;
    }

    /**
     * [afvd_schedule league="mensteam" group="A" home_only="1" show="upcoming" highlight="Wetterau Bulls"]
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
        ], $atts, 'afvd_schedule');

        $liga_code = $this->resolve_liga_code($atts['league']);
        if (!$liga_code) {
            return $this->error(__('League not found.', 'afvd-data'));
        }

        $highlight = $atts['highlight'] ?: $this->team_name;

        wp_enqueue_style('afvd-data-public', AFVD_DATA_PLUGIN_URL . 'public/css/afvd-data.css', [], AFVD_DATA_VERSION);

        // Determine groups to render
        $league_config = AFVD_Admin::get_league_by_slug($atts['league']);
        $groups = [];

        if (!empty($atts['group'])) {
            $groups = [$atts['group']];
        } elseif ($league_config && !empty($league_config['groups'])) {
            $groups = array_map('trim', explode(',', $league_config['groups']));
        }

        $query_args = [
            'show'      => $atts['show'],
            'limit'     => (int) $atts['limit'],
            'team_name' => $highlight,
            'home_only' => !empty($atts['home_only']),
        ];

        $output = '';
        $league_name = AFVD_DB::get_league_name($liga_code);
        $output .= '<h2>' . esc_html($league_name) . '</h2>';

        $wrapper_class = 'afvd-schedule-wrap';
        if ($atts['class']) {
            $wrapper_class .= ' ' . esc_attr($atts['class']);
        }
        $output .= '<div class="' . $wrapper_class . '">';

        if (!empty($groups)) {
            foreach ($groups as $gruppe) {
                $args = array_merge($query_args, ['gruppe' => $gruppe]);
                $rows = AFVD_DB::get_schedule($liga_code, $args);
                $output .= $this->build_schedule_table($rows, $highlight, $gruppe);
            }
        } else {
            $rows = AFVD_DB::get_schedule($liga_code, $query_args);
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
            return '<p>' . esc_html__('No standings data available.', 'afvd-data') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="afvd-group-header">'
                . sprintf(esc_html__('Group %s', 'afvd-data'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="afvd-league-table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Rank', 'afvd-data') . '</th>';
        $output .= '<th>' . esc_html__('Team', 'afvd-data') . '</th>';
        $output .= '<th>' . esc_html__('P+', 'afvd-data') . '</th>';
        $output .= '<th>' . esc_html__('P-', 'afvd-data') . '</th>';
        $output .= '<th class="afvd-nomobile">' . esc_html__('TD+', 'afvd-data') . '</th>';
        $output .= '<th class="afvd-nomobile">' . esc_html__('TD-', 'afvd-data') . '</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        foreach ($rows as $i => $row) {
            $is_highlight = $highlight && false !== stripos($row['team'], $highlight);
            $row_class = [];
            if ($i % 2 === 1) {
                $row_class[] = 'odd';
            }
            if ($is_highlight) {
                $row_class[] = 'afvd-highlight';
            }
            $class_attr = !empty($row_class) ? ' class="' . esc_attr(implode(' ', $row_class)) . '"' : '';

            $output .= '<tr' . $class_attr . '>';
            $output .= '<td>' . esc_html($row['platz']) . '</td>';
            $output .= '<td>' . esc_html($row['team']) . '</td>';
            $output .= '<td>' . esc_html($row['p_plus']) . '</td>';
            $output .= '<td>' . esc_html($row['p_minus']) . '</td>';
            $output .= '<td class="afvd-nomobile">' . esc_html($row['td_plus']) . '</td>';
            $output .= '<td class="afvd-nomobile">' . esc_html($row['td_minus']) . '</td>';
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
            return '<p>' . esc_html__('No schedule data available.', 'afvd-data') . '</p>';
        }

        $output = '';
        if ($gruppe) {
            $output .= '<span class="afvd-group-header">'
                . sprintf(esc_html__('Group %s', 'afvd-data'), esc_html($gruppe))
                . '</span>';
        }

        $output .= '<table class="afvd-league-table afvd-schedule-table">';
        $output .= '<thead><tr>';
        $output .= '<th>' . esc_html__('Date', 'afvd-data') . '</th>';
        $output .= '<th>' . esc_html__('Kickoff', 'afvd-data') . '</th>';
        $output .= '<th>' . esc_html__('Home', 'afvd-data') . '</th>';
        $output .= '<th>' . esc_html__('Away', 'afvd-data') . '</th>';
        $output .= '<th class="afvd-nomobile">' . esc_html__('Score', 'afvd-data') . '</th>';
        $output .= '<th>' . esc_html__('Stadium', 'afvd-data') . '</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        foreach ($rows as $i => $row) {
            $home_highlight = $highlight && false !== stripos($row['heim'], $highlight);
            $away_highlight = $highlight && false !== stripos($row['gast'], $highlight);

            $row_class = [];
            if ($i % 2 === 1) {
                $row_class[] = 'odd';
            }
            $class_attr = !empty($row_class) ? ' class="' . esc_attr(implode(' ', $row_class)) . '"' : '';

            $date_display = $row['datum1'] ? wp_date(get_option('date_format'), strtotime($row['datum1'])) : '';

            $score = (int) $row['td_heim'] . ':' . (int) $row['td_gast'];
            if (0 === (int) $row['td_heim'] && 0 === (int) $row['td_gast'] && !empty($row['datum1']) && strtotime($row['datum1']) > time()) {
                $score = '-:-';
            }

            $output .= '<tr' . $class_attr . '>';
            $output .= '<td data-label="' . esc_attr__('Date', 'afvd-data') . '" class="afvd-num">' . esc_html($date_display) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Kickoff', 'afvd-data') . '" class="afvd-num">' . esc_html($row['kickoff']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Home', 'afvd-data') . '"' . ($home_highlight ? ' class="afvd-highlight"' : '') . '>' . esc_html($row['heim']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Away', 'afvd-data') . '"' . ($away_highlight ? ' class="afvd-highlight"' : '') . '>' . esc_html($row['gast']) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Score', 'afvd-data') . '" class="afvd-highlight afvd-num afvd-nomobile">' . esc_html($score) . '</td>';
            $output .= '<td data-label="' . esc_attr__('Stadium', 'afvd-data') . '">' . esc_html($row['stadion']) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    /**
     * Resolve a league attribute to a liga_code.
     * Accepts either a slug (from config) or a direct liga_code.
     */
    private function resolve_liga_code($league_attr) {
        if (empty($league_attr)) {
            return null;
        }

        // First try as a slug
        $config = AFVD_Admin::get_league_by_slug($league_attr);
        if ($config) {
            return $config['liga_code'];
        }

        // Fall back: maybe they passed the liga_code directly
        return $league_attr;
    }

    /**
     * Disclaimer linking to AFVD.
     */
    private function disclaimer() {
        return '<span class="afvd-disclaimer">'
            . sprintf(
                /* translators: %s: link to AFVD */
                esc_html__('Data provided by %s', 'afvd-data'),
                '<a href="https://www.afvd.de" target="_blank" rel="noopener">AFVD</a>'
            )
            . '</span>';
    }

    /**
     * Render a shortcode error (only visible to logged-in editors).
     */
    private function error($message) {
        if (current_user_can('edit_posts')) {
            return '<p class="afvd-error"><strong>AFVD Data:</strong> ' . esc_html($message) . '</p>';
        }
        return '';
    }
}
