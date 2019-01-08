<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 06-Dec-18
 * Time: 9:03 PM
 */

class Model_auth extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');
        $this->date_time = date("Y-m-d H:i:s");
        $this->date = date("Y-m-d");
    }

    public function auth($username,$password)
    {
        $this->db->select('user_id');
        $this->db->where('user_key',$username);
        $this->db->where('user_password',$password);
        $this->db->where('user_is_active',1);
        $this->db->where('user_status',1);
        $this->db->from('user');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result[0]->user_id;
        else return NULL;
    }
}