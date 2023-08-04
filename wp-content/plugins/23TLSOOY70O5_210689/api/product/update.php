<?php
include "../../config/systemConfig.php";
include "../base_request.php";
include "database/update_database.php";
class update extends base_request{

    function update_product($item){
        global $dbContext;
        global $db_update;
        $id_product = $item['id'];
        if ($dbContext == null) {
            $this->error($this->create_error('can_not_connect_database', 'Không thể kết nối mysql. Vui lòng cấu hình và khởi động lại web'));
        } else {
            $id_product = $db_update->update_product($dbContext, $id_product, $item);
            $this->success(array('product' => array('id' => $id_product)));
        }
    }

    function update_variant($item){
        global $dbContext;
        global $db_update;
        $id_variant= $item['id'];
        if ($dbContext == null) {
            $this->error($this->create_error('can_not_connect_database', 'Không thể kết nối mysql. Vui lòng cấu hình và khởi động lại web'));
        } else {
            $id_variant = $db_update->update_variant($dbContext, $id_variant, $item);
            $this->success(array('variation' => array('id' => $id_variant)));
        }
    }

    function update_product_with_variation($item){
        global $dbContext;
        global $db_update;
        $id_product = $item['id'];
        if ($dbContext == null) {
            $this->error($this->create_error('can_not_connect_database', 'Không thể kết nối mysql. Vui lòng cấu hình và khởi động lại web'));
        } else {
            $id_product = $db_update->update_product($dbContext, $id_product, $item);
        }

        if (isset($item['variantions'])) {
            $variants = $item['variantions'];
            foreach ($variants as $variant) {
                $id_variant = $db_update->update_variant($dbContext, $variant['id_variant'], $variant);
            }
        }

        $this->success(array('product' => array('id' => $id_product)));
    }

    function update_product_by_sku($item){
        global $dbContext;
        if ($dbContext == null) {
            $this->error($this->create_error('can_not_connect_database', 'Không thể kết nối mysql. Vui lòng cấu hình và khởi động lại web'));
        } else {
            if(isset($item['sku'])){
                $sku = $item['sku'];
                $sql = "SELECT get_productid_from_sku('$sku') AS id_product;";
                // $sql = "SELECT 1 AS id_product;";
                // die($sql);
                if($result = @$dbContext->query($sql)) {
                    $item['id'] = @$result->fetch_object()->id_product;
                    $result->free_result();
                    if(isset($item['id']) && $item['id'] && $item['id'] != '')
                        $this->update_product($item);
                    else
                        $this->error($this->create_error('update_product_by_sku_not_found_sku', "Không tìm thấy sku $sku"));
                } else {
                    $this->error($this->create_error('update_product_by_sku_error_select_sku', $sql));
                }
            }else{
                $this->error($this->create_error('update_product_by_sku_not_found_sku', 'Chưa có SKU'));
            }
        }
    }
    function update_status($item){
        if(!isset($item['id']))
            $this->error($this->create_error('missing_data', 'Chưa có id'));
        else if(!isset($item['status'])){
            $this->error($this->create_error('missing_data', 'Chưa có status'));
        }else {
            $id = $item['id'];
            $status = isset($item['status']) ? $item['status'] : null;// future draft publish
            if($status != null && !in_array($status, ["future", "draft", "publish"]))
                $this->error($this->create_error('wrong_status', 'Status chỉ nhận các giá trị: future, draft, publish'));
                
            $date_publish = isset($item['date_publish']) ? $item['date_publish'] : null;
            if($status != null || $date_publish != null){
                global $db_update;
                global $dbContext;
                $db_update->update_status($dbContext, $id, $status, $date_publish);
                // check_and_publish_future_post
            }
            $this->success(array('product' => $item));
        }
    }

    function schedule_product($item){
        if(!isset($item['id'])){
            $this->error($this->create_error('missing_data', 'Chưa có id'));
        }else if(!isset($item['date_publish_utc'])) {
            $this->error($this->create_error('missing_data', 'Chưa có date_publish_utc'));
        }else{
            $id = $item['id'];
            
            $date_publish_utc = isset($item['date_publish_utc']) ? $item['date_publish_utc'] : null;
            global $db_update;
            global $dbContext;
            require_once "../../../../../wp-config.php";
            require_once "../../../../../wp-includes/post.php";
            $db_update->schedule_product($dbContext, $id, $date_publish_utc);
            // check_and_publish_future_post($id);
            $this->success(array('product' => $item));
        }
    }
}

$request = new update();
$method = $_SERVER['REQUEST_METHOD'];
if($method == 'PUT' || $method == 'put' || $method == 'POST' || $method == 'post'){
    $json = file_get_contents('php://input');
    $item = json_decode($json, true);
    if(isset($_GET['action'])) {
        $action = $_GET['action'];
            switch($action){
                case 'update_product':
                    $request->update_product($item);
                    break;
                case 'update_product_with_variation':
                    $request->update_product_with_variation($item);
                    break;
                case 'update_variation':
                    $request->update_variant($item);
                    break;
                case 'update_product_by_sku':
                    $request->update_product_by_sku($item);
                    break;
                case 'update_status':
                    $request->update_status($item);
                    break;
                case 'schedule_product':
                    $request->schedule_product($item);
                    break;
                default:
                    $request->error($request->create_error('not_found_action', 'Not match ?action param'));
                    break;
            }
        }else{
            $request->update_product($item);
        }
    }else {
        $request->error($request->create_error( 'not_match_method', 'Accept only PUT, POST method'));
}
