<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Computes every indicator on BNS Form No. 1C (Barangay Nutrition Profile)
 * for one barangay, straight from household_members/households (the
 * anthropometric/enrollment fields added in 007_nutrition_profile.sql) and
 * the handful of facts that live on address_barangay because they aren't
 * derived from any resident/household record (puroks, school counts).
 */
class Nutrition_profile_model extends CI_Model
{
    /** The 9 rows under "Number and percent of preschool children according to Nutritional Status". */
    const PRESCHOOL_STATUS_ROWS = [
        ['label' => 'Severely underweight', 'field' => 'nutritional_status_weight_age', 'value' => 'Severely Underweight'],
        ['label' => 'Underweight', 'field' => 'nutritional_status_weight_age', 'value' => 'Underweight'],
        ['label' => 'Normal weight', 'field' => 'nutritional_status_weight_age', 'value' => 'Normal'],
        ['label' => 'Severely Wasted', 'field' => 'nutritional_status_weight_height', 'value' => 'Severely Wasted'],
        ['label' => 'Wasted', 'field' => 'nutritional_status_weight_height', 'value' => 'Wasted'],
        ['label' => 'Overweight', 'field' => 'nutritional_status_weight_height', 'value' => 'Overweight'],
        ['label' => 'Obese', 'field' => 'nutritional_status_weight_height', 'value' => 'Obese'],
        ['label' => 'Severely Stunted', 'field' => 'nutritional_status_height_age', 'value' => 'Severely Stunted'],
        ['label' => 'Stunted', 'field' => 'nutritional_status_height_age', 'value' => 'Stunted'],
    ];

    /** The 5 rows under "Number and percent of school children according to Nutritional Status". */
    const SCHOOL_STATUS_ROWS = ['Severely wasted', 'Wasted', 'Normal', 'Overweight', 'Obese'];

    public function get_summary($barangay_id)
    {
        $population = $this->count_members($barangay_id);
        $household_count = $this->db->where('barangay_id', $barangay_id)->where('archive', 0)->count_all_results('households');
        $households_surveyed = $this->count_households($barangay_id, function ($db) {
            $db->where('households.is_surveyed', 1);
        });

        $pregnant = $this->count_members($barangay_id, function ($db) {
            $db->where('household_members.sex', 'Female')->where('household_members.is_pregnant', 1);
        });
        $lactating = $this->count_members($barangay_id, function ($db) {
            $db->where('household_members.sex', 'Female')->where('household_members.is_lactating', 1);
        });

        $households_with_preschoolers = $this->count_distinct_households($barangay_id, function ($db) {
            $this->where_age_months($db, 0, 71);
        });

        $preschool_population = $this->count_members($barangay_id, function ($db) {
            $this->where_age_months($db, 0, 59);
        });

        $preschool_measured = $this->count_members($barangay_id, function ($db) {
            $this->where_age_months($db, 0, 59);
            $db->where('household_members.opt_plus_measured', 1);
        });
        $measured_coverage_pct = $this->percent($preschool_measured, $preschool_population);

        $preschool_status = [];
        foreach (self::PRESCHOOL_STATUS_ROWS as $row) {
            $count = $this->count_members($barangay_id, function ($db) use ($row) {
                $this->where_age_months($db, 0, 59);
                $db->where('household_members.opt_plus_measured', 1)
                    ->where('household_members.' . $row['field'], $row['value']);
            });
            $preschool_status[] = [
                'label' => $row['label'],
                'count' => $count,
                'percent' => $this->percent($count, $preschool_measured),
            ];
        }

        $age_brackets_months = [
            'infants_0_5' => [0, 5],
            'infants_6_11' => [6, 11],
            'preschoolers_0_23' => [0, 23],
            'preschoolers_12_59' => [12, 59],
            'preschoolers_24_59' => [24, 59],
        ];
        $age_bracket_counts = [];
        foreach ($age_brackets_months as $key => [$min, $max]) {
            $age_bracket_counts[$key] = $this->count_members($barangay_id, function ($db) use ($min, $max) {
                $this->where_age_months($db, $min, $max);
            });
        }

        $families_with_wasted = $this->count_distinct_households($barangay_id, function ($db) {
            $this->where_age_months($db, 0, 59);
            $db->where_in('household_members.nutritional_status_weight_height', ['Severely Wasted', 'Wasted']);
        });
        $families_with_stunted = $this->count_distinct_households($barangay_id, function ($db) {
            $this->where_age_months($db, 0, 59);
            $db->where_in('household_members.nutritional_status_height_age', ['Severely Stunted', 'Stunted']);
        });

        $barangay = $this->db->where('id', $barangay_id)->get('address_barangay')->row();

        $kindergarten_enrolled = $this->count_members($barangay_id, function ($db) {
            $db->where('household_members.school_level', 'Kindergarten');
        });
        $school_children = $this->count_members($barangay_id, function ($db) {
            $db->where('household_members.school_level', 'Elementary');
        });
        $school_children_weighed = $this->count_members($barangay_id, function ($db) {
            $db->where_in('household_members.school_level', ['Kindergarten', 'Elementary'])
                ->where('household_members.school_weighed', 1);
        });
        $school_coverage_pct = $this->percent($school_children_weighed, $kindergarten_enrolled + $school_children);

        $school_status = [];
        foreach (self::SCHOOL_STATUS_ROWS as $label) {
            $value = $label === 'Severely wasted' ? 'Severely Wasted' : $label;
            $count = $this->count_members($barangay_id, function ($db) use ($value) {
                $db->where_in('household_members.school_level', ['Kindergarten', 'Elementary'])
                    ->where('household_members.school_weighed', 1)
                    ->where('household_members.school_nutritional_status', $value);
            });
            $school_status[] = [
                'label' => $label,
                'count' => $count,
                'percent' => $this->percent($count, $school_children_weighed),
            ];
        }

        return (object) [
            'total_puroks' => (int) ($barangay->total_puroks ?? 0),
            'population' => $population,
            'household_count' => (int) $household_count,
            'households_surveyed' => $households_surveyed,
            'pregnant' => $pregnant,
            'lactating' => $lactating,
            'households_with_preschoolers' => $households_with_preschoolers,
            'preschool_population' => $preschool_population,
            'preschool_measured' => $preschool_measured,
            'measured_coverage_pct' => $measured_coverage_pct,
            'preschool_status' => $preschool_status,
            'age_brackets' => $age_bracket_counts,
            'families_with_wasted' => $families_with_wasted,
            'families_with_stunted' => $families_with_stunted,
            'day_care_centers_public' => (int) ($barangay->day_care_centers_public ?? 0),
            'day_care_centers_private' => (int) ($barangay->day_care_centers_private ?? 0),
            'elementary_schools_public' => (int) ($barangay->elementary_schools_public ?? 0),
            'elementary_schools_private' => (int) ($barangay->elementary_schools_private ?? 0),
            'kindergarten_enrolled' => $kindergarten_enrolled,
            'school_children' => $school_children,
            'school_children_weighed' => $school_children_weighed,
            'school_coverage_pct' => $school_coverage_pct,
            'school_status' => $school_status,
        ];
    }

    /** @param callable|null $extra receives the CI query builder to add extra where clauses */
    private function count_members($barangay_id, callable $extra = null)
    {
        $this->db->from('household_members')
            ->join('households', 'households.id = household_members.household_id')
            ->where('households.archive', 0)
            ->where('households.barangay_id', $barangay_id);
        if ($extra) {
            $extra($this->db);
        }
        return (int) $this->db->count_all_results();
    }

    private function count_distinct_households($barangay_id, callable $extra = null)
    {
        $this->db->select('COUNT(DISTINCT household_members.household_id) AS cnt')
            ->from('household_members')
            ->join('households', 'households.id = household_members.household_id')
            ->where('households.archive', 0)
            ->where('households.barangay_id', $barangay_id);
        if ($extra) {
            $extra($this->db);
        }
        return (int) $this->db->get()->row()->cnt;
    }

    private function count_households($barangay_id, callable $extra = null)
    {
        $this->db->from('households')
            ->where('households.archive', 0)
            ->where('households.barangay_id', $barangay_id);
        if ($extra) {
            $extra($this->db);
        }
        return (int) $this->db->count_all_results();
    }

    private function where_age_months($db, $min_months, $max_months)
    {
        $db->where('TIMESTAMPDIFF(MONTH, household_members.birthdate, CURDATE()) >=', $min_months)
            ->where('TIMESTAMPDIFF(MONTH, household_members.birthdate, CURDATE()) <=', $max_months);
    }

    private function percent($count, $total)
    {
        return $total > 0 ? round($count / $total * 100, 1) : 0.0;
    }
}
