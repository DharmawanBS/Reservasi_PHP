<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 06-Dec-18
 * Time: 9:15 PM
 */

class Model_vehicle extends CI_Model
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
        //get_compiled_insert/update
        $this->db->insert('vehicle',$data);
        return $this->db->insert_id();
    }

    public function update($data,$id)
    {
        $this->db->where('vehicle_id',$id);
        $this->db->update('vehicle',$data);
    }

    private function _status($date_start,$date_end)
    {
        $this->db->select(
            'vehicle_id as vehicle,
            count(*) as jumlah'
        );
        $this->db->group_start();
        $this->db->where('reservation_start >=',$date_start);
        $this->db->where('reservation_start <=',$date_end);
        $this->db->group_end();
        $this->db->from('reservation');
        $this->db->group_by('vehicle_id');
        return $this->db->get_compiled_select();
    }

    public function select($id, $is_free, $date_start, $date_end, $status)
    {
        $count = $this->_status($date_start,$date_end);

        $this->db->select(
            'vehicle_id as id,
            vehicle_type as type,
            vehicle_number as number,
            vehicle_status as status,
            (
                case
                    when jum.jumlah is null || jum.jumlah = 0
                    then TRUE
                    else FALSE
                end
            ) as is_free'
        );
        if ($status !== null) {
            $this->db->where('vehicle_status',$status);
        }
        if ($id !== null) {
            $this->db->where('vehicle_id',$id);
        }
        $this->db->where('vehicle_is_active',1);
        $this->db->from('vehicle');
        $this->db->join('('.$count.') jum','jum.vehicle = vehicle.vehicle_id','left');
        if (is_bool($is_free)) {
            $sub_query = $this->db->get_compiled_select();
            $this->db->select();
            $this->db->from('('.$sub_query.') sub');
            $this->db->where('is_free',$is_free);
        }
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }

    public function update_feature($data,$id)
    {
        $this->db->where('vehicle_feature_id',$id);
        $this->db->delete('vehicle_feature');

        if (count($data) > 0) {
            $this->db->insert_batch('vehicle_feature',$data);
        }
    }

    public function select_feature($id)
    {
        $this->db->select(
            'vehicle_feature_key as key,
            vehicle_feature_value as value'
        );
        $this->db->where('vehicle_feature_id',$id);
        $this->db->from('vehicle_feature');
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }
}