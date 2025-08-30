<?php

#[AllowDynamicProperties]
class  DBsCore extends CI_Model{
      private CONST TILES_CONTROLLERS = [
        'keycloak_tiles_controller',
        'account_manager_controller',
        'facs_manager_controller',
        'home'
      ];
      private $activeDbs = [];

    public function __construct(){
        //
        // Define all different databases here
        //
        $GUARDIAN_DEV = $this->GUARDIAN_DEV = $this->findDBConnection(getenv(GLOBAL_APP_STRUCTURE.'_guardian_users'));

        if (
          (RHOMBUS_FACS === 'TRUE' || 
          RHOMBUS_SSO_KEYCLOAK === 'TRUE' || 
          RHOMBUS_SSO_PLATFORM_ONE === 'TRUE') &&
          defined('CREDENTIALS_TILE_DB') && 
          $this->load_tiles_dbs()
        ) {
          $KEYCLOAK_TILE = $this->KEYCLOAK_TILE = $this->findDBConnection(CREDENTIALS_TILE_DB);

          $this->load->model('Keycloak_tiles_model');
        }
		
        //
        // All the model has to be loaded here
        //
        $this->load->model('DB_ind_model'); 
        $this->load->model('Generic');

        /**
         * Migrated: Moheb, August 21st, 2020
         */
        $this->load->model('Account_manager_model');
      
        /**
         * Migrated: Moheb, August 21st, 2020
         * 
         * Login and Registration 
         * 
         */
        $this->load->model('login_register/SSO_model');
        $this->load->model('login_register/Google_2FA_model');
        $this->load->model('login_register/Login_model');
        $this->load->model('login_register/Login_private_subnet_model');
        $this->load->model('login_register/Login_token_model');
        $this->load->model('login_register/Register_model');
        $this->load->model('login_register/Users_keys_model');
        $this->load->model('facs_models/Roles_manager_model');
        $this->load->model('facs_models/Subapps_manager_model');
        $this->load->model('facs_models/Features_manager_model');
        $this->load->model('facs_models/Role_mappings_manager_model');
        $this->load->model('facs_models/Roles_manager_model');
        $this->load->model('facs_models/Subapps_manager_model');
        $this->load->model('facs_models/Subapps_alias_manager_model');
        
        $this->load->model('facs_models/Features_manager_model');
        $this->load->model('facs_models/Role_mappings_manager_model');
        $this->load->model('login_register/Keycloak_model');
        $this->load->model('login_register/Platform_One_model');
    }

    public function findDBConnection($dbName) {
      if (strlen(trim($dbName)) == 0) {
        log_message('error', var_export(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS, 2), true));
      }

      log_message('error', sprintf('Attempting to load %s', $dbName));
      
      if (!isset($this->activeDbs[$dbName])) {
        $this->activeDbs[$dbName] = $this->load->database($dbName, TRUE);
      }

      if ($this->activeDbs[$dbName] !== false && $this->activeDbs[$dbName] instanceof CI_DB) {
        log_message('info', sprintf('Database %s loaded', $dbName));
      } else {
        log_message('error', sprintf('Unable to load database %s', $dbName));
      }

      return $this->activeDbs[$dbName];
    }

  public function getDBConnection($tile_name)
  { 
    if ($this->load_tiles_dbs()) {
      switch ($tile_name) {
        case 'ACTF':
          $db = $this->findDBConnection(ACTF_SCHEMA);
          break;
        case 'USAFPPBE':
          $db = $this->findDBConnection(USAFPPBE_SCHEMA);
          break;
        case 'SLRD':
          $db = $this->findDBConnection(SLRD_SCHEMA);
          break;
        case 'CAPDEV':
          $db = $this->findDBConnection(CAPDEV_SCHEMA);
          break;
        case 'TRIAD':
          $db = $this->findDBConnection(TRIAD_SCHEMA);
          break;
        case 'COMPETITION':
          $db = $this->findDBConnection(COMPETITION_SCHEMA);
          break;
        case 'THREAT':
          $db = $this->findDBConnection(THREAT_SCHEMA);
          break;
        case 'WSS':
          $db = $this->findDBConnection(WSS_SCHEMA);
          break;
        case 'MANPOWER':
          $db = $this->findDBConnection(MANPOWER_SCHEMA);
          break;
        case 'EAAFM':
          $db = $this->findDBConnection(EAAFM_SCHEMA);
          break;
        case 'STRATEGICBASING':
          $db = $this->findDBConnection(STRATEGICBASING_SCHEMA);
          break;
        case 'OBLIGATIONEXPENDITURE':
          $db = $this->findDBConnection(OBLIGATIONEXPENDITURE_SCHEMA);
          break;
        case 'CSPI':
          $db = $this->findDBConnection(CSPI_SCHEMA);
          break;
        case 'FH':
          $db = $this->findDBConnection(FH_SCHEMA);
          break;
        case 'COMBINED':
          $db = $this->findDBConnection(COMBINED_SCHEMA);
          break;
        case 'KG':
          $db = $this->findDBConnection(KG_SCHEMA);
          break;
        case 'OOB':
          $db = $this->findDBConnection(OOB_SCHEMA);
          break;
        case 'USSFPPBE':
          $db = $this->findDBConnection(USSFPPBE_SCHEMA);
          break;
        case 'SOCOM':
            $db = $this->findDBConnection(SOCOM_SCHEMA);
          break;
      }
    } else {
      log_message('error', 'Databases should not be loaded except when using the tiles pages');
      $db = false;
    }

    return $db;
  }

    public function load_tiles_dbs() {
      return in_array(strtolower($this->router->fetch_class ()), self::TILES_CONTROLLERS, true);
    }



    public function __destruct() {
      foreach ($this->activeDbs as $i => &$db) {
        $db->close();
        $db = null;
        unset($this->activeDbs[$i]);
      }
    }
  }
