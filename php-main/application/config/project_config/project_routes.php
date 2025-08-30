<?php

/**
 * Project Specific Routes
 */

$route['SOCOM'] = LOGIN_ROUTE;
$route['SOCOM/(.+)'] = LOGIN_ROUTE;
$route['socom/index'] = 'SOCOM/SOCOM_HOME/index';
$route['socom/zbt_summary'] = 'SOCOM/SOCOM_HOME/zbt_summary';
$route['socom/issue'] = 'SOCOM/SOCOM_HOME/issue';
$route['socom/pb_comparison'] = 'SOCOM/SOCOM_HOME/pb_comparison';
$route['socom/pb_comparison/graph/update'] = 'SOCOM/SOCOM_HOME/update_pb_comparison_graph';
$route['socom/pb_comparison/filter/program/update'] = 'SOCOM/SOCOM_HOME/update_program_filter';
$route['socom/program_group/filter/update'] = 'SOCOM/SOCOM_HOME/update_program_filter_by_group';
$route['socom/filter/business_rules/update_program_group'] = 'SOCOM/SOCOM_HOME/get_all_program_group';
$route['socom/filter/business_rules/update_resource_category'] = 'SOCOM/SOCOM_HOME/get_resource_category_code';
$route['socom/filter/business_rules/update_capability_sponsor'] = 'SOCOM/SOCOM_HOME/get_capability_sponsor';
$route['socom/pb_comparison/filter/resource_category/update'] = 'SOCOM/SOCOM_HOME/update_resource_category_filter_pb';
$route['socom/pb_comparison/filter/execution_manager/update'] = 'SOCOM/SOCOM_HOME/update_execution_manager_filter_pb';
$route['socom/pb_comparison/filter/program_name/update'] = 'SOCOM/SOCOM_HOME/update_program_name_filter_pb';
$route['socom/pb_comparison/filter/eoc_code/update'] = 'SOCOM/SOCOM_HOME/update_eoc_code_filter_pb';
$route['socom/pb_comparison/filter/osd_pe/update'] = 'SOCOM/SOCOM_HOME/update_osd_pe_filter_pb';
$route['socom/budget_to_execution'] = 'SOCOM/SOCOM_HOME/budget_to_execution';
$route['socom/budget_to_execution/graph/update'] = 'SOCOM/SOCOM_HOME/update_budget_to_execution_graph';
$route['socom/budget_to_execution/filter/program/update'] = 'SOCOM/SOCOM_HOME/update_program_filter';
$route['socom/budget_to_execution/filter/resource_category/update'] = 'SOCOM/SOCOM_HOME/update_resource_category_filter_be';
$route['socom/budget_to_execution/filter/execution_manager/update'] = 'SOCOM/SOCOM_HOME/update_execution_manager_filter_be';
$route['socom/budget_to_execution/filter/program_name/update'] = 'SOCOM/SOCOM_HOME/update_program_name_filter_be';
$route['socom/budget_to_execution/filter/eoc_code/update'] = 'SOCOM/SOCOM_HOME/update_eoc_code_filter_be';
$route['socom/budget_to_execution/filter/osd_pe/update'] = 'SOCOM/SOCOM_HOME/update_osd_pe_filter_be';
$route['socom/get_dollars_moved_resource_category'] = 'SOCOM/SOCOM_HOME/get_dollars_moved_resource_category';

// event summary
$route['socom/(:any)/event_summary'] = 'SOCOM/SOCOM_Event_Summary/event_summary/$1';
$route['socom/(:any)/event_summary/(:any)'] = 'SOCOM/SOCOM_Event_Summary/event_summary/$1/$2';
$route['socom/(:any)/event_summary_overall'] = 'SOCOM/SOCOM_Event_Summary/overall_event_summary/$1';
$route['socom/(:any)/get_event_summary_data/(:any)'] = 'SOCOM/SOCOM_Event_Summary/get_event_summary_data/$1/$2';
$route['socom/(:any)/get_exported_event_summary_data'] = 'SOCOM/SOCOM_Event_Summary/get_exported_event_summary_data/$1';
$route['socom/(:any)/get_overall_event_summary_data'] = 'SOCOM/SOCOM_Event_Summary/get_overall_event_summary_data/$1';
$route['socom/(:any)/get_ao_ad_data/(:any)'] = 'SOCOM/SOCOM_Event_Summary/get_ao_ad_data/$1/$2';

// program breakdown
$route['socom/program_breakdown/filter/update'] = 'SOCOM/SOCOM_Program_Breakdown/update_program_filter';
$route['socom/(:any)/program_breakdown'] = 'SOCOM/SOCOM_Program_Breakdown/index/$1';
$route['socom/(:any)/program_breakdown/graph/update'] = 'SOCOM/SOCOM_Program_Breakdown/update_program_breakdown_graph/$1';
$route['socom/(:any)/program_breakdown/table/update'] = 'SOCOM/SOCOM_Program_Breakdown/update_program_summary_table/$1';
$route['socom/(:any)/program_breakdown/table/save'] = 'SOCOM/SOCOM_Program_Breakdown/save_program_summary_table/$1';
$route['socom/(:any)/program_breakdown/card/update'] = 'SOCOM/SOCOM_Program_Breakdown/update_program_summary_card/$1';
$route['socom/(:any)/historical_pom'] = 'SOCOM/SOCOM_Program_Breakdown/historical_pom/$1';
$route['socom/(:any)/historical_pom/update'] = 'SOCOM/SOCOM_Program_Breakdown/update_historical_pom/$1';
$route['socom/(:any)/eoc_summary'] = 'SOCOM/SOCOM_Program_Breakdown/eoc_summary/$1';
$route['socom/(:any)/eoc_summary/update'] = 'SOCOM/SOCOM_Program_Breakdown/update_eoc_summary/$1';

// optimizer
$route['optimizer/view'] = 'SOCOM/SOCOM_Optimizer/index';
$route['optimizer/(:any)/optimize'] = 'SOCOM/SOCOM_Optimizer/optimize/$1';
$route['optimizer/save_coa'] = 'SOCOM/SOCOM_COA/save_coa';
$route['optimizer/get_coa'] = 'SOCOM/SOCOM_COA/get_coa_user_list';
$route['optimizer/get_coa_data'] = 'SOCOM/SOCOM_COA/get_coa_user_data';
$route['optimizer/scenario/(:num)/simulation/table/insert'] = 'SOCOM/SOCOM_COA/insert_coa_table_row/$1';
$route['optimizer/scenario/(:num)/simulation/table/insert/update'] = 'SOCOM/SOCOM_COA/update_coa_table_insert_dropdown/$1';
$route['optimizer/scenario/(:num)/simulation/table/insert/get'] = 'SOCOM/SOCOM_COA/get_coa_table_row_budget/$1';
$route['optimizer/scenario/(:num)/simulation/table/output'] = 'SOCOM/SOCOM_COA/get_output_table/$1';
$route['optimizer/scenario/(:num)/manual_override_save'] = 'SOCOM/SOCOM_COA/manual_override_save/$1';
$route['optimizer/scenario/(:num)/save_override_form'] = 'SOCOM/SOCOM_COA/save_override_form/$1';
$route['optimizer/get_display_banner'] = 'SOCOM/SOCOM_COA/get_display_banner';
$route['optimizer/scenario/(:num)/change_scenario_status'] = 'SOCOM/SOCOM_COA/change_scenario_status/$1';
$route['optimizer/scenario/(:num)/table/(:num)/get_detailed_summary'] = 'SOCOM/SOCOM_COA/get_detailed_summary/$1/$2';
$route['optimizer/scenario/(:num)/get_detailed_summary_data'] = 'SOCOM/SOCOM_COA/get_detailed_summary_data/$1';
$route['optimizer/get_detailed_summary_data/(:any)/update'] = 'SOCOM/SOCOM_COA/update_detailed_summary_view/$1';
$route['optimizer/get_detailed_comparison'] = 'SOCOM/SOCOM_COA/get_detailed_comparison';
$route['optimizer/get_detailed_comparison_data/(:any)'] = 'SOCOM/SOCOM_COA/get_detailed_comparison_data/$1';
$route['optimizer/get_program_breakdown/(:any)'] = 'SOCOM/SOCOM_COA/get_program_breakdown/$1';

// Resource Constrained Coa
$route['socom/resource_constrained_coa'] = 'SOCOM/SOCOM_HOME/resource_constrained_coa';
$route['socom/resource_constrained_coa/program/list'] = 'SOCOM/SOCOM_Program/index';
$route['socom/resource_constrained_coa/program/list/get'] = 'SOCOM/SOCOM_Program/get_program';
$route['socom/resource_constrained_coa/program/list/get/(:any)'] = 'SOCOM/SOCOM_Program/get_program/$1';
$route['socom/resource_constrained_coa/program/list/get_storm'] = 'SOCOM/SOCOM_Storm/get_storm';
$route['socom/resource_constrained_coa/program/weight_table/get/(:any)'] = 'SOCOM/SOCOM_Program/get_weighted_table/$1';
$route['socom/resource_constrained_coa/weights/create'] = 'SOCOM/SOCOM_Weights_Builder/create_weights';
$route['socom/resource_constrained_coa/weights/save'] = 'SOCOM/SOCOM_Weights_Builder/save_weights';
$route['socom/resource_constrained_coa/weights/list/save'] = 'SOCOM/SOCOM_Weights_List/save_weights';
$route['socom/resource_constrained_coa/criteria/weights/get/(:num)'] = 'SOCOM/SOCOM_Weights_List/get_weight/$1';
$route['socom/resource_constrained_coa/weights/delete/(:num)'] = 'SOCOM/SOCOM_Weights_Builder/delete_weights/$1';
$route['socom/resource_constrained_coa/criteria/weights/list/data'] = 'SOCOM/SOCOM_Weights_List/get_data';
$route['socom/resource_constrained_coa/program/score/data'] = 'SOCOM/SOCOM_Score/index';
$route['socom/resource_constrained_coa/program/score/edit'] = 'SOCOM/SOCOM_Score/edit';
$route['socom/resource_constrained_coa/program/score/create'] = 'SOCOM/SOCOM_Score/create';
$route['socom/resource_constrained_coa/program/score/get'] = 'SOCOM/SOCOM_Score/get';
$route['socom/(:any)/eoc_historical_pom'] = 'SOCOM/SOCOM_HOME/eoc_historical_pom/$1';
$route['socom/resource_constrained_coa/program/export'] = 'SOCOM/SOCOM_Document_Export/export';
$route['socom/resource_constrained_coa/program/list/update'] = 'SOCOM/SOCOM_Program/update_selection';
$route['socom/resource_constrained_coa/fetch/proposed_cuts'] = 'SOCOM/SOCOM_Optimizer/proposed_cuts';

# Dashboard
$route['dashboard'] = 'SOCOM/SOCOM_Dashboard/index';
$route['dashboard/myuser'] = 'SOCOM/SOCOM_Dashboard_Admin_AOAD_Users/index';

$route['dashboard/admin/admin_users/list/get'] = 'SOCOM/SOCOM_Dashboard_Admin_AOAD_Users/get_admin_user_list';
$route['dashboard/admin/ao_ad_users/list/get'] = 'SOCOM/SOCOM_Dashboard_Admin_AOAD_Users/get_ao_ad_user_list';
$route['dashboard/admin/cycle_users/list/get'] = 'SOCOM/SOCOM_Dashboard_Cycle_Users/get_user_list';
$route['dashboard/admin/pom_users/list/get'] = 'SOCOM/SOCOM_Dashboard_Site_Users/get_pom_user_list';
$route['dashboard/admin/cap_users/list/get'] = 'SOCOM/SOCOM_Dashboard_Cap_Users/get_cap_user_list';

$route['dashboard/admin/admin_users/status/save'] = 'SOCOM/SOCOM_Dashboard_Admin_AOAD_Users/save_admin_status';
$route['dashboard/admin/ao_ad_users/status/save'] = 'SOCOM/SOCOM_Dashboard_Admin_AOAD_Users/save_ao_ad_status';
$route['dashboard/admin/cycle_users/status/save'] = 'SOCOM/SOCOM_Dashboard_Cycle_Users/save_status';
$route['dashboard/admin/pom_users/status/save'] = 'SOCOM/SOCOM_Dashboard_Site_Users/save_pom_status';
$route['dashboard/admin/cap_users/status/save'] = 'SOCOM/SOCOM_Dashboard_Cap_Users/save_cap_status';

$route['dashboard/myuser/admin/save'] = 'SOCOM/SOCOM_Dashboard_Admin_AOAD_Users/save_my_user_admin';
$route['dashboard/myuser/ao_ad/save'] = 'SOCOM/SOCOM_Dashboard_Admin_AOAD_Users/save_my_user_ao_ad';
$route['dashboard/myuser/cycle_users/save'] = 'SOCOM/SOCOM_Dashboard_Cycle_Users/save_my_user';
$route['dashboard/myuser/pom/save'] = 'SOCOM/SOCOM_Dashboard_Site_Users/save_my_user_pom';
$route['dashboard/myuser/cap/save'] = 'SOCOM/SOCOM_Dashboard_Cap_Users/save_my_user_cap';

#$route['Database_Upload'] = 'Login/index';
#$route['Database_Upload/(.+)'] = 'Login/index';
$route['dashboard/import_upload'] = 'SOCOM/Database_upload';
$route['dashboard/import_upload/program_alignment/upload_file'] = 'SOCOM/Database_Save_Program_Alignment/save_upload';
$route['dashboard/import_upload/program_alignment/list_view'] = 'SOCOM/Database_Save_Program_Alignment/list_uploads';
$route['dashboard/import_upload/program_alignment/results_list_view'] = 'SOCOM/Database_Save_Program_Alignment/get_processed';
$route['dashboard/import_upload/program_alignment/process_file'] = 'SOCOM/Database_upload/process_file';
$route['dashboard/import_upload/program_alignment/delete_file'] = 'SOCOM/Database_upload/delete_file';
$route['dashboard/import_upload/program_alignment/cancel_file'] = 'SOCOM/Database_upload/cancel_file';

$route['dashboard/import_upload/database_save_in-pom_cycle_data_upload/upload_file'] = 'SOCOM/Database_Save_In_POM_Cycle_Data_Upload/save_upload';
$route['dashboard/import_upload/database_save_in-pom_cycle_data_upload/list_view'] = 'SOCOM/Database_Save_In_POM_Cycle_Data_Upload/list_uploads';
$route['dashboard/import_upload/database_save_in-pom_cycle_data_upload/results_list_view'] = 'SOCOM/Database_Save_In_POM_Cycle_Data_Upload/get_processed';
$route['dashboard/import_upload/database_save_in-pom_cycle_data_upload/process_file'] = 'SOCOM/Database_upload/process_file';
$route['dashboard/import_upload/database_save_in-pom_cycle_data_upload/delete_file'] = 'SOCOM/Database_upload/delete_file';
$route['dashboard/import_upload/database_save_in-pom_cycle_data_upload/cancel_file'] = 'SOCOM/Database_upload/cancel_file';
$route['dashboard/import_upload/upload_file'] = 'SOCOM/Database_upload/activate_file';


$route['dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/upload_file'] = 'SOCOM/Database_Save_Out_of_POM_Cycle_Data_Upload/save_upload';
$route['dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/list_view'] = 'SOCOM/Database_Save_Out_of_POM_Cycle_Data_Upload/list_uploads';
$route['dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/results_list_view'] = 'SOCOM/Database_Save_Out_of_POM_Cycle_Data_Upload/get_processed';
$route['dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/process_file'] = 'SOCOM/Database_upload/process_file';
$route['dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/delete_file'] = 'SOCOM/Database_upload/delete_file';
$route['dashboard/import_upload/database_save_out-of-pom_cycle_data_upload/cancel_file'] = 'SOCOM/Database_upload/cancel_file';

$route['dashboard/import_upload/database_save_zbt_issue_data_upload/parse_file'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/parse_upload';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/upsert_file'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/upsert_upload';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/upload_file'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/save_upload';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/list_view'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/list_uploads';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/results_list_view'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/get_processed';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/results_list_view_admin'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/get_processed_admin';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/process_file'] = 'SOCOM/Database_upload/process_file';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/delete_file'] = 'SOCOM/Database_upload/delete_file';
$route['dashboard/import_upload/database_save_zbt_issue_data_upload/cancel_file'] = 'SOCOM/Database_upload/cancel_file';
$route['dashboard/import_upload/save_submit'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/save_submit';
$route['dashboard/import_upload/save_approve'] = 'SOCOM/Database_Save_ZBT_Issue_Data_Upload/save_approve';

$route['dashboard/import_upload/messages'] = 'SOCOM/SOCOM_API_Upload_Notification/get_messages';
$route['dashboard/import_upload/acknowledge_message'] = 'SOCOM/SOCOM_API_Upload_Notification/acknowledge_message';

$route['dashboard/import_upload/editor_view/(:any)'] = 'SOCOM/SOCOM_DT_Editor/index/$1';
$route['dashboard/import_upload/editor_view/(:any)/1'] = 'SOCOM/SOCOM_DT_Editor/index/$1/1';
$route['dashboard/import_upload/fetch_data_editor'] = 'SOCOM/SOCOM_DT_Editor/fetch_data_editor';
$route['dashboard/import_upload/fetch_data_editor/1'] = 'SOCOM/SOCOM_DT_Editor/fetch_data_editor/1';
$route['dashboard/import_upload/search_data_editor'] = 'SOCOM/SOCOM_DT_Editor/search_data_editor';
$route['dashboard/import_upload/search_data_editor/1'] = 'SOCOM/SOCOM_DT_Editor/search_data_editor/1';
$route['dashboard/import_upload/save_data_edits'] = 'SOCOM/SOCOM_DT_Editor/save_data_edits';

$route['dashboard/cycles'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/index';
$route['dashboard/cycles/create'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/create_cycle';
$route['dashboard/cycles/get_active'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/get_active_cycle';
$route['dashboard/cycles/list/get'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/get_cycles';
$route['dashboard/cycles/list/get_deleted'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/get_deleted_cycles';
$route['dashboard/cycles/update'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/update_cycle';

$route['dashboard/cycles/criteria/create'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/create_criteria';
$route['dashboard/cycles/criteria/terms/get'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/get_criteria_terms';
$route['dashboard/cycles/criteria/terms/update'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/update_criteria_description';
$route['dashboard/cycles/criteria/terms/delete'] = 'SOCOM/SOCOM_Dashboard_Cycle_Management/delete_criteria_description';
$route['dashboard/pom/get_tables_exist'] = 'SOCOM/SOCOM_POM_Controller/get_tables_exist_pom';
$route['dashboard/pom/get_tables_exist/(:any)'] = 'SOCOM/SOCOM_POM_Controller/get_tables_exist/$1';
$route['dashboard/pom/save_new_pom'] = 'SOCOM/SOCOM_POM_Controller/save_new_pom';

$route['dashboard/coa_management'] = 'SOCOM/SOCOM_Dashboard_COA_Management/index';
$route['dashboard/coa_management/get_my_coa'] = 'SOCOM/SOCOM_Dashboard_COA_Management/get_my_coa';
$route['dashboard/coa_management/get_coa_shared_to_me'] = 'SOCOM/SOCOM_Dashboard_COA_Management/get_coa_shared_to_me';
$route['dashboard/coa_management/get_coa_shared_by_me'] = 'SOCOM/SOCOM_Dashboard_COA_Management/get_coa_shared_by_me';
$route['dashboard/coa_management/share_coa'] = 'SOCOM/SOCOM_Dashboard_COA_Management/share_coa';
$route['dashboard/coa_management/revoke_coa'] = 'SOCOM/SOCOM_Dashboard_COA_Management/revoke_coa';
$route['dashboard/coa_management/get_selected_coa'] = 'SOCOM/SOCOM_Dashboard_COA_Management/get_selected_coa';
$route['dashboard/coa_management/merge_coa'] = 'SOCOM/SOCOM_Dashboard_COA_Management/merge_coa';
$route['dashboard/coa_management/get_proposed_budget'] = 'SOCOM/SOCOM_Dashboard_COA_Management/get_proposed_budget';

#AOAD Saves
$route['socom/(:any)/eoc_summary/(:any)/dropdown/save'] = 'SOCOM/SOCOM_AOAD/save_ao_ad_dropdown/$1/$2';
$route['socom/(:any)/eoc_summary/(:any)/comment/save'] = 'SOCOM/SOCOM_AOAD/save_ao_ad_comment/$1/$2';
$route['socom/(:any)/eoc_summary/(:any)/dropdown/delete'] = 'SOCOM/SOCOM_AOAD/delete_item_dropdown/$1/$2';
$route['socom/(:any)/eoc_summary/(:any)/comment/delete'] = 'SOCOM/SOCOM_AOAD/delete_item_comment/$1/$2';

#Final AD
$route['socom/(:any)/final_ad_action/(:any)/get'] = 'SOCOM/SOCOM_AOAD/get_final_ad_table_data/$1/$2';
$route['socom/(:any)/final_ad_action/(:any)/save'] = 'SOCOM/SOCOM_AOAD/save_final_ad_table_data/$1/$2';

#Portfolio Viewer
$route['portfolio/view'] = 'SOCOM/SOCOM_Portfolio_Viewer/index';
$route['portfolio/budget_trend_overview/graph/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_budget_trend_overview_graph';
$route['portfolio/final_enacted_budget/graph/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_final_enacted_budget_graph';
$route['portfolio/execution/graph/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_execution_graph';
$route['portfolio/budget_trend_overview/dropdown/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_budget_trend_overview_dropdown';
$route['portfolio/amount/chart/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_chart_data_amount';
$route['portfolio/top_program/chart/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_data_top_program';
$route['portfolio/selected_program/chart/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_chart_data_selected_program';
$route['portfolio/program_execution_drilldown/graph/funding/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_funding_graph';
$route['portfolio/program_execution_drilldown/dropdown/funding/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_funding_dropdown';
$route['portfolio/program_execution_drilldown/graph/ams/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_ams_graph';
$route['portfolio/program_execution_drilldown/metadata/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_metadata_descriptions';
$route['portfolio/program_group/dropdown/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_program_group_dropdown';
$route['portfolio/fielding_view/table/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_fielding_data';
$route['portfolio/fielding_view/dropdown/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_fielding_dropdown';
$route['portfolio/fielding_view/dropdown/component/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_fielding_component_dropdown';
$route['portfolio/compare_programs/budgets/table/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_ams_budgets_table';
$route['portfolio/program_execution_drilldown/milestone/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_milestone_data';
$route['portfolio/program_execution_drilldown/milestone/requirements/get'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_requirements_data';
$route['portfolio/program_execution_drilldown/milestone/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/update_milestone_data';
$route['portfolio/program_execution_drilldown/graph/fielding/update'] = 'SOCOM/SOCOM_Portfolio_Viewer/get_fielding_data';

# Release notes view
$route['release_notes'] = 'Release_notes_controller/index';
$route['release_notes/get_note'] = 'Release_notes_controller/get_note';
