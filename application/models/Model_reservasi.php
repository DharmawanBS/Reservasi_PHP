<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
 * Date: 06-Dec-18
 * Time: 10:24 PM
 */

class Model_reservasi extends CI_Model
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
        $this->db->insert('reservasi',$data);
        return $this->db->insert_id();
    }

    public function update($data,$id)
    {
        $this->db->where('reservasi_id',$id);
        $this->db->update('reservasi',$data);
    }

    public function is_free($id,$start,$end)
    {
        $this->db->group_start();
        $this->db->where('reservasi_start >=',$start);
        $this->db->where('reservasi_start <=',$end);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('reservasi_end >=',$start);
        $this->db->where('reservasi_end <=',$end);
        $this->db->group_end();
        $this->db->where('reservasi_vehicle',$id);
        $this->db->from('reservasi');
        return $this->db->count_all_results() == 0;
    }

    public function select($id,$booking,$start,$end)
    {
        $this->db->select(
            'reservasi_id as id,
            reservasi_booking as booking,
            reservasi_name as name,
            reservasi_phone as phone,
            reservasi_destination as destination,
            reservasi_pick_up_location as pick_up_location,
            reservasi_start as start,
            reservasi_end as end,
            reservasi_vehicle as vehicle,
            reservasi_notes as notes,
            reservasi_price as price,
            reservasi_created as created,
            vehicle_type as type,
            vehicle_number as number'
        );
        if ( ! is_null($id)) {
            $this->db->where('reservasi_id',$id);
        }
        if ( ! is_null($booking)) {
            $this->db->like('LOWER(reservasi_booking)',strtolower($booking));
        }
        if ( ! is_null($start)) {
            $this->db->where('reservasi_start >=',$start);
        }
        if ( ! is_null($end)) {
            $this->db->where('reservasi_end <=',$end);
        }
        $this->db->where('reservasi.reservasi_vehicle = vehicle.vehicle_id');
        $this->db->from('reservasi,vehicle');
        $query = $this->db->get();
        $result = $query->result();
        if (sizeof($result) > 0) return $result;
        else return NULL;
    }
}