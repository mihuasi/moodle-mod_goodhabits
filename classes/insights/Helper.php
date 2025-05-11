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

//            if (!isset($data[$habit_id])) {
//                $data[$habit_id] = [
//                    'effort' => [],
//                    'outcome' => [],
//                    'dates' => []
//                ];
//            }
//
//            $data[$habit_id][$record->id][$date] = $record->y_axis_val;
//            $data[$habit_id]['dates'][$date] = $date;
        }
//        print_object($data);

        return $data;
    }

    /**
     * @return array
     */
    public static function get_graph_dates(): array
    {
        return static::$graph_dates;
    }
}