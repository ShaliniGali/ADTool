<?php
/**
 * General Helper class
 *
 *
 */
defined('BASEPATH') || exit('No direct script access allowed');

if ( ! function_exists('covertToProgramId'))
{
 // --------------------------------------------------------------------
    /**
     * Tenary op
     *
     * @param  bool  $condition
     * @param  array  $true_expression_obj
     * @param  string  $true_expression_key
     * @param  mixed     $false_expression
     * @return  mixed
     */
    function covertToProgramId($type_of_coa, $params)
    {
        $program_id = '';
        switch ($type_of_coa) {
            case 'ISS':
            case 'RC_T':
                $program_code = $params['program_code'] ?? '';
                $pom_sponsor = $params['pom_sponsor'] ?? '';
                $cap_sponsor = $params['cap_sponsor'] ?? '';
                $ass_area_code = $params['ass_area_code'] ?? '';
                $execution_manager = $params['execution_manager'] ?? '';
                $resource_category = $params['resource_category'] ?? '';
                $eoc_code = $params['eoc_code'] ?? '';
                $osd_pe_code = $params['osd_pe_code'] ?? '';
  
                $program_id = implode('_', [
                    $program_code, $pom_sponsor, $cap_sponsor, $ass_area_code, $execution_manager, $resource_category, 
                    $eoc_code, $osd_pe_code
                ]);
                break;
            case 'ISS_EXTRACT':
                $program_code = $params['program_code'] ?? '';
                $pom_sponsor = $params['pom_sponsor'] ?? '';
                $cap_sponsor = $params['cap_sponsor'] ?? '';
                $ass_area_code = $params['ass_area_code'] ?? '';
                $execution_manager = $params['execution_manager'] ?? '';
                $resource_category = $params['resource_category'] ?? '';
                $eoc_code = $params['eoc_code'] ?? '';
                $osd_pe_code = $params['osd_pe_code'] ?? '';
                $event_name = $params['event_name'] ?? '';

                $program_id = implode('_', [
                    $program_code, $pom_sponsor, $cap_sponsor, $ass_area_code, $execution_manager, $resource_category, 
                    $eoc_code, $osd_pe_code, $event_name
                ]);
                break;
              
        }
        return hash('sha512', $program_id);
    }
}


if (!function_exists('get_years_coa')) {
    function get_years_coa($use_iss_extract) {
        $CI = get_instance();

        if ($use_iss_extract === true) {
            $year = $CI->dynamic_year->getPomYearForSubapp('ISS_SUMMARY_YEAR');
            $year_list = $CI->dynamic_year->getYearList($year);
        } else {
            $year = $CI->dynamic_year->getPomYearForSubapp('RESOURCE_CONSTRAINED_COA_YEAR');
            $year_list = $CI->dynamic_year->getYearList($year);
        }
        
        return [$year, $year_list];
    }

    function get_years_issue_summary() {
        $CI = get_instance();

        $year = $CI->dynamic_year->getPomYearForSubapp('ISS_SUMMARY_YEAR');
        $year_list = $CI->dynamic_year->getYearList($year);
        
        return [$year, $year_list];
    }

    function get_years_zbt_summary() {
        $CI = get_instance();

        $year = $CI->dynamic_year->getPomYearForSubapp('ZBT_SUMMARY_YEAR');
        $year_list = $CI->dynamic_year->getYearList($year);
        
        return [$year, $year_list];
    }
}