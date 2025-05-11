<?php
namespace mod_goodhabits\insights;

defined('MOODLE_INTERNAL') || die();

class Helper {

    private static $graph_dates = [];

    public static function get_habit_entries($instanceid, $userid, $limits, $habit_ids) {
        global $DB;

        list($insql, $params) = $DB->get_in_or_equal($habit_ids, SQL_PARAMS_NAMED);

        $sql = "SELECT e.*, i.name
            FROM {mod_goodhabits_item} i
            INNER JOIN {mod_goodhabits_entry} e ON e.habit_id = i.id 
                AND e.habit_id $insql
                AND e.userid = :userid 
                AND e.endofperiod_timestamp >= :lower AND e.endofperiod_timestamp <= :upper
            WHERE i.instanceid = :instanceid
            ORDER BY e.endofperiod_timestamp ASC";

        $lower = $limits['lower'];
        $upper = $limits['upper'];

        $params += [
            'instanceid' => $instanceid,
            'userid' => $userid,
            'lower' => $lower,
            'upper' => $upper,
        ];

        $recs = $DB->get_records_sql($sql, $params);

        return $recs;
    }


    /**
     * Structure data for charting and analysis
     */
    public static function structure_data(array $records) {
        $data = [];
        foreach ($records as $record) {
            $date = date('d-m', $record->endofperiod_timestamp);
            if (!in_array($date, static::$graph_dates)) {
                static::$graph_dates[] = $date;
            }

            $habit_id = $record->habit_id;
            $habit_name = $record->name;

            if (!isset($data[$habit_name])) {
                $data[$habit_name] = [];
            }
            $data[$habit_name][$date] = [
                'x' => $record->x_axis_val,
                'y' => $record->y_axis_val,
            ];
        }

        return $data;
    }

    public static function populate_effort_outcome_series($entries_data, $measure)
    {
        $series_arr = [];
        $dates = static::$graph_dates;

        foreach ($entries_data as $name => $habit_entries) {
                $series_data = [];
                foreach ($dates as $date) {
                    if (isset($habit_entries[$date])) {
                        $series_data[] = $habit_entries[$date][$measure];
                    } else {
                        $series_data[] = null;
                    }
                }
                $string_id = $measure . 'label';
                $measure_name = \mod_goodhabits\Helper::get_string($string_id);
                $series = new \core\chart_series($name . ' - ' . $measure_name, $series_data);
                if ($measure == 'y') {
                    $series->set_type(\core\chart_series::TYPE_LINE);
                }
                $series_arr[] = $series;
        }

        return $series_arr;
    }

    /**
     * @return array
     */
    public static function get_graph_dates(): array
    {
        return static::$graph_dates;
    }
}