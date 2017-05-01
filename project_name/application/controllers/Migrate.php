<?php

class Migrate extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    public function run() {
        $this->load->library('migration');
        $this->load->model('migration_logs_model'); 
        
        $version = $this->input->post('version');

        $data['version'] = $version;
        $data['description'] = $this->migration->get_name($version);
        
        if ( ! $this->migration->version($version) ){
                $this->session->set_flashdata('message', 'Error!');
                redirect(base_url().'migrate', 'refresh');
        } else {
            if($this->migration_logs_model->create($data)){
                $this->session->set_flashdata('message', 'Success!');
                redirect(base_url().'migrate', 'refresh');
            } else { 
                $this->session->set_flashdata('message', 'Error!');
                redirect(base_url().'migrate', 'refresh');
            }
        }
    }
    
    public function index($start = 0){
        $this->load->model('migration_logs_model'); 

        $max = 10;

        $config['base_url'] = base_url() . $this->router->class . '/index';
        $config['total_rows'] = $this->migration_logs_model->total_of_rows();
        $config['per_page'] = $max;
        
        $this->pagination->initialize($config);

        $dir = 'PAHT_TO/application/migrations';

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    $data['files'][] = $file;
                }
                closedir($dh);
            }
        } 

        $data["pagination"] = $this->pagination->create_links();
                
        if(isset($_POST)){
            $filter = trim($this->input->post('filter'));
            $data['migrations'] = $this->migration_logs_model->show_search($filter,$max, $start);
        } else {
            $data['migrations'] = $this->migration_logs_model->show_search($max, $start);
        }
        
        $this->load->view('migrations/index' , $data);
    }
}