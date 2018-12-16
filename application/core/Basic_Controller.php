<?php
require_once APPPATH.'/libraries/REST_Controller.php';
/**
 * Created by PhpStorm.
 * User: dharmawan
 * Date: 12/09/18
 * Time: 22:21
 */

class Basic_Controller extends REST_Controller
{
    protected $date_time;
    protected $date;

    const MSG_OK = 'OK';
    // ouput failed
    const MSG_FAILED = 'FAILED';
    // some input were not sent
    const MSG_INVALID = 'INVALID';
    // token was expired or access API without token
    const MSG_UNAUTHORIZED = 'UNAUTHORIZED';
    //there is no data
    const MSG_EMPTY = 'EMPTY';
    //password required
    const MSG_PASSWORD = 'PASSWORD_REQUIRED';
    //ldap no output
    const MSG_LDAP_NO_OUTPUT = 'LDAP_NO_OUTPUT';
    //ldap server error
    const MSG_LDAP_ERROR = 'LDAP_ERROR';
    //default value paging
    const PAGING = 10;

    /**
     * Basic_Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->access();

        //  set default date time
        date_default_timezone_set('Asia/Jakarta');

        $this->date_time = date("Y-m-d H:i:s");
        $this->date = date("Y-m-d");
    }

    public static function access()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, token, refresh_token, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }
    }

    /**
     * output ok
     * @param string|array|int|null $data
     * @param array|null $meta
     */
    public function output_ok($data,$meta = NULL)
    {
        $this->response(
            $this->output(
                self::MSG_OK,
                $data,
                $meta),
            REST_Controller::HTTP_OK);
    }

    /**
     * output empty
     */
    public function output_empty()
    {
        $this->response(
            $this->output(
                self::MSG_EMPTY,
                NULL),
            REST_Controller::HTTP_OK);
    }

    /**
     * output invalid
     */
    public function output_invalid()
    {
        $this->response(
            $this->output(
                self::MSG_INVALID,
                NULL),
            REST_Controller::HTTP_NOT_FOUND);
    }

    /**
     * output failed
     * @param null $data
     */
    public function output_failed($data = NULL)
    {
        $this->response(
            $this->output(
                self::MSG_FAILED,
                $data),
            REST_Controller::HTTP_OK);
    }

    /**
     * output unauthorized
     */
    public function output_unauthorized()
    {
        $this->response(
            $this->output(
                self::MSG_UNAUTHORIZED,
                NULL),
            REST_Controller::HTTP_UNAUTHORIZED);
    }

    /**
     * default API output
     *
     * @param       string $msg
     * @param       array|string|null $data
     * @param       array|null $meta
     * @return      array
     */
    public function output($msg, $data, $meta = NULL)
    {
        if (is_null($meta)) $meta = $this->meta(is_null($data) ? 0 : (is_array($data) ? sizeof($data) : 1),FALSE,NULL);
        return array(
            'msg' => $msg,
            'data' => $data,
            'meta' => $meta
        );
    }

    /**
     * @param int $count
     * @param bool $allow_pagination
     * @param int $page
     * @param int $all
     * @return array
     */
    public function meta($count,$allow_pagination,$page,$all = 0)
    {
        $all = $all < $count ? $count : $all;

        return array(
            'count_all' => $all,
            'count' => $count,
            'allow_pagination' => $allow_pagination,
            'using_pagination' => ! is_null($page),
            'data_per_pagination' => $allow_pagination ? self::PAGING : 0,
            'curent_page' => $page,
            'page_count' => ceil($all/self::PAGING),
        );
    }

    /**
     * convert message to http_code
     *
     * @param string $message
     * @return int
     */
    private function _http_code($message) {
        switch($message) {
            case self::MSG_OK:
                return REST_Controller::HTTP_OK;
            case self::MSG_EMPTY:
                return REST_Controller::HTTP_OK;
            case self::MSG_UNAUTHORIZED:
                return REST_Controller::HTTP_UNAUTHORIZED;
            case self::MSG_FAILED:
                return REST_Controller::HTTP_NOT_FOUND;
            case self::MSG_INVALID:
                return REST_Controller::HTTP_NOT_FOUND;
            default:
                return REST_Controller::HTTP_OK;
        }
    }

    /**
     * Validating input variable
     *
     * @param $input
     * @param bool $is_numeric
     * @param bool $is_array
     * @param bool $continue
     * @param string $message
     * @return mixed
     */
    public function validate_input($input,$is_numeric = FALSE,$is_array = FALSE,$continue = FALSE,$message = self::MSG_INVALID)
    {
        if (is_null($input) OR $input === "") {
            if ($continue) return NULL;
            else $this->response($this->output($message,NULL), $this->_http_code($message));
        }
        else if ($is_numeric && ! is_numeric($input)) {
            if ($continue) return NULL;
            else $this->response($this->output($message,NULL), $this->_http_code($message));
        }
        else if ($is_array && ! is_array($input) && sizeof($input) <= 0) {
            if ($continue) return NULL;
            else $this->response($this->output($message,NULL), $this->_http_code($message));
        }
        return $input;
    }

    /**
     * convert data to boolean
     *
     * @param $input
     * @return bool
     */
    public function to_bool($input)
    {
        return is_null($input) ? FALSE : is_bool($input) ? $input : FALSE;
    }
}

/* End of file Basic_Controller.php */
/* Location: ./application/core/Basic_Controller.php */