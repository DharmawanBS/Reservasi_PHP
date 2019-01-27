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

    private function _sub_reservation($start,$end,$compiled = TRUE)
    {
        $this->db->where('reservation_is_active',TRUE);
        $this->db->where('reservation_start >=', $start);
        $this->db->where('reservation_start <=', $end);
        $this->db->from('reservation');
        if ($compiled) {
            return $this->db->get_compiled_select();
        }

        $query = $this->db->get();
        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }

    public function user_per_month($start,$end,$year)
    {
        $start_date = date($year.'-'.$start.'-01');
        $end_date = date($year.'-'.$end.'-t');

        $this->db->select('CAST(SUBSTR(reservation_start, 6, 2) as INT) as month,count(lower(reservation_client_name)) as count');
        $this->db->group_by('lower(reservation_client_name),month');
        $sub_reservation = $this->_sub_reservation($start_date,$end_date);

        $this->db->select('month.value as month,(case when reservasi.count is null then 0 else reservasi.count end) as count');
        $this->db->where('value >=', $start);
        $this->db->where('value <=', $end);
        $this->db->from('month');
        $this->db->join('('.$sub_reservation.') as reservasi','reservasi.month = month.value','left');
        $this->db->order_by('value','asc');
        $query = $this->db->get();

        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }

    public function transaction_per_month($start,$end,$year)
    {
        $start_date = date($year.'-'.$start.'-01');
        $end_date = date($year.'-'.$end.'-t');

        $this->db->select('CAST(SUBSTR(reservation_start, 6, 2) as INT) as month,count(*) as count');
        $this->db->where('reservation_finish',TRUE);
        $this->db->group_by('month');
        $sub_reservation = $this->_sub_reservation($start_date,$end_date);

        $this->db->select('month.value as month,(case when reservasi.count is null then 0 else reservasi.count end) as count');
        $this->db->where('value >=', $start);
        $this->db->where('value <=', $end);
        $this->db->from('month');
        $this->db->join('('.$sub_reservation.') as reservasi','reservasi.month = month.value','left');
        $this->db->order_by('value','asc');
        $query = $this->db->get();

        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }

    public function income_total_per_month($start,$end,$year)
    {
        $start_date = date($year.'-'.$start.'-01');
        $end_date = date($year.'-'.$end.'-t');

        $this->db->select('CAST(SUBSTR(reservation_start, 6, 2) as INT) as month,
                            sum(datediff(reservation_end,reservation_start)*
                            (
                              case
                                when price IS NULL
                                  THEN 0
                                ELSE price
                                END
                              )) AS total'
        );
        $this->db->where('reservation_finish',TRUE);
        $this->db->group_by('month');
        $sub_reservation = $this->_sub_reservation($start_date,$end_date);

        $this->db->select('month.value as month,(case when reservasi.total is null then 0 else reservasi.total end) as total');
        $this->db->where('value >=', $start);
        $this->db->where('value <=', $end);
        $this->db->from('month');
        $this->db->join('('.$sub_reservation.') as reservasi','reservasi.month = month.value','left');
        $this->db->order_by('value','asc');
        $query = $this->db->get();

        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }
    
    public function user($year)
    {
        $start_date = date($year.'-01-01');
        $end_date = date($year.'-12-t');

        $this->db->select('count(lower(reservation_client_name)) as count');
        $sub = $this->_sub_reservation($start_date,$end_date,FALSE);
        if ($sub) {
            if ($sub[0]->count === NULL) return 0;
            return $sub[0]->count;
        }
        else return 0;
    }

    public function transaction($year)
    {
        $start_date = date($year.'-01-01');
        $end_date = date($year.'-12-t');

        $this->db->select('count(*) as count');
        $this->db->where('reservation_finish',TRUE);
        $sub = $this->_sub_reservation($start_date,$end_date,FALSE);
        if ($sub) {
            if ($sub[0]->count === NULL) return 0;
            return $sub[0]->count;
        }
        else return 0;
    }

    public function income_total($year)
    {
        $start_date = date($year.'-01-01');
        $end_date = date($year.'-12-t');

        $this->db->select('sum(datediff(reservation_end,reservation_start)*
                            (
                              case
                                when price IS NULL
                                  THEN 0
                                ELSE price
                                END
                              )) AS total'
        );
        $this->db->where('reservation_finish',TRUE);
        $sub = $this->_sub_reservation($start_date,$end_date,FALSE);
        if ($sub) {
            if ($sub[0]->total === NULL) return 0;
            return $sub[0]->total;
        }
        else return 0;
    }

    public function vehicle()
    {
        $this->db->where('vehicle_is_active',TRUE);
        $this->db->from('vehicle');
        return $this->db->count_all_results();
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