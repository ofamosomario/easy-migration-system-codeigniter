<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class migration_logs_model extends CI_Model {
    function create($data) {
        return $this->db->insert('migration_logs', $data);
    }
    
    function total_of_rows(){
        return $this->db->count_all_results('migration_logs');
    }
    
    function show_search($filtro,$maximo, $inicio) {
        if($filtro == ''){
            $this->db->order_by('id desc');
            $query = $this->db->get('migration_logs', $maximo, $inicio);
        } else {
            $query = $this->db->query("select * from migration_logs where description like '%$filtro%' ");
        }
        return $query->result();
    }
}
