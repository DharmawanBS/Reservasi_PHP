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

    public function update($data,$id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->update('reservation',$data);
    }

    public function delete($id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->delete('reservation');
    }

    public function is_free($id,$start,$end)
    {
        $this->db->group_start();
        $this->db->group_start();
        $this->db->where('reservation_start >=',$start);
        $this->db->where('reservation_start <=',$end);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('reservation_end >=',$start);
        $this->db->where('reservation_end <=',$end);
        $this->db->group_end();
        $this->db->group_end();
        $this->db->where('reservation_is_approved',1);
        $this->db->where('reservation_is_cancel',NULL);
        $this->db->where('vehicle_id',$id);
        $this->db->from('reservation');
        return $this->db->count_all_results() == 0;
    }

    public function is_waiting_approval($id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->group_start();
        $this->db->where('reservation_is_cancel',NULL);
        $this->db->where('reservation_cancel_datetime',NULL);
        $this->db->where('reservation_cancel_id',NULL);
        $this->db->where('reservation_is_approved',NULL);
        $this->db->where('reservation_approved_datetime',NULL);
        $this->db->where('reservation_approved_id',NULL);
        $this->db->group_end();
        return $this->db->count_all_results() > 0;
    }

    public function is_before_date($id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->where('reservation_start <=',$this->date_time);
        return $this->db->count_all_results() > 0;
    }

    public function select($id, $code, $start, $end, $is_approved, $is_cancel)
    {
        $this->db->select(
            'reservation.reservation_id as id,
            reservation.reservation_code as code,
            reservation.reservation_client_id as client_id,
            reservation.reservation_client_name as client_name,
            reservation.reservation_client_phone as client_phone,
            reservation.reservation_destination as destination,
            reservation.reservation_pick_up_location as pick_up_location,
            reservation.reservation_notes as notes,
            reservation.reservation_start as start,
            reservation.reservation_end as end,
            reservation.reservation_is_approved as is_approved,
            reservation.reservation_approved_datetime as approved_datetime,
            reservation.reservation_approved_id as approved_by,
            reservation.reservation_is_cancel as is_cancel,
            reservation.reservation_cancel_datetime as cancel_datetime,
            reservation.reservation_cancel_id as cancel_by,
            reservation.vehicle_id as vehicle_id,
            vehicle.vehicle_type as vehicle_type,
            vehicle.vehicle_number as vehicle_number,
            reservation.user_id as user,
            reservation.user_type_id as user_type_id,
            reservation.price as price,
            reservation.reservation_datetime as created'
        );
        if ( ! is_null($id)) {
            $this->db->where('reservation.reservation_id',$id);
        }
        if ( ! is_null($code)) {
            $this->db->like('LOWER(reservation.reservation_code)',strtolower($code));
        }
        if ( ! is_null($start)) {
            $this->db->where('reservation.reservation_start >=',$start);
        }
        if ( ! is_null($end)) {
            $this->db->where('reservation.reservation_end <=',$end);
        }
        if ( ! is_null($is_approved)) {
            $this->db->where('reservation.reservation_is_approved',$is_approved);
        }
        if ( ! is_null($is_cancel)) {
            $this->db->where('reservation.reservation_is_cancel',$is_cancel);
        }
        $this->db->where('reservation.vehicle_id = vehicle.vehicle_id');
        $this->db->where('reservation.user_id = user.user_id');
        $this->db->where('reservation.reservation_is_active',TRUE);
        $this->db->where('reservation.reservation_approved_id = user.user_id');
        $this->db->from('reservation,vehicle,user');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }

    public function insert_crew($data)
    {
        $this->db->insert_batch('crew',$data);
    }

    public function select_crew($id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->from('crew');
        $this->db->order_by('crew_status','asc');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }
}