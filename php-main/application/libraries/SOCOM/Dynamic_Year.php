<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class Dynamic_Year {
    const POSITIONS = [
        'EXT',
        'ZBT',
        'ISS',
        'POM'
    ];

    const SUBAPPS = [
        'ZBT_SUMMARY',
        'ISS_SUMMARY',
        'RESOURCE_CONSTRAINED_COA'
    ];

    protected $data_tables_distinct = [];
    protected $data_tables_availability = [];

    protected $missing = false;

    public function __construct() {
        // Get the CodeIgniter instance
        $this->CI = &get_instance();
        $this->CI->load->model('SOCOM_Dynamic_Year_model');
    }

    public function setActive() {
        $res = php_api_call(
            'GET',
            'Content-Type: ' . APPLICATION_JSON,
            null,
            RHOMBUS_PYTHON_URL."/socom/metadata/pom-position/active"
        );
    }

    public function setFromCurrentYear() {
        $pom = $this->CI->SOCOM_Dynamic_Year_model->getCurrentPom();

        $api_params = array(
            'year' => (int)$pom['POM_YEAR'],
            'position' => $pom['LATEST_POSITION'],
        );

        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL."/socom/metadata/pom-position/dt-tables",
            $php_api_http_status
        );

        $decoded_res = json_decode($res, true);
        
        // If API call fails, provide fallback data structure
        if ($php_api_http_status !== '200' || !$decoded_res || isset($decoded_res['detail'])) {
            log_message('info', 'Dynamic_Year API call failed, using fallback data structure');
            
            // Create fallback data structure for ZBT and ISS
            $this->CI->SOCOM_SUBAPP_DB = [
                'ZBT_SUMMARY' => [
                    'CURRENT' => [
                        'ZBT_EXTRACT' => [
                            0 => 'ZBT_SUMMARY_2024',
                            1 => 'ZBT_SUMMARY_2025'
                        ],
                        'ZBT' => [
                            0 => 'ZBT_SUMMARY_2024',
                            1 => 'ZBT_SUMMARY_2025'
                        ],
                        'ISS' => [
                            0 => 'ISS_SUMMARY_2024',
                            1 => 'ISS_SUMMARY_2025'
                        ],
                        'POM' => [
                            0 => 'POM_SUMMARY_2024',
                            1 => 'POM_SUMMARY_2025'
                        ]
                    ]
                ],
                'ISS_SUMMARY' => [
                    'CURRENT' => [
                        'ISS_EXTRACT' => [
                            0 => 'ISS_SUMMARY_2024',
                            1 => 'ISS_SUMMARY_2025'
                        ],
                        'ISS' => [
                            0 => 'ISS_SUMMARY_2024',
                            1 => 'ISS_SUMMARY_2025'
                        ]
                    ]
                ],
                'RESOURCE_CONSTRAINED_COA' => [
                    'CURRENT' => [
                        'ISS' => [
                            0 => 'RESOURCE_CONSTRAINED_COA_2024',
                            1 => 'RESOURCE_CONSTRAINED_COA_2025'
                        ]
                    ]
                ]
            ];
        } else {
            $this->CI->SOCOM_SUBAPP_DB = $decoded_res;
        }

        defined('SOCOM_SUBAPP_DB') || define('SOCOM_SUBAPP_DB', $this->CI->SOCOM_SUBAPP_DB);
    }

    public function getByYear($year, $position) {
        $checkPom = $this->CI->SOCOM_Dynamic_Year_model->getPomByYear($year);

        $position = strtoupper(trim($position));

        if (!isset($checkPom['POM_YEAR'], $checkPom['LATEST_POSITION'])) {
            log_message('error', 
                sprintf(
                    'Requested POM_YEAR: %s and POSITION: %s do not exist in %s::%s',
                    $year,
                    $position,
                    __CLASS__,
                    __METHOD__
                )
            );
            return false;
        }
        if (!in_array($position, self::POSITIONS, true)) {
            log_message('error',
                sprintf(
                    'Requested POSITION: %s do not exist in %s::%s',
                    $year,
                    $position,
                    __CLASS__,
                    __METHOD__
                )
            );
            return false;
        }

        $api_params = array(
            'year' => (int)$checkPom['POM_YEAR'],
            'position' => $position,
        );

        $res = php_api_call(
            'POST',
            'Content-Type: ' . APPLICATION_JSON,
            json_encode($api_params),
            RHOMBUS_PYTHON_URL."/socom/metadata/pom-position/dt-tables",
            $php_api_http_status
        );

        $res = json_decode($res, true);

        if ($php_api_http_status !== '200') {
            $detail = explode(': ', $res['detail']);

            $res = [];

            if (preg_match('/^(DT_.*)\,?\s?$/', $detail[1]) === 1) {
                $res['MISSING']['CURRENT']['POM'] = explode(', ', $detail[1]);
            }
            
            $this->missing = true;
        }
        
        return $res;
    }

    public function hasMissing() {
        return $this->missing;
    }

    public function getTableDecr() {
        $tables_decr = $this->CI->SOCOM_Dynamic_Year_model->getCurrentDecr();
        
        // set CONSTANT structure with 0 or 1 from current DECR and current API JSON unique tables
        defined('SOCOM_TABLES_DECR') || define('SOCOM_TABLES_DECR',  $tables_decr);

        return SOCOM_TABLES_DECR;
    }

    public function setTablesExist($year = null, $position = null) {
        $this->data_tables_distinct = [];
        $this->data_tables_availability = [];
        
        if ($year === null) {
            $subapp_db = SOCOM_SUBAPP_DB;
            $currentPom = $this->CI->SOCOM_Dynamic_Year_model->getCurrentPom();
            $currentPos = $currentPom['LATEST_POSITION'];
        } else {
            $latest_pom = $this->CI->SOCOM_Dynamic_Year_model->getLatestPomYear();
            $yearPom = $this->CI->SOCOM_Dynamic_Year_model->getPomByYear($year);
            if (
                $latest_pom === $year && 
                in_array(strtoupper($position), self::POSITIONS, true)
            ) {
                $currentPos = $position;
            } else {
                $currentPos = $yearPom['LATEST_POSITION'];
            }

            $subapp_db = $this->getByYear($year, $position);
        }
        $pom_pos = array_search(strtoupper($currentPos), self::POSITIONS, true);
        
        foreach ($subapp_db as $page => $dataset) {
            if (!is_array($dataset)) {
                continue;
            }

            foreach ($dataset as $set => $position) {
                foreach ($position as $pos_name => $table_names) {
                    foreach ($table_names as $table_name) {
                        $new_pos_name = trim(strtoupper($pos_name));
                        
                        $new_pos = array_search(
                            str_replace('_EXTRACT', '', $new_pos_name), self::POSITIONS, true);
                        
                        $availability = ($this->CI->SOCOM_Dynamic_Year_model->check_table_exists($table_name) === false ? 3 :
                        ($new_pos !== false && $pom_pos >= $new_pos ? 1 : 2));
                        
                        if (!isset($data_tables_distinct[$table_name])) {
                            $this->data_tables_distinct[$table_name] = [
                                'table_name' => $table_name,
                                'availability' => $availability
                            ];
                        }

                        $this->data_tables_availability[$page][] = [
                            'table_name' => $table_name,
                            'page' => $page,
                            'set' => $set,
                            'position' => $new_pos_name,
                            'availability' => $availability
                        ];
                    }
                }
            }
        }

        $this->data_tables_distinct = array_values($this->data_tables_distinct);
    }

    public function getTablesPom() {
        return  $this->data_tables_distinct;
    }

    public function getTablesPagePom() {
        return  $this->data_tables_availability;
    }

    public function getPomYearForSubapp(string $subapp) {
        $pom_year = $this->CI->SOCOM_Dynamic_Year_model->getCurrentPom();
        $year = $pom_year['POM_YEAR'];
        $position = $pom_year['LATEST_POSITION'];
        
        $yearSubappResult =  $this->CI->SOCOM_Dynamic_Year_model->getCurrentDecrByPosition($year, $position);

        if (!isset($yearSubappResult[$subapp])) {
            log_message('error', sprintf('Unable to get subapp POM year for SUBAPP: %s', $subapp));
        }

        return $yearSubappResult[$subapp] ?? '';
    }

    public function getTable(string $subapp, bool $current = true, string $table_type = 'EXT', int $table_loc = 0) {
        if ($current !== true) {
            $set = 'HISTORICAL_POM';
        } else {
            $set = 'CURRENT';
        }

        if ($this->validateDBConstant($subapp, $set, $table_type, $table_loc) === false) {
            show_error('Unable to determine POM Year data set', 500);
        } else {
            log_message('error', 
                sprintf('Utilized %s table from Dynamic_Year::getTable', 
                $this->CI->SOCOM_SUBAPP_DB[$subapp][$set][$table_type][$table_loc])
            );
            log_message('error', 
                sprintf('Table found using Subapp: %s, Set: %s, Table Type: %s, Table Loc: %s', 
                    $subapp, $set, $table_type, $table_loc)
            );
        }

        return $this->CI->SOCOM_SUBAPP_DB[$subapp][$set][$table_type][$table_loc];
    }

    public function validateDBConstant(string $subapp, string $set, string $table_type, int $table_loc) {
        $result = true;

        if (!isset($this->CI->SOCOM_SUBAPP_DB[$subapp])) {
            log_message('error',
                sprintf(
                    "Subapp not found in Dynamic Year JSON from API: %s, JSON: %s",
                $subapp,
                var_export($this->CI->SOCOM_SUBAPP_DB, true)
            ));
            $result = false;
        }

        if (!isset($this->CI->SOCOM_SUBAPP_DB[$subapp][$set])) {
            log_message('error',
                sprintf(
                    "SET not found in Dynamic Year JSON from API: %s::%s, JSON: %s",
                $subapp,
                $set,
                var_export($this->CI->SOCOM_SUBAPP_DB, true)
            ));
            $result = false;
        }

        if (!isset($this->CI->SOCOM_SUBAPP_DB[$subapp][$set][$table_type])) {
            log_message('error',
                sprintf(
                    "TABLE TYPE not found in Dynamic Year JSON from API: %s::%s::%s, JSON: %s",
                $subapp,
                $set,
                $table_type,
                var_export($this->CI->SOCOM_SUBAPP_DB, true)
            ));
            $result = false;
        }

        if (!isset($this->CI->SOCOM_SUBAPP_DB[$subapp][$set][$table_type][$table_loc])) {
            log_message('error',
                sprintf(
                    "TABLE TYPE not found in Dynamic Year JSON from API: %s::%s::%s::%s, JSON: %s",
                $subapp,
                $set,
                $table_type,
                $table_loc,
                var_export($this->CI->SOCOM_SUBAPP_DB, true)
            ));
            $result = false;
        }
        return $result;
    }

    public function getYearList(int $year) {
        static $year_list = [];

        if (!isset($year_list[$year])) {
            $year_list[$year] = range($year, $year + 4);
        }

        return $year_list[$year];
    }
}