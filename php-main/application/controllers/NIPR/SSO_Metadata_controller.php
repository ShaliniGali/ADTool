<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SSO_Metadata_controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->useraccounttype->checkSuperAdmin()) {
            $this->output->set_status_header(401);
            exit();
        }
    }
    public function getSelfMetadata() {
        $base_url = parse_url(($_SERVER['SCRIPT_URI']));
        $make_url = $base_url['scheme']."://".$base_url['host'].":".$base_url['port']."/";
        $_SERVER['PATH_INFO'] = '/' . hash('sha256', $make_url);
        $_REQUEST['output'] = 'xhtml';
        $_REQUEST['rbsp'] = 'metadata';
        require_once(APPPATH . 'simplesamlphp/lib/_autoload.php');
        require_once(APPPATH . 'simplesamlphp/modules/saml/www/sp/metadata.php');
        echo '<pre>';
        $metaArray20['entityid'] = $entityId;
        echo json_encode($metaArray20);
        echo '</pre>';
    }

    public function getAllRemoteSPMetadatas() {
        require_once(APPPATH . 'simplesamlphp/lib/_autoload.php');
        $_REQUEST['rbadmin'] = true;
        require_once(APPPATH . 'simplesamlphp/modules/core/www/frontpage_federation.php');

        var_dump($t->data['metaentries']['remote']);
    }
    
}