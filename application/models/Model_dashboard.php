<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 08-Jan-19
 * Time: 8:04 PM
 */

class Model_dashboard extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');
        $this->date_time = date("Y-m-d H:i:s");
        $this->date = date("Y-m-d");
    }
    
    public function user($month = NULL)
    {
        $this->db->select('LOWER(reservation_client_name)');
        $this->db->where('reservation_is_active',TRUE);
        if ($month !== NULL) {
            $start = date('Y-'.$month.'-01');
            $end = date('Y-m-t',$start);
            $this->db->where('reservation_start >=', $start);
            $this->db->where('reservation_end <=', $end);
        }
        $this->db->from('reservation');
        $query = $this->db->get_compiled_select();

        $this->db->distinct();
        $this->db->from('('.$query.') SUB');
        return $this->db->count_all_results();
    }

    public function transaction($month = NULL)
    {
        $this->db->where('reservation_is_active',TRUE);
        if ($month !== NULL) {
            $start = date('Y-'.$month.'-01');
            $end = date('Y-m-t',$start);
            $this->db->where('reservation_start >=', $start);
            $this->db->where('reservation_end <=', $end);
        }
        $this->db->from('reservation');
        return $this->db->count_all_results();
    }

    public function vehicle()
    {
        $this->db->where('vehicle_is_active',TRUE);
        $this->db->from('vehicle');
        return $this->db->count_all_results();
    }

    public function income_total($month)
    {
        $this->db->select(
        'sum(datediff(reservation_end,reservation_start)*
        (
          case
            when price IS NULL
              THEN 0
            ELSE price
            END
          )) AS total');
        $this->db->where('reservation_is_active',TRUE);
        $this->db->where('reservation_is_approved',TRUE);
        if ($month !== NULL) {
            $start = date('Y-'.$month.'-01');
            $end = date('Y-m-t',$start);
            $this->db->where('reservation_start >=', $start);
            $this->db->where('reservation_end <=', $end);
        }
        $this->db->from('reservation');
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) > 0) return $result[0]->total;
        else return NULL;
    }

    public function income_per_vehicle($month)
    {
        $this->db->select(
        'sum(datediff(reservation_end,reservation_start)*
        (
          case
            when price IS NULL
              THEN 0
            ELSE price
            END
          )) AS total,count(*) as count,
          vehicle_id as id'
        );
        $this->db->where('reservation_is_active',TRUE);
        $this->db->where('reservation_is_approved',TRUE);
        if ($month !== NULL) {
            $start = date('Y-'.$month.'-01');
            $end = date('Y-m-t',$start);
            $this->db->where('reservation_start >=', $start);
            $this->db->where('reservation_end <=', $end);
        }
        $this->db->from('reservation');
        $this->db->group_by('vehicle_id');
        $query = $this->db->get_compiled_select();

        $this->db->select(
            'vehicle.vehicle_id as id,
            vehicle.vehicle_type as type,
            vehicle.vehicle_number as number,
            (
                case when sub.total is null
                then 0
                else sub.total
                end
            ) as total'
        );
        $this->db->from('vehicle');
        $this->db->join('('.$query.') sub','sub.id = vehicle.vehicle_id','left');
        $this->db->order_by('sub.total','desc');
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }
}