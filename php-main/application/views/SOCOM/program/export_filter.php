<?php
        $ass_area = $this->DBs->SOCOM_model->get_assessment_area_code();
        $capability_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'CAPABILITY');
        $pom_sponsor = $this->DBs->SOCOM_model->get_sponsor('LOOKUP_SPONSOR', 'POM');


        $this->load->view('templates/carbon/carbon_modal', [
            'modal_id' => 'exporter_modal',
            'role' => 'program-alignment-export',
            'title' => 'Program Alignment Export',
            'title_heading' => 'Socom Export',
            'html_content' => $this->load->view('option/exporter_modal', 
            [
                'ass_area' => $ass_area, 
                'id' => 1,
                'capability_sponsor' => $capability_sponsor,
                'pom_sponsor' => $pom_sponsor
            ], true)
        ]);

        $this->load->view('templates/carbon/carbon_modal', [
            'modal_id' => 'filter_modal',
            'role' => 'optimizer-program-filter',
            'title' => 'Optimizer Program Filter',
            'title_heading' => 'Socom Filter',
            'html_content' => $this->load->view('option/filter_modal', 
            [
                'ass_area' => $ass_area, 
                'id' => 2,
                'capability_sponsor' => $capability_sponsor,
                'pom_sponsor' => $pom_sponsor,
                'page' => $page
            ], true)
        ]);
    ?>
