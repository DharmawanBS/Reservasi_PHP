<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
 * Date: 06-Dec-18
 * Time: 10:24 PM
 */

class Model_reservation extends CI_Model
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
        $this->db->insert('reservation',$data);
        return $this->db->insert_id();
    }

    public function insert_crew($data)
    {
        $this->db->insert_batch('crew',$data);
    }

    public function update($data,$id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->update('reservation',$data);
    }

    public function is_free($id,$start,$end)
    {
        $this->db->group_start();
        $this->db->where('reservation_start >=',$start);
        $this->db->where('reservation_start <=',$end);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('reservation_end >=',$start);
        $this->db->where('reservation_end <=',$end);
        $this->db->group_end();
        $this->db->where('reservation_is_approved',1);
        $this->db->where('vehicle_id',$id);
        $this->db->from('reservation');
        return $this->db->count_all_results() == 0;
    }

    public function select($id, $code, $start, $end, $is_approved)
    {
        $this->db->select(
            'reservation_id as id,
            reservation_code as code,
            reservation_start as start,
            reservation_end as end,
            reservation_is_approved as app,
            reservation_approved_datetime as appdate,
            reservation_approved_id as appuser,
            reservation_is_active as active,
            vehicle_id as vehicle,
            user_id as user,
            price_id as price,
            reservation_datetime as created'
        );
        if ( ! is_null($id)) {
            $this->db->where('reservation_id',$id);
        }
        if ( ! is_null($code)) {
            $this->db->like('LOWER(reservation_code)',strtolower($code));
        }
        if ( ! is_null($start)) {
            $this->db->where('reservation_start >=',$start);
        }
        if ( ! is_null($end)) {
            $this->db->where('reservation_end <=',$end);
        }
        if ( ! is_null($is_approved)) {
            $this->db->where('reservation_is_approved',$is_approved);
        }
        $this->db->where('reservation.vehicle_id = vehicle.vehicle_id');
        $this->db->where('reservation.user_id = user.user_id');
        $this->db->where('reservation.reservation_approved_id = user.user_id');
        $this->db->where('reservation.price_id = price.price_id');
        $this->db->from('reservation,vehicle,user,price');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }
}