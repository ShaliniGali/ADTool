<?php

#[AllowDynamicProperties]
class SOCOM_Dynamic_Year_model extends CI_Model
{
    public function saveNewPom(int $year, string $position) {
        if (!in_array($position, Dynamic_Year::POSITIONS, true)) {
            log_message('error',
                sprintf(
                    'Position %s, is not a possible position %s',
                    $position,
                    Dynamic_Year::POSITIONS
                )
            );
            return false;
        }

        $checkPom = $this->getPomByYear($year);

        $this->DBs->SOCOM_UI->trans_start();
        
        /// run update
        if (!empty($checkPom)) {
            // save history
            $pomToHistory = $this->saveCurrentPomToHistory();
            $setInactive = $this->setActivePomInactive();

            $this->DBs->SOCOM_UI
                ->set('IS_ACTIVE', 1)
                ->set('LATEST_POSITION', $position)
                ->where('POM_YEAR', $year)
                ->update('USR_LOOKUP_POM_POSITION');

            $log = vsprintf(
                'Failed to save current pom to history',
                [get_called_class(), __METHOD__, ($pomToHistory ? ' true '  : ' false ')]
            );
            log_message('error', $log);
        } else {
            $setActiveToInactive = $this->setActivePomInactive();

            $this->DBs->SOCOM_UI
                ->set('POM_YEAR', $year)
                ->set('IS_ACTIVE', 1)
                ->set('LATEST_POSITION', $position)
                ->insert('USR_LOOKUP_POM_POSITION');
        }

        $result = $this->DBs->SOCOM_UI->trans_complete();

        $log = vsprintf(
            '%s %s USR_LOOKUP_POM_POSITION change to Active POM result transaction was %s',
            [get_called_class(), __METHOD__, ($result ? ' true '  : ' false ')]
        );
        log_message('error', $log);
        $log = vsprintf(
            'All positions set inactive',
            [get_called_class(), __METHOD__, ($setActiveToInactive   ? ' true '  : ' false ')]
        );
        log_message('error', $log);

        return $result;
    }

    public function setActivePomInactive() {
        return $this->DBs->SOCOM_UI
            ->set('IS_ACTIVE', 0)
            ->where('IS_ACTIVE', 1)
            ->update('USR_LOOKUP_POM_POSITION');
    }

    public function saveCurrentPomToHistory() {
        $pom = $this->getCurrentPomFull();
        
        $this->DBs->SOCOM_UI
            ->set('POM_ID', $pom['ID'])
            ->set('POM_YEAR', $pom['POM_YEAR'])
            ->set('LATEST_POSITION', $pom['LATEST_POSITION'])
            ->set('IS_ACTIVE', $pom['IS_ACTIVE'])
            ->set('CREATED_DATETIME', $pom['CREATED_DATETIME'])
            ->set('USER_ID', $pom['USER_ID'])
            ->insert('USR_LOOKUP_POM_POSITION_HISTORY');

    }

    public function getAllPomYears() {
        $pom_years = array_column($this->DBs->SOCOM_UI
            ->select('POM_YEAR')
            ->from('USR_LOOKUP_POM_POSITION')
            ->order_by('POM_YEAR ASC')
            ->get()->result_array(), 'POM_YEAR');

        $start_time = strtotime('+2 years');
        $new_pom_year = (int)date('Y', $start_time);
        $month = (int)date('n', $start_time);
        if ($month >= 10 && $month <= 12) {
            $new_pom_year += 1;
        }

        $new_pom_year = (string)$new_pom_year;

        $last_pom_year = current($pom_years);

        while ($new_pom_year > $last_pom_year) {
            $new_pom_years = (string)++$last_pom_year;

            array_unshift($pom_years, $new_pom_years);
        }
        unset($new_pom_year, $last_pom_year, $month, $start_time);

        return $pom_years;
    }

    public function getLatestPomYear() {
        return $this->DBs->SOCOM_UI
            ->select('MAX(POM_YEAR) as POM_YEAR', false)
            ->from('USR_LOOKUP_POM_POSITION')
            ->get()->row_array()['POM_YEAR'] ?? date('Y');
    }

    public function getPomByYear(int $year) {
        return $this->DBs->SOCOM_UI
            ->select('POM_YEAR')
            ->select('LATEST_POSITION')
            ->select('IS_ACTIVE')
            ->from('USR_LOOKUP_POM_POSITION')
            ->where('POM_YEAR', $year)
            ->get()->row_array();
    }

    public function getCurrentPomFull() {
        return $this->DBs->SOCOM_UI
            ->select('ID')
            ->select('POM_YEAR')
            ->select('LATEST_POSITION')
            ->select('IS_ACTIVE')
            ->select('USER_ID')
            ->select('CREATED_DATETIME')
            ->from('USR_LOOKUP_POM_POSITION')
            ->where('IS_ACTIVE', 1)
            ->get()->row_array();
    }

    public function getCurrentPom() {
        static $result = null;

        if ($result === null) {
            $result = $this->DBs->SOCOM_UI
                ->select('POM_YEAR')
                ->select('LATEST_POSITION')
                ->from('USR_LOOKUP_POM_POSITION')
                ->where('IS_ACTIVE', 1)
                ->get()->row_array();
        }

        return $result ?? false;
    }

    public function getCurrentDecr() {
        return $this->DBs->SOCOM_UI
            ->select('POSITION')
            ->select('SUBAPP')
            ->select('EXT_DECR')
            ->select('ZBT_DECR')
            ->select('ISS_DECR')
            ->select('POM_DECR')
            ->from('LOOKUP_POM_POSITION_DECREMENT')
            ->get()->result_array();
    }

    public function getCurrentDecrByPosition(int $year, string $position) {
        return $this->DBs->SOCOM_UI
            ->select(sprintf('(%s - A.EXT_DECR) as ZBT_SUMMARY_YEAR', (int)$year))
            ->select(sprintf('(%s - A.ZBT_DECR) AS ISS_SUMMARY_YEAR', (int)$year))
            ->select(sprintf('(%s - A.ISS_DECR) AS RESOURCE_CONSTRAINED_COA_YEAR', (int)$year))
            ->from('LOOKUP_POM_POSITION_DECREMENT as A')
            ->where('A.POSITION', $position)
            ->get()->row_array();
    }

    public function setCycleToActivePom(int $cycle_id) {
        $pom = $this->getCurrentPomFull();
        $user_id = (int)$this->session->userdata("logged_in")["id"];

        return $this->DBs->SOCOM_UI
            ->set('POM_ID', $pom['ID'])
            ->set('CYCLE_ID', $cycle_id)
            ->set('USER_ID', $user_id)
            ->insert('USR_LOOKUP_POM_POSITION_CYCLE');
    }

    public function check_table_exists($table) {
        return $this->DBs->SOCOM_UI->table_exists($table);
    }
    
    public function getPositionDecr() {
        $when = '';
        foreach(Dynamic_Year::POSITIONS as $i => $pos) {
            $when .=<<<EOT
WHEN '{$pos}' THEN {$i}

EOT;
        }

        $order =<<<EOT
CASE POSITION
{$when}
END CASE;
EOT;
        return $this->DBs->SOCOM_UI
            ->select('POSITION')
            ->select('SUBAPP')
            ->select('EXT_DECR')
            ->select('ZBT_DECR')
            ->select('ISS_DECR')
            ->select('POM_DECR')
            ->from('LOOKUP_POM_POSITION_DECREMENT')
            ->order_by($order)
            ->get()->result_array();
        }

}