<?php
defined('ABSPATH') || exit;

class Gridirontables_AFVD_DB {

    public static function standings_table() {
        global $wpdb;
        return $wpdb->prefix . 'gridirontables_afvd_standings';
    }

    public static function schedule_table() {
        global $wpdb;
        return $wpdb->prefix . 'gridirontables_afvd_schedule';
    }

    /**
     * Pairs of [legacy_table, new_table] used by the migration.
     * The first matching legacy table that exists wins.
     */
    private static function legacy_table_pairs() {
        global $wpdb;
        return [
            'standings' => [
                $wpdb->prefix . 'dsfooboo_football_data_standings',
                $wpdb->prefix . 'footballdata_standings',
                $wpdb->prefix . 'afvdata_standings',
            ],
            'schedule' => [
                $wpdb->prefix . 'dsfooboo_football_data_schedule',
                $wpdb->prefix . 'footballdata_schedule',
                $wpdb->prefix . 'afvdata_schedule',
            ],
        ];
    }

    /**
     * Rename legacy *_standings / *_schedule tables to gridirontables_afvd_* if any exist.
     * Idempotent: bails out cleanly when nothing to migrate.
     */
    public static function migrate_from_legacy() {
        global $wpdb;

        $targets = [
            'standings' => self::standings_table(),
            'schedule'  => self::schedule_table(),
        ];

        foreach (self::legacy_table_pairs() as $kind => $legacy_tables) {
            $new = $targets[$kind];
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $new_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $new)) === $new;

            foreach ($legacy_tables as $old) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                $old_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $old)) === $old;
                if (!$old_exists) {
                    continue;
                }

                if (!$new_exists) {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                    $wpdb->query("RENAME TABLE {$old} TO {$new}");
                    $new_exists = true;
                } else {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                    $wpdb->query("DROP TABLE IF EXISTS {$old}");
                }
            }
        }
    }

    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $standings = self::standings_table();
        $schedule  = self::schedule_table();

        $sql = "CREATE TABLE {$standings} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            liga_code varchar(50) NOT NULL,
            bezeichnung varchar(255) NOT NULL DEFAULT '',
            gruppe varchar(50) NOT NULL DEFAULT '',
            platz int NOT NULL DEFAULT 0,
            team varchar(255) NOT NULL DEFAULT '',
            teamname varchar(255) NOT NULL DEFAULT '',
            kuerzel varchar(50) NOT NULL DEFAULT '',
            p_plus int NOT NULL DEFAULT 0,
            p_minus int NOT NULL DEFAULT 0,
            td_plus int NOT NULL DEFAULT 0,
            td_minus int NOT NULL DEFAULT 0,
            imported_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY standing_unique (liga_code, gruppe, kuerzel),
            KEY idx_liga_code (liga_code)
        ) {$charset_collate};

        CREATE TABLE {$schedule} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            game_id varchar(50) NOT NULL DEFAULT '',
            liga_code varchar(50) NOT NULL,
            bezeichnung varchar(255) NOT NULL DEFAULT '',
            gruppe varchar(50) NOT NULL DEFAULT '',
            datum1 date DEFAULT NULL,
            datum2 date DEFAULT NULL,
            kickoff varchar(10) NOT NULL DEFAULT '',
            heim varchar(255) NOT NULL DEFAULT '',
            heimname varchar(255) NOT NULL DEFAULT '',
            heimkuerzel varchar(50) NOT NULL DEFAULT '',
            gast varchar(255) NOT NULL DEFAULT '',
            gastname varchar(255) NOT NULL DEFAULT '',
            gastkuerzel varchar(50) NOT NULL DEFAULT '',
            td_heim int NOT NULL DEFAULT 0,
            td_gast int NOT NULL DEFAULT 0,
            q1_heim int NOT NULL DEFAULT 0,
            q1_gast int NOT NULL DEFAULT 0,
            q2_heim int NOT NULL DEFAULT 0,
            q2_gast int NOT NULL DEFAULT 0,
            q3_heim int NOT NULL DEFAULT 0,
            q3_gast int NOT NULL DEFAULT 0,
            q4_heim int NOT NULL DEFAULT 0,
            q4_gast int NOT NULL DEFAULT 0,
            stadion varchar(255) NOT NULL DEFAULT '',
            kommentar text,
            imported_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY game_unique (liga_code, game_id),
            KEY idx_liga_code (liga_code),
            KEY idx_datum (datum1)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option('gridirontables_afvd_db_version', GRIDIRONTABLES_AFVD_DB_VERSION);
    }

    public static function upsert_standing($data) {
        global $wpdb;
        $table = self::standings_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $wpdb->query($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "INSERT INTO {$table}
                (liga_code, bezeichnung, gruppe, platz, team, teamname, kuerzel,
                 p_plus, p_minus, td_plus, td_minus, imported_at)
            VALUES (%s, %s, %s, %d, %s, %s, %s, %d, %d, %d, %d, %s)
            ON DUPLICATE KEY UPDATE
                bezeichnung = VALUES(bezeichnung),
                platz = VALUES(platz),
                team = VALUES(team),
                teamname = VALUES(teamname),
                p_plus = VALUES(p_plus),
                p_minus = VALUES(p_minus),
                td_plus = VALUES(td_plus),
                td_minus = VALUES(td_minus),
                imported_at = VALUES(imported_at)",
            $data['liga_code'],
            $data['bezeichnung'],
            $data['gruppe'],
            $data['platz'],
            $data['team'],
            $data['teamname'],
            $data['kuerzel'],
            $data['p_plus'],
            $data['p_minus'],
            $data['td_plus'],
            $data['td_minus'],
            $data['imported_at']
        ));
    }

    public static function upsert_game($data) {
        global $wpdb;
        $table = self::schedule_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $wpdb->query($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "INSERT INTO {$table}
                (game_id, liga_code, bezeichnung, gruppe, datum1, datum2, kickoff,
                 heim, heimname, heimkuerzel, gast, gastname, gastkuerzel,
                 td_heim, td_gast, q1_heim, q1_gast, q2_heim, q2_gast,
                 q3_heim, q3_gast, q4_heim, q4_gast, stadion, kommentar, imported_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                    %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
                bezeichnung = VALUES(bezeichnung),
                gruppe = VALUES(gruppe),
                datum1 = VALUES(datum1),
                datum2 = VALUES(datum2),
                kickoff = VALUES(kickoff),
                heim = VALUES(heim),
                heimname = VALUES(heimname),
                heimkuerzel = VALUES(heimkuerzel),
                gast = VALUES(gast),
                gastname = VALUES(gastname),
                gastkuerzel = VALUES(gastkuerzel),
                td_heim = VALUES(td_heim),
                td_gast = VALUES(td_gast),
                q1_heim = VALUES(q1_heim),
                q1_gast = VALUES(q1_gast),
                q2_heim = VALUES(q2_heim),
                q2_gast = VALUES(q2_gast),
                q3_heim = VALUES(q3_heim),
                q3_gast = VALUES(q3_gast),
                q4_heim = VALUES(q4_heim),
                q4_gast = VALUES(q4_gast),
                stadion = VALUES(stadion),
                kommentar = VALUES(kommentar),
                imported_at = VALUES(imported_at)",
            $data['game_id'],
            $data['liga_code'],
            $data['bezeichnung'],
            $data['gruppe'],
            $data['datum1'],
            $data['datum2'],
            $data['kickoff'],
            $data['heim'],
            $data['heimname'],
            $data['heimkuerzel'],
            $data['gast'],
            $data['gastname'],
            $data['gastkuerzel'],
            $data['td_heim'],
            $data['td_gast'],
            $data['q1_heim'],
            $data['q1_gast'],
            $data['q2_heim'],
            $data['q2_gast'],
            $data['q3_heim'],
            $data['q3_gast'],
            $data['q4_heim'],
            $data['q4_gast'],
            $data['stadion'],
            $data['kommentar'],
            $data['imported_at']
        ));
    }

    public static function cleanup_stale($table, $liga_code, $import_started_at) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $wpdb->query($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "DELETE FROM {$table} WHERE liga_code = %s AND imported_at < %s",
            $liga_code,
            $import_started_at
        ));
    }

    public static function get_standings($liga_code, $gruppe = null) {
        global $wpdb;
        $table = self::standings_table();

        if ($gruppe) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
            return $wpdb->get_results($wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                "SELECT * FROM {$table} WHERE liga_code = %s AND gruppe = %s ORDER BY platz ASC",
                $liga_code,
                $gruppe
            ), ARRAY_A);
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_results($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "SELECT * FROM {$table} WHERE liga_code = %s ORDER BY gruppe ASC, platz ASC",
            $liga_code
        ), ARRAY_A);
    }

    public static function get_schedule($liga_code, $args = []) {
        global $wpdb;
        $table = self::schedule_table();

        $where = ['liga_code = %s'];
        $params = [$liga_code];

        if (!empty($args['gruppe'])) {
            $where[] = 'gruppe = %s';
            $params[] = $args['gruppe'];
        }

        if (!empty($args['team_name'])) {
            $like = '%' . $wpdb->esc_like($args['team_name']) . '%';
            if (!empty($args['home_only'])) {
                $where[] = 'heim LIKE %s';
                $params[] = $like;
            } else {
                $where[] = '(heim LIKE %s OR gast LIKE %s)';
                $params[] = $like;
                $params[] = $like;
            }
        }

        if (!empty($args['show'])) {
            $today = current_time('Y-m-d');
            if ($args['show'] === 'upcoming') {
                $where[] = 'datum1 >= %s';
                $params[] = $today;
            } elseif ($args['show'] === 'past') {
                $where[] = 'datum1 < %s';
                $params[] = $today;
            }
        }

        $where_sql = implode(' AND ', $where);
        $order = 'ORDER BY datum1 ASC';
        $limit = '';

        if (!empty($args['limit'])) {
            $limit = $wpdb->prepare('LIMIT %d', $args['limit']);
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $sql = "SELECT * FROM {$table} WHERE {$where_sql} {$order} {$limit}";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results($wpdb->prepare($sql, ...$params), ARRAY_A);
    }

    public static function get_counts($liga_code) {
        global $wpdb;
        $standings_table = self::standings_table();
        $schedule_table  = self::schedule_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $standings_count = (int) $wpdb->get_var($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "SELECT COUNT(*) FROM {$standings_table} WHERE liga_code = %s",
            $liga_code
        ));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $schedule_count = (int) $wpdb->get_var($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "SELECT COUNT(*) FROM {$schedule_table} WHERE liga_code = %s",
            $liga_code
        ));

        return [
            'standings' => $standings_count,
            'schedule'  => $schedule_count,
        ];
    }

    public static function get_standing_groups($liga_code) {
        global $wpdb;
        $table = self::standings_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_col($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "SELECT DISTINCT gruppe FROM {$table} WHERE liga_code = %s AND gruppe != '' ORDER BY gruppe ASC",
            $liga_code
        ));
    }

    public static function get_schedule_groups($liga_code) {
        global $wpdb;
        $table = self::schedule_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_col($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "SELECT DISTINCT gruppe FROM {$table} WHERE liga_code = %s AND gruppe != '' ORDER BY gruppe ASC",
            $liga_code
        ));
    }

    public static function get_league_name($liga_code) {
        global $wpdb;
        $table = self::standings_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $name = $wpdb->get_var($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "SELECT bezeichnung FROM {$table} WHERE liga_code = %s LIMIT 1",
            $liga_code
        ));

        if (!$name) {
            $table = self::schedule_table();
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $name = $wpdb->get_var($wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                "SELECT bezeichnung FROM {$table} WHERE liga_code = %s LIMIT 1",
                $liga_code
            ));
        }

        return $name ?: $liga_code;
    }

    /**
     * Drop all plugin tables (current + legacy). Used by uninstall.php.
     */
    public static function uninstall() {
        global $wpdb;
        $tables = [
            self::standings_table(),
            self::schedule_table(),
        ];
        foreach (self::legacy_table_pairs() as $legacy_tables) {
            foreach ($legacy_tables as $t) {
                $tables[] = $t;
            }
        }
        foreach (array_unique($tables) as $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}
