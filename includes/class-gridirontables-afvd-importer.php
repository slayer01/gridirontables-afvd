<?php
defined('ABSPATH') || exit;

class Gridirontables_AFVD_Importer {

    private $base_url;

    public function __construct() {
        $this->base_url = rtrim(get_option('gridirontables_afvd_api_base_url', 'http://vereine.football-verband.de/'), '/');
    }

    public function import_all_active() {
        $leagues = get_option('gridirontables_afvd_leagues', []);
        $results = [];

        foreach ($leagues as $league) {
            if (empty($league['active'])) {
                continue;
            }

            $result = $this->import_league($league['liga_code']);

            if (is_wp_error($result)) {
                $results[$league['liga_code']] = ['error' => $result->get_error_message()];
            } else {
                $results[$league['liga_code']] = $result;
            }
        }

        update_option('gridirontables_afvd_last_sync', time());

        return $results;
    }

    public function import_league($liga_code) {
        $liga_code = sanitize_text_field($liga_code);
        $now = current_time('mysql');

        $standings_result = $this->import_standings($liga_code, $now);
        $schedule_result  = $this->import_schedule($liga_code, $now);

        if (is_wp_error($standings_result) && is_wp_error($schedule_result)) {
            return new WP_Error(
                'gridirontables_afvd_import_failed',
                sprintf(
                    /* translators: 1: standings error, 2: schedule error */
                    __('Standings: %1$s | Schedule: %2$s', 'gridirontables-afvd'),
                    $standings_result->get_error_message(),
                    $schedule_result->get_error_message()
                )
            );
        }

        $counts = Gridirontables_AFVD_DB::get_counts($liga_code);

        return [
            'liga_code'       => $liga_code,
            'standings_count' => $counts['standings'],
            'schedule_count'  => $counts['schedule'],
            'standings_error' => is_wp_error($standings_result) ? $standings_result->get_error_message() : null,
            'schedule_error'  => is_wp_error($schedule_result) ? $schedule_result->get_error_message() : null,
        ];
    }

    private function import_standings($liga_code, $import_time) {
        $url = $this->base_url . '/xmltabelle.php5?Liga=' . urlencode($liga_code);
        $xml = $this->fetch_xml($url);

        if (is_wp_error($xml)) {
            return $xml;
        }

        $count = 0;
        foreach ($xml->children() as $row) {
            Gridirontables_AFVD_DB::upsert_standing([
                'liga_code'   => sanitize_text_field((string) $row->Liga),
                'bezeichnung' => sanitize_text_field((string) $row->Bezeichnung),
                'gruppe'      => sanitize_text_field((string) $row->Gruppe),
                'platz'       => (int) $row->Platz,
                'team'        => sanitize_text_field((string) $row->Team),
                'teamname'    => sanitize_text_field((string) $row->Teamname),
                'kuerzel'     => sanitize_text_field((string) $row->Kuerzel),
                'p_plus'      => (int) $row->PPlus,
                'p_minus'     => (int) $row->PMinus,
                'td_plus'     => (int) $row->TDPlus,
                'td_minus'    => (int) $row->TDMinus,
                'imported_at' => $import_time,
            ]);
            $count++;
        }

        if ($count > 0) {
            Gridirontables_AFVD_DB::cleanup_stale(Gridirontables_AFVD_DB::standings_table(), $liga_code, $import_time);
        }

        return $count;
    }

    private function import_schedule($liga_code, $import_time) {
        $url = $this->base_url . '/xmlspielplan.php5?Liga=' . urlencode($liga_code);
        $xml = $this->fetch_xml($url);

        if (is_wp_error($xml)) {
            return $xml;
        }

        $count = 0;
        foreach ($xml->children() as $row) {
            $datum1 = (string) $row->Datum1;
            $datum2 = (string) $row->Datum2;

            Gridirontables_AFVD_DB::upsert_game([
                'game_id'      => sanitize_text_field((string) $row->ID),
                'liga_code'    => sanitize_text_field((string) $row->Liga),
                'bezeichnung'  => sanitize_text_field((string) $row->Bezeichnung),
                'gruppe'       => sanitize_text_field((string) $row->Gruppe),
                'datum1'       => $this->parse_date($datum1),
                'datum2'       => $this->parse_date($datum2),
                'kickoff'      => sanitize_text_field((string) $row->Kickoff),
                'heim'         => sanitize_text_field((string) $row->Heim),
                'heimname'     => sanitize_text_field((string) $row->Heimname),
                'heimkuerzel'  => sanitize_text_field((string) $row->Heimkuerzel),
                'gast'         => sanitize_text_field((string) $row->Gast),
                'gastname'     => sanitize_text_field((string) $row->Gastname),
                'gastkuerzel'  => sanitize_text_field((string) $row->Gastkuerzel),
                'td_heim'      => (int) $row->TDHeim,
                'td_gast'      => (int) $row->TDGast,
                'q1_heim'      => (int) $row->Q1Heim,
                'q1_gast'      => (int) $row->Q1Gast,
                'q2_heim'      => (int) $row->Q2Heim,
                'q2_gast'      => (int) $row->Q2Gast,
                'q3_heim'      => (int) $row->Q3Heim,
                'q3_gast'      => (int) $row->Q3Gast,
                'q4_heim'      => (int) $row->Q4Heim,
                'q4_gast'      => (int) $row->Q4Gast,
                'stadion'      => sanitize_text_field((string) $row->Stadion),
                'kommentar'    => sanitize_text_field((string) $row->Kommentar),
                'imported_at'  => $import_time,
            ]);
            $count++;
        }

        if ($count > 0) {
            Gridirontables_AFVD_DB::cleanup_stale(Gridirontables_AFVD_DB::schedule_table(), $liga_code, $import_time);
        }

        return $count;
    }

    private function fetch_xml($url) {
        $response = wp_remote_get($url, [
            'timeout'    => 30,
            'user-agent' => 'Gridirontables-AFVD-WordPress-Plugin/' . GRIDIRONTABLES_AFVD_VERSION,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if (200 !== $code) {
            return new WP_Error(
                'gridirontables_afvd_http_error',
                /* translators: %d: HTTP status code */
                sprintf(__('HTTP %d from data API', 'gridirontables-afvd'), $code)
            );
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return new WP_Error('gridirontables_afvd_empty_response', __('Empty response from data API', 'gridirontables-afvd'));
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (false === $xml) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $msg = !empty($errors) ? $errors[0]->message : __('Unknown XML parse error', 'gridirontables-afvd');
            /* translators: %s: XML error message */
            return new WP_Error('gridirontables_afvd_xml_error', sprintf(__('XML parse error: %s', 'gridirontables-afvd'), trim($msg)));
        }

        return $xml;
    }

    private function parse_date($date_string) {
        if (empty($date_string)) {
            return null;
        }
        $ts = strtotime($date_string);
        return $ts ? gmdate('Y-m-d', $ts) : null;
    }
}
