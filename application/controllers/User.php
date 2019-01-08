<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 08-Dec-18
 * Time: 12:45 PM
 */

/**
 * @property Model_user $Model_user
 */
class User extends Basic_Controller
{

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Model_user');
    }

    private function _check_input($user,$id,$name,$key,$password,$type)
    {
        $data = array(
            'user_name' => $name,
            'user_type_id' => $type,
            'user_lastmodified' => $this->date_time,
            'user_lastmodified_id' => $user
        );

        if ($key !== null) {
            $data['user_key'] = $key;
        }
        if ($password !== null) {
            $data['user_password'] = $password;
        }

        if ($id === null) {
            $data['user_created'] = $this->date_time;
            $data['user_created_id'] = $user;
            $data['user_is_active'] = TRUE;
            $id = $this->Model_user->insert($data);
        }
        else {
            $this->Model_user->update($data,$id);
        }

        return $id;
    }

    public function insert_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);
        $name = $this->validate_input(@$data['name']);
        $key = $this->validate_input(@$data['key']);
        $password = $this->validate_input(@$data['password'],FALSE,TRUE);
        $type = $this->validate_input(@$data['type'],TRUE,TRUE);

        if ($this->Model_user->already_used(NULL,$key, 0)) {
            $this->output_failed();
        }

        $id = $this->_check_input($user,NULL,$name,$key,$password,$type);

        $this->output_ok($id);
    }

    public function update_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $name = $this->validate_input(@$data['name']);
        $key = $this->validate_input(@$data['key'],FALSE,FALSE,TRUE);
        $password = $this->validate_input(@$data['password'],FALSE,TRUE,TRUE);
        $type = $this->validate_input(@$data['type'],TRUE,TRUE);

        if ($this->Model_user->already_used($id,$key, 1)) {
            $this->output_failed();
        }

        $id = $this->_check_input($user,$id,$name,$key,$password,$type);

        $this->output_ok($id);
    }

    public function read_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,TRUE);
        $status = $this->validate_input(@$data['status'],FALSE,FALSE,TRUE);

        $data = $this->Model_user->select($id,$status);
        if ($data === null) {
            $this->output_empty();
        }
        else {
            $this->output_ok($data);
        }
    }

    public function delete_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);

        $data = array(
            'user_is_active' => FALSE
        );
        $this->Model_user->update($data,$id);
        $this->output_ok(NULL);
    }

    public function activate_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);

        $data = array(
            'user_lastmodified' => $this->date_time,
            'user_lastmodified_id' => $user,
            'user_status' => TRUE
        );
        $this->Model_user->update($data,$id);

        $this->output_ok(NULL);
    }

    public function deactivate_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);

        $data = array(
            'user_lastmodified' => $this->date_time,
            'user_lastmodified_id' => $user,
            'user_status' => FALSE
        );
        $this->Model_user->update($data,$id);

        $this->output_ok(NULL);
    }
}