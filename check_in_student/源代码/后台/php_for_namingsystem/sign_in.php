<?php
/**
 * @author:黄宏运(170327031)&方炜杰(170327021)
 */
header("ACCESS-CONTROL-ALLOW-ORIGIN: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Methods: GET, POST, PUT,DELETE");

//获取数据
$data = json_decode(file_get_contents("php://input"));
//echo json_encode($data->c_id);


/* Connect to a MySQL server  连接数据库服务器 */
$link = mysqli_connect(
    'localhost',  /* The host to connect to 连接MySQL地址 */
    'root',      /* The user to connect as 连接MySQL用户名 */
    'root',  /* The password to use 连接MySQL密码 */
    'test');    /* The default database to query 连接数据库名称*/

//$link = mysqli_connect(
//    '127.0.0.1:3306',  /* The host to connect to 连接MySQL地址 */
//    'root',      /* The user to connect as 连接MySQL用户名 */
//    '21Af93349a02',  /* The password to use 连接MySQL密码 */
//    'wordpress');    /* The default database to query 连接数据库名称*/

//数据库连接判别
if (!$link) {
    printf("Can't connect to MySQL Server. Error code: %s ", mysqli_connect_error());
    exit;
}

$output_true = array('answer' => true);
$output_false = array('answer' => false);

function check($data, $link, $output_true, $output_false){
    /**
     * 函数的含义说明
     *
     * 判断该课程是否开始签到
     *
     * @access public
     * @param $data $arg1 前端数据
     * @param $link $arg2 数据库连接
     * @param $output_false $arg3 运行错误返回值
     *
     **/
    $check = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM seat_info WHERE c_id = \"".$data->c_id."\""));

    if($check != null){
        $output = array(
            'answer' => true,
            'seat' => json_decode($check["seat"])
        );
        echo json_encode($output);
    }

    else
        echo json_encode($output_false);
    }

function start_sign_in($data, $link, $output_true, $output_false){
    /**
     * 函数的含义说明
     *
     * 将签到状态改为可以开始签到
     *
     * @access public
     * @param $data $arg1 前端数据
     * @param $link $arg2 数据库连接
     * @param $output_true $arg3 运行正确返回值
     * @param $output_false $arg4 运行错误返回值
     *
     **/
    $flag = mysqli_query($link, "INSERT INTO seat_info (c_id,seat)
VALUES(\"".$data->c_id."\",".json_encode(json_encode($data->seat)).")");
    //信息导入成功，返回确认信息
    if($flag == true)
        echo json_encode($output_true);
    else
        echo json_encode($output_false);
}

function end_sign_in($data, $link, $output_true, $output_false){
    /**
     * 函数的含义说明
     *
     * 结束签到
     *
     * @access public
     * @param $data $arg1 前端数据
     * @param $link $arg2 数据库连接
     * @param $output_true $arg3 运行正确返回值
     * @param $output_false $arg4 运行错误返回值
     *
     **/
    $flag_1 = mysqli_query($link, "DELETE FROM seat_info WHERE c_id = \"".$data->c_id."\"");
    $flag_2 = mysqli_query($link, "DELETE FROM sign_info WHERE c_id = \"".$data->c_id."\"");
    if($flag_1 != false || $flag_2 != false)
        echo json_encode($output_true);
    else
        echo json_encode($output_false);
}

function sign_in($data, $link, $output_true, $output_false){
    /**
     * 函数的含义说明
     *
     * 签到功能
     *
     * @access public
     * @param $data $arg1 前端数据
     * @param $link $arg2 数据库连接
     * @param $output_true $arg3 运行正确返回值
     * @param $output_false $arg4 运行错误返回值
     *
     **/
    $result = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM seat_info WHERE  c_id = \"".$data->c_id."\""));
    $p = $data->position;
    if($result != null) {
        $re_seat = json_decode($result["seat"]);
        if($re_seat->detail[$p]->avail == true) {
            $re_seat->detail[$p]->avail = false;
            $re_seat->detail[$p]->s_account = $data->s_account;
            mysqli_query($link, "UPDATE seat_info SET seat=".json_encode(json_encode($re_seat))." WHERE c_id = \"".$data->c_id."\"");
            mysqli_query($link, "UPDATE select_course_info SET sign_count = sign_count+1 WHERE s_account = ".$data->s_account." AND c_id = \"".$data->c_id."\"");
            mysqli_query($link, "INSERT INTO sign_info (s_account, c_id) VALUES (".$data->s_account.",\"".$data->c_id."\")");
            echo json_encode($output_true);
        } else
            echo json_encode($output_false);
    } else
        echo json_encode(array('answer' => 'late'));
}

function check_sign($data, $link, $output_true, $output_false){
    /**
     * 函数的含义说明
     *
     * 检查表里的签到信息
     *
     * @access public
     * @param $data $arg1 前端数据
     * @param $link $arg2 数据库连接
     * @param $output_true $arg3 运行正确返回值
     * @param $output_false $arg4 运行错误返回值
     *
     **/
    $result =mysqli_query($link, "SELECT * FROM sign_info WHERE s_account = ".$data->s_account." AND c_id = \"".$data->c_id."\"");
    if($row = mysqli_fetch_assoc($result) != null)
        echo json_encode($output_false);
    else
        echo json_encode($output_true);
}

function show_detail($data, $link){
    /**
     * 函数的含义说明
     *
     * 返回签到的学生信息
     *
     * @access public
     * @param $data $arg1 前端数据
     * @param $link $arg2 数据库连接
     * @param $output_true $arg3 运行正确返回值
     * @param $output_false $arg4 运行错误返回值
     *
     **/
    $result = mysqli_fetch_assoc(mysqli_query($link, 'SELECT * FROM student_info WHERE account = ' .$data->s_account));
    echo json_encode($result);
}

if($data->action == 'check')
    check($data, $link, $output_true, $output_false);
elseif($data->action == 'start_sign_in')
    start_sign_in($data, $link, $output_true, $output_false);
elseif($data->action == 'end_sign_in')
    end_sign_in($data, $link, $output_true, $output_false);
elseif($data->action == 'sign_in')
    sign_in($data, $link, $output_true, $output_false);
elseif($data->action == 'check_sign')
    check_sign($data, $link, $output_true, $output_false);
elseif($data->action == 'show_detail')
    show_detail($data, $link);