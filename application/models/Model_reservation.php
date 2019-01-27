<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
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
        $this->db->group_start();
        $this->db->where('reservation_finish',1);
        $this->db->or_where('reservation_is_approved',1);
        $this->db->group_end();
        $this->db->where('vehicle_id',$id);
        $this->db->from('reservation');
        return $this->db->count_all_results() === 0;
    }

    public function is_waiting_approval($id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->where('reservation_finish',FALSE);
        $this->db->from('reservation');
        return $this->db->count_all_results() > 0;
    }

    public function is_before_date($id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->where('reservation_start >=',$this->date_time);
        $this->db->from('reservation');
        return $this->db->count_all_results() > 0;
    }

    public function select($id, $code, $vehicle, $start, $end, $is_public, $is_finish, $is_approved)
    {
        $query_payment = $this->payment_complete(NULL,TRUE);

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
            datediff(reservation.reservation_end,reservation.reservation_start) as duration,
            reservation.reservation_is_approved as is_approved,
            reservation.reservation_approved_datetime as approved_datetime,
            reservation.reservation_approved_id as approved_by,
            reservation.vehicle_id as vehicle_id,
            vehicle.vehicle_type as vehicle_type,
            vehicle.vehicle_number as vehicle_number,
            reservation.user_id as user,
            reservation.user_type_id as user_type_id,
            reservation.price as price,
            reservation.price*(datediff(reservation.reservation_end,reservation.reservation_start)) as total,
            reservation.reservation_datetime as created,
            (
                case
                when payment.paid is null
                then 0
                else payment.paid
                end
            ) as paid,
            (
                case
                when payment.status is null
                then FALSE
                else payment.status
                end
            ) as status_payment'
        );
        $this->db->from('reservation,vehicle');
        $this->db->join('('.$query_payment.') as payment','reservation.reservation_id = payment.reservation_id','left');
        if ($id !== null) {
            $this->db->where('reservation.reservation_id',$id);
        }
        if ($code !== null) {
            $this->db->like('LOWER(reservation.reservation_code)',strtolower($code));
        }
        if ($code !== null) {
            $this->db->like('LOWER(reservation.vehicle_id)',$vehicle);
        }
        if ($start !== null) {
            $this->db->where('reservation.reservation_start >=',$start);
        }
        if ($end !== null) {
            $this->db->where('reservation.reservation_end <=',$end);
        }
        if ($is_public !== null) {
            $this->db->where('reservation.user_id'.($is_public ? '' : ' !='),NULL);
        }
        if ($is_finish !== null) {
            $this->db->where('reservation.reservation_finish',$is_finish);
        }
        if ($is_approved !== null) {
            $this->db->where('reservation.reservation_is_approved',$is_approved);
        }
        $this->db->where('reservation.vehicle_id = vehicle.vehicle_id');
        $this->db->where('reservation.reservation_is_active',TRUE);
        $this->db->order_by('reservation.reservation_start','asc');
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }

    public function insert_payment($data)
    {
        $this->db->insert('payment',$data);
    }

    public function select_payment($id)
    {
        $this->db->where('reservation_id',$id);
        $this->db->from('payment');
        $this->db->order_by('payment_date','asc');
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) > 0) return $result;
        else return NULL;
    }

    public function payment_complete($id,$output_query = FALSE)
    {
        $this->db->select('price*(datediff(reservation_end,reservation_start)) as price,reservation_id');
        $this->db->from('reservation');
        $query_price = $this->db->get_compiled_select();

        $this->db->select('sum(payment_price) as total,reservation_id');
        $this->db->from('payment');
        $query_payment = $this->db->get_compiled_select();

        $this->db->from('('.$query_price.') as price,('.$query_payment.') as payment');
        $this->db->where('price.reservation_id = payment.reservation_id');
        if ($output_query) {
            $this->db->select('
                price.price as price,
                payment.total as paid,
                payment.reservation_id,
                (
                    case 
                    when price.price <= payment.total
                    then TRUE
                    else FALSE
                    end 
                ) as status
            ');
            return $this->db->get_compiled_select();
        }
        $this->db->where('price.price <= payment.total');
        $this->db->where('payment.reservation_id',$id);
        return $this->db->count_all_results() > 0;
    }
}