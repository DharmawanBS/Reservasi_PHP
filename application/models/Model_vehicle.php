<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
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

    private function _status($date)
    {
        $this->db->select(
            'vehicle_id as vehicle,
            count(*) as jumlah'
        );
        $this->db->group_start();
        $this->db->where('reservation_start >=',$date);
        $this->db->where('reservation_start <=',$date);
        $this->db->group_end();
        $this->db->from('reservation');
        $this->db->group_by('vehicle_id');
        return $this->db->get_compiled_select();
    }

    public function select($id,$is_free,$date,$status)
    {
        $count = $this->_status($date);

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
        if ( ! is_null($status)) {
            $this->db->where('vehicle_status',$status);
        }
        if ( ! is_null($id)) {
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
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }

    public function update_feature($data,$id)
    {
        $this->db->where('vehicle_feature_id',$id);
        $this->db->delete('vehicle_feature');

        if (sizeof($data) > 0) {
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
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }

    public function find_price($id, $date, $user_type)
    {
        $this->db->select('price.price_price as normal_price,vehicle.vehicle_price as global_price');
        $this->db->where('vehicle.vehicle_id',$id);
        $this->db->order_by('price.price_start', 'desc');
        $this->db->limit(1);
        $this->db->from('vehicle');
        $this->db->join('price',"vehicle.vehicle_id = price.vehicle_id AND price.user_type_id = ".$user_type." AND price.price_start <= '".$date."'",'left');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result[0];
        else return NULL;
    }

    public function update_price($data,$id)
    {
        $this->db->where('vehicle_id',$id);
        $this->db->delete('price');

        if (sizeof($data) > 0) {
            $this->db->insert_batch('price',$data);
        }
    }

    public function select_price($id)
    {
        $this->db->select('p.price_price as price, p.price_start as start, p.user_type_id as type_id, u.user_type_name as type_name');
        $this->db->where('p.vehicle_id',$id);
        $this->db->where('p.user_type_id=u.user_type_id');
        $this->db->from('price p, user_type u');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }
}