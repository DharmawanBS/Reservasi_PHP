<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
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
            'user_key' => $key,
            'user_password' => $password,
            'user_name' => $name,
            'user_type_id' => $type,
            'user_lastmodified' => $this->date_time,
            'user_lastmodified_id' => $user
        );

        if (is_null($id)) {
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
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $name = $this->validate_input(@$data['name'],FALSE,FALSE,FALSE);
        $key = $this->validate_input(@$data['key'],FALSE,FALSE,FALSE);
        $password = $this->validate_input(@$data['password'],FALSE,TRUE,FALSE);
        $type = $this->validate_input(@$data['type'],TRUE,TRUE,FALSE);

        $id = $this->_check_input($user,NULL,$name,$key,$password,$type);

        $this->output_ok($id);
    }

    public function update_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,FALSE);
        $name = $this->validate_input(@$data['name'],FALSE,FALSE,FALSE);
        $key = $this->validate_input(@$data['key'],FALSE,FALSE,FALSE);
        $password = $this->validate_input(@$data['password'],FALSE,TRUE,FALSE);
        $type = $this->validate_input(@$data['type'],TRUE,TRUE,FALSE);

        $id = $this->_check_input($user,$id,$name,$key,$password,$type);

        $this->output_ok($id);
    }

    public function read_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,TRUE);

        $data = $this->Model_user->select($id);
        if (is_null($data)) {
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
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,FALSE);

        $data = array(
            'user_is_active' => FALSE
        );
        $this->Model_user->update($data,$id);
        $this->output_ok(NULL);
    }
}