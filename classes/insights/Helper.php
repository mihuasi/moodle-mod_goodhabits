<?php
namespace mod_goodhabits\insights;

use mod_goodhabits\calendar\FlexiCalendar;

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
            $date = date('d-m-y', $record->endofperiod_timestamp);
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

    public static function add_missing_dates(FlexiCalendar $calendar, $start, $end)
    {
        $period = $calendar->get_period_duration();
        $graph_dates = &static::$graph_dates;

        // Convert timestamps to DateTime objects
        $start_date = (new \DateTime())->setTimestamp($start)->setTime(0, 0);
        $end_date = (new \DateTime())->setTimestamp($end)->setTime(23, 59, 59);
        $year = $start_date->format('Y');

        // Parse and validate existing dates
        $existing_dates = [];
        foreach ($graph_dates as $date_str) {
            $date = \DateTime::createFromFormat('d-m-Y', "$date_str-$year");
            if ($date && $date >= $start_date && $date <= $end_date) {
                $existing_dates[] = $date;
            }
        }

        // Calculate base date for sequence generation
        if (!empty($existing_dates) AND $period > 1) {
            $earliest_existing = min($existing_dates);
            $diff = $earliest_existing->diff($start_date);
            $diff_days = (int) $diff->format('%a');
            $k = floor($diff_days / $period);

            $base_date = clone $earliest_existing;
            $base_date->modify("-" . ($k * $period) . " days");

            // Ensure base_date >= start_date
            while ($base_date < $start_date) {
                $base_date->modify("+$period days");
            }
        } else {
            $base_date = clone $start_date;
        }

        // Generate full date sequence
        $sequence = [];
        $current = clone $base_date;

        // Forward generation
        while ($current <= $end_date) {
            $sequence[] = $current->format('d-m-y');
            $current->modify("+$period days");
        }

        // Backward generation (only if needed)
        if (!empty($existing_dates)) {
            $current = clone $base_date;
            $current->modify("- $period days");
            while ($current >= $start_date) {
                array_unshift($sequence, $current->format('d-m-y'));
                $current->modify("-$period days");
            }
        }

        // Merge and deduplicate
        $merged = array_unique(array_merge($graph_dates, $sequence));

        // Sort chronologically
        usort($merged, function($a, $b) use ($year) {
            return \DateTime::createFromFormat('d-m-Y', "$a-$year") <=>
                \DateTime::createFromFormat('d-m-Y', "$b-$year");
        });

        static::$graph_dates = $merged;
    }


    public static function populate_effort_outcome_series($entries_data, $metric, $chart_type = '')
    {
        $series_arr = [];
        $dates = static::$graph_dates;
        if (!$chart_type AND $metric == 'y') {
            $chart_type = \core\chart_series::TYPE_LINE;
        }

        foreach ($entries_data as $name => $habit_entries) {
                $series_data = [];

                foreach ($dates as $date) {
                    if (isset($habit_entries[$date])) {
                        $series_data[] = static::get_metric_value($habit_entries, $date, $metric);
                    } else {
                        $series_data[] = null;
                    }
                }

                $string_id = $metric . 'label';
                $metric_name = \mod_goodhabits\Helper::get_string($string_id);
                $series = new \core\chart_series($name . ': ' . $metric_name, $series_data);
                if ($chart_type == \core\chart_series::TYPE_LINE) {
                    $series->set_type(\core\chart_series::TYPE_LINE);
                }
                $series_arr[] = $series;
        }

        return $series_arr;
    }

    public static function map_metric_term($form_term)
    {
        switch ($form_term) {
            case 'effort':
                return 'x';
            case 'outcome':
                return 'y';
            case 'difference':
                return 'diff';
        }
    }

    private static function get_metric_value($habit_entries, $date, $metric)
    {
        if ($metric == 'diff') {
            return $habit_entries[$date]['y'] - $habit_entries[$date]['x'];
        }
        return $habit_entries[$date][$metric];
    }

    /**
     * @return array
     */
    public static function get_graph_dates(): array
    {
        return static::$graph_dates;
    }
}