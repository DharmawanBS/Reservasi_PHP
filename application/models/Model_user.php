<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
 * Date: 06-Dec-18
 * Time: 9:15 PM
 */

class Model_user extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');
        $this->date_time = date("Y-m-d H:i:s");
        $this->date = date("Y-m-d");
    }

    public function insert($data)
    {
        $this->db->insert('user',$data);
        return $this->db->insert_id();
    }

    public function update($data,$id)
    {
        $this->db->where('user_id',$id);
        $this->db->update('user',$data);
    }

    public function select($id,$status)
    {
        $this->db->select(
            'user_id as id,
            user_name as name,
            user_type_id as type,
            user_status as status'
        );
        if ( ! is_null($id)) {
            $this->db->where('user_id',$id);
        }
        if ( ! is_null($status)) {
            $this->db->where('user_status',$status);
        }
        $this->db->where('user_is_active',1);
        $this->db->from('user');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }
}