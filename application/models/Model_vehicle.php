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
            'reservasi_vehicle as vehicle,
            count(*) as jumlah'
        );
        $this->db->group_start();
        $this->db->where('reservasi_start >=',$date);
        $this->db->where('reservasi_start <=',$date);
        $this->db->group_end();
        $this->db->where('reservasi_vehicle');
        $this->db->from('reservasi');
        $this->db->group_by('reservasi_vehicle');
        return $this->db->get_compiled_select();
    }

    public function select($id,$is_free,$date)
    {
        $count = $this->_status($date);

        $this->db->select(
            'vehicle_id as id,
            vehicle_type as type,
            vehicle_number as number,
            (
                case
                    when jum.jumlah is null || jum.jumlah = 0
                    then TRUE
                    else FALSE
                end
            ) as is_free'
        );
        if ( ! is_null($id)) {
            $this->db->where('vehicle_id',$id);
        }$this->db->where('vehicle_is_active',1);
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

    public function find_price($id,$date,$usertype)
    {
        $this->db->select('price_price');
        $this->db->where('vehicle_id',$id);
        $this->db->where('user_type_id',$usertype);
        $this->db->where('price_start <=',$date);
        $this->db->order_by('price_start', 'desc');
        $this->db->limit(1);
        $this->db->from('price');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result[0]->price_price;
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