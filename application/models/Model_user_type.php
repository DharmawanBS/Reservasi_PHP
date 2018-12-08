<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
 * Date: 06-Dec-18
 * Time: 9:15 PM
 */

class Model_user_type extends CI_Model
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
        $this->db->insert('user_type',$data);
        return $this->db->insert_id();
    }

    public function update($data,$id)
    {
        $this->db->where('user_type_id',$id);
        $this->db->update('user_type',$data);
    }

    private function _count()
    {
        $this->db->select(
            'user_type_id as id,
            count(*) as jumlah'
        );
        $this->db->from('user');
        $this->db->group_by('user_type_id');
        return $this->db->get_compiled_select();
    }

    public function select($id)
    {
        $count = $this->_count();

        $this->db->select(
            'user_type_id as id,
            user_type_name as name,
            (
                case
                    when jum.jumlah is null
                    then 0
                    else jum.jumlah
                end
            ) as jumlah'
        );
        if ( ! is_null($id)) {
            $this->db->where('user_type_id',$id);
        }$this->db->where('user_type_is_active',1);
        $this->db->from('user_type');
        $this->db->join('('.$count.') jum','jum.id = user_type.user_type_id','left');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }
}