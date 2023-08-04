<?php

class FifuDb {

    private $posts;
    private $postmeta;
    private $termmeta;
    private $term_taxonomy;
    private $term_relationships;
    private $query;
    private $wpdb;
    private $author;
    private $types;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->options = $wpdb->prefix . 'options';
        $this->posts = $wpdb->prefix . 'posts';
        $this->postmeta = $wpdb->prefix . 'postmeta';
        $this->termmeta = $wpdb->prefix . 'termmeta';
        $this->term_taxonomy = $wpdb->prefix . 'term_taxonomy';
        $this->term_relationships = $wpdb->prefix . 'term_relationships';
        $this->author = 77777;
        $this->MAX_INSERT = get_option('fifu_spinner_db');
        $this->MAX_URL_LENGTH = 2048;
        $this->types = $this->get_types();
    }

    function get_types() {
        $post_types = fifu_get_post_types();
        return join("','", $post_types);
    }

    /* alter table */

    function change_url_length() {
        $this->wpdb->get_results("
            ALTER TABLE " . $this->posts . "
            MODIFY COLUMN guid VARCHAR(" . $this->MAX_URL_LENGTH . ")"
        );
    }

    /* deprecated data */

    function delete_deprecated_options() {
        $this->wpdb->get_results("
            DELETE FROM " . $this->options . " 
            WHERE option_name IN ('fifu_cpt0','fifu_cpt1','fifu_cpt2','fifu_cpt3','fifu_cpt4','fifu_cpt5','fifu_cpt6','fifu_cpt7','fifu_cpt8','fifu_cpt9','fifu_data_generation','fifu_debug_mode','fifu_fake2','fifu_priority','fifu_update_all_id','fifu_update_all_status','fifu_update_all_timestamp','fifu_update_number','fifu_wc_theme','fifu_max_url','fifu_variation_attach_id_0','fifu_variation_attach_id_1','fifu_variation_attach_id_2','fifu_variation_attach_id_3','fifu_variation_attach_id_4','fifu_variation_attach_id_5','fifu_variation_attach_id_6','fifu_variation_attach_id_7','fifu_variation_attach_id_8','fifu_variation_attach_id_9','fifu_default_width')"
        );
    }

    /* autoload no */

    function update_autoload() {
        $this->wpdb->get_results("
            UPDATE " . $this->options . " 
            SET autoload = 'no'
            WHERE option_name IN ('fifu_auto_category_created','fifu_column_height','fifu_data_clean','fifu_fake','fifu_fake_created','fifu_key','fifu_spinner_db','fifu_update_all','fifu_update_ignore')"
        );
    }

    /* attachment metadata */

    // insert 1 _wp_attached_file for each attachment
    function insert_attachment_meta_url($ids) {
        $this->wpdb->get_results("
            INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (
                SELECT p.id, '_wp_attached_file', CONCAT(';', p.guid)
                FROM " . $this->posts . " p
                WHERE p.post_parent IN (" . $ids . ")
                AND p.post_type = 'attachment' 
                AND p.post_author = " . $this->author . " 
                AND NOT EXISTS (
                    SELECT 1 
                    FROM " . $this->postmeta . "
                    WHERE post_id = id 
                    AND meta_key = '_wp_attached_file'
                )
            )"
        );

        // giaiphapmmo start: Thêm cái này để khi bấm vào ảnh ở media mới hiện
        $this->wpdb->get_results("
        INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (
            SELECT p.id, '_wp_attachment_metadata', 'a:1:{i:0;s:15:\"giaiphapmmo.net\";}'
            FROM " . $this->posts . " p
            WHERE p.post_parent IN (" . $ids . ")
            AND p.post_type = 'attachment' 
            AND p.post_author = " . $this->author . " 
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->postmeta . "
                WHERE post_id = id 
                AND meta_key = '_wp_attachment_metadata'
            )
        )"
        );
        // giaiphapmmo end
    }

    // delete 1 _wp_attached_file or _wp_attachment_image_alt for each attachment
    function delete_attachment_meta($ids) {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . "
            WHERE meta_key IN ('_wp_attached_file', '_wp_attachment_image_alt')
            AND EXISTS (
                SELECT 1 
                FROM " . $this->posts . " p
                WHERE p.id = post_id
                AND p.post_parent IN (" . $ids . ")
                AND p.post_type = 'attachment' 
                AND p.post_author = " . $this->author . " 
            )"
        );
    }

    // insert 1 _wp_attachment_image_alt for each attachment
    function insert_attachment_meta_alt($ids) {
        $this->wpdb->get_results("
            INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (
                SELECT p.id, '_wp_attachment_image_alt', p.post_title 
                FROM " . $this->posts . " p 
                WHERE p.post_parent IN (" . $ids . ") 
                AND p.post_type = 'attachment' 
                AND p.post_author = " . $this->author . " 
                AND NOT EXISTS (
                    SELECT 1 
                    FROM " . $this->postmeta . " 
                    WHERE post_id = id 
                    AND meta_key = '_wp_attachment_image_alt'
                )
            )"
        );
    }

    // insert 1 _thumbnail_id for each attachment (posts)
    function insert_thumbnail_id($ids) {
        $this->wpdb->get_results("
            INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (
                SELECT p.post_parent, '_thumbnail_id', p.id 
                FROM " . $this->posts . " p 
                WHERE p.post_parent IN (" . $ids . ") 
                AND p.post_type = 'attachment' 
                AND p.post_author = " . $this->author . " 
                AND NOT EXISTS (
                    SELECT 1 
                    FROM " . $this->postmeta . " 
                    WHERE post_id = p.post_parent 
                    AND meta_key = '_thumbnail_id'
                )
            )"
        );
    }

    // has attachment created bu FIFU
    function is_fifu_attachment($att_id) {
        return $this->wpdb->get_row("
            SELECT 1 
            FROM " . $this->posts . " 
            WHERE id = " . $att_id . " 
            AND post_author = " . $this->author
                ) != null;
    }

    // get ids from categories with external media and no thumbnail_id
    function get_categories_without_meta() {
        return $this->wpdb->get_results("
            SELECT DISTINCT term_id
            FROM " . $this->termmeta . " a
            WHERE a.meta_key IN ('fifu_image_url', 'fifu_video_url', 'fifu_slider_image_url_0', 'fifu_shortcode')
            AND a.meta_value IS NOT NULL 
            AND a.meta_value <> ''
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->termmeta . " b 
                WHERE a.term_id = b.term_id 
                AND b.meta_key = 'thumbnail_id'
                AND b.meta_value <> 0
            )"
        );
    }

    // get ids from posts with external media and no _thumbnail_id
    function get_posts_without_meta() {
        return $this->wpdb->get_results("
            SELECT DISTINCT post_id
            FROM " . $this->postmeta . " a
            WHERE a.meta_key IN ('fifu_image_url', 'fifu_video_url', 'fifu_slider_image_url_0', 'fifu_shortcode')
            AND a.meta_value IS NOT NULL 
            AND a.meta_value <> ''
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->postmeta . " b 
                WHERE a.post_id = b.post_id 
                AND b.meta_key = '_thumbnail_id'
            )"
        );
    }

    // get thumbnail_id from category
    function get_category_thumbnail_id($term_id) {
        return $this->wpdb->get_row("
            SELECT meta_value 
            FROM " . $this->termmeta . " 
            WHERE term_id = " . $term_id . " 
            AND meta_key = 'thumbnail_id'"
        );
    }

    // get ids from posts with external url
    function get_posts_with_url() {
        return $this->wpdb->get_results("
            SELECT post_id 
            FROM " . $this->postmeta . " 
            WHERE meta_key = 'fifu_image_url'"
        );
    }

    // get ids from terms with external url
    function get_terms_with_url() {
        return $this->wpdb->get_results("
            SELECT term_id 
            FROM " . $this->termmeta . " 
            WHERE meta_key = 'fifu_image_url'
            AND meta_value <> ''
            AND meta_value IS NOT NULL"
        );
    }

    // get ids from posts with external gallery
    function get_posts_with_external_gallery() {
        return $this->wpdb->get_results("
            SELECT DISTINCT post_id 
            FROM " . $this->postmeta . " a 
            WHERE (
                a.meta_key LIKE 'fifu_image_url_%'
                OR a.meta_key LIKE 'fifu_video_url_%'
                OR a.meta_key LIKE 'fifu_slider_image_url_%'
            )
            AND a.meta_value IS NOT NULL 
            AND a.meta_value <> ''"
        );
    }

    // get urls from external gallery
    function get_gallery_urls($post_id) {
        return $this->wpdb->get_results("
            SELECT meta_value
            FROM " . $this->postmeta . " a
            WHERE a.post_id = " . $post_id . "
            AND (
                a.meta_key LIKE 'fifu_image_url_%'
                OR a.meta_key LIKE 'fifu_video_url_%'
                OR (
                    a.meta_key LIKE 'fifu_slider_image_url_%'
                    AND a.meta_key <> 'fifu_slider_image_url_0' 
                )
            )
            ORDER BY meta_key"
        );
    }

    // delete 1 _product_image_gallery for each post
    function delete_product_image_gallery_by($ids) {
        return $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE post_id IN (" . $ids . ")
            AND meta_key = '_product_image_gallery'"
        );
    }

    function find_id_attachment_deleted($attach_ids) {
        $query = "
            SELECT ID as ID
            FROM " . $this->posts . " p
            WHERE p.ID IN (" . $attach_ids . ")";
        $temp_result_query = $this->wpdb->get_results($query);
        $currentIds = array_column($temp_result_query, 'ID');
        $res = [];
        $old_ids = explode(",", $attach_ids);
        foreach($old_ids as $old_id) {
            if(!in_array($old_id, $currentIds))
                $res[] = intval($old_id);
        }
        return $res;
    }

    function get_url_of_id_acttachment($attach_ids) {
        // $query = "
        //     SELECT ID as ID
        //     FROM " . $this->posts . " p
        //     WHERE p.ID IN (" . $attach_ids . ")";
        $query = "SELECT ID as ID, m.meta_value as URL"
            . " FROM " . $this->posts . " p INNER JOIN " . $this->postmeta. " m ON p.ID = m.post_id"
            . " WHERE p.ID IN (" . $attach_ids . ") AND m.meta_key = '_wp_attached_file'";// AND (m.meta_value like ';http%' OR m.meta_value like 'http%')";
        $temp_result_query = $this->wpdb->get_results($query);
        $currentIds = [];
        foreach(json_decode(json_encode($temp_result_query)) as $t){
            $currentIds[] = array(
                "ID" => intval($t->ID),
                "URL" => $t->URL
            );
        }
        return $currentIds;
    }


    // insert 1 _product_image_gallery for each post
    function insert_product_image_gallery($ids, $arr_att_ids = null, $old_attment_with_urls = null) {
        if($arr_att_ids == null || $arr_att_ids == "") {
            $this->wpdb->get_results("
                INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (
                    SELECT post_parent, '_product_image_gallery', GROUP_CONCAT(id)
                    FROM " . $this->posts . " p
                    WHERE p.post_parent IN (" . $ids . ")
                    AND p.id NOT IN (
                        SELECT pm.meta_value
                        FROM " . $this->postmeta . " pm
                        WHERE pm.post_id = p.post_parent
                        AND pm.meta_key = '_thumbnail_id'
                    )
                    AND p.post_type = 'attachment'
                    AND p.post_author = " . $this->author . "
                    GROUP BY post_parent
                )"
            );
        } else {
            // giaiphapmmo viết mới đoạn xử lý này để tạo đúng các ảnh được chọn
            $lstIdProduct = [];
            if(is_array($ids)){
                foreach($ids as $id){
                    $lstIdProduct[] = $id;
                }
            } else{
                $lstIdProduct[] = $ids;
            }
            foreach($lstIdProduct as $id){
                // Lấy danh sách ảnh con của post
                $query = "
                    SELECT ID as ID
                    FROM " . $this->posts . " p
                    WHERE p.post_parent IN (" . $ids . ")
                    AND p.id NOT IN (
                        SELECT pm.meta_value
                        FROM " . $this->postmeta . " pm
                        WHERE pm.post_id = p.post_parent
                        AND pm.meta_key = '_thumbnail_id'
                    )
                    AND p.post_type = 'attachment'
                    AND (p.post_author = " . $this->author . " OR p.ID in (" . $arr_att_ids . ") )";
                $temp = $this->wpdb->get_results($query);
                $tempIds = array_column($temp, 'ID');

                // Lấy ảnh cũ chưa bị xóa
                $query = "
                    SELECT ID as ID
                    FROM " . $this->posts . " p
                    WHERE p.ID IN (" . $arr_att_ids . ")";
                $temp = $this->wpdb->get_results($query);
                $tempIds2 = array_column($temp, 'ID');

                // Gộp 2 danh sách ảnh lại
                foreach($tempIds2 as $t){
                    if(!in_array($t, $tempIds)){
                        $tempIds[] = $t;
                    }
                }

                // Tìm url của id mới => so khớp với url id cũ để biết thứ tự chính xác
                $new_attment_with_urls = $this->get_url_of_id_acttachment(join(",",$tempIds));
                // file_put_contents("test.txt", json_encode($old_attment_with_urls));
                $arr_att_ids_replace_new_id = $arr_att_ids;
                foreach($new_attment_with_urls as $new_id_with_url){
                    foreach($old_attment_with_urls as $old_id_with_url){
                        if(isset($new_id_with_url["URL"]) && isset($old_id_with_url["URL"]) && isset($new_id_with_url["ID"]) && isset($old_id_with_url["ID"])
                                && (str_contains($new_id_with_url["URL"], "http://") || str_contains($new_id_with_url["URL"], "https://"))
                                && $new_id_with_url["URL"] == $old_id_with_url["URL"]){ // Chỉ xét của FIFU
                                    $arr_att_ids_replace_new_id = str_replace($old_id_with_url["ID"], $new_id_with_url["ID"], $arr_att_ids_replace_new_id);
                            }
                        }
                }
                // Sắp xếp lại danh sách id ảnh
                $att_ids_ordered = [];
                $old_ids = explode(",", $arr_att_ids_replace_new_id);
                for($i = 0; $i < count($tempIds); $i++){
                    $temp_old_id = $old_ids[$i];
                    $temp_new_id = $tempIds[$i];
                    // Nếu ở vị trí thứ i mà đã có sẵn ở danh sách cũ => giữ nguyên vị trí
                    if(count($old_ids) > $i && in_array($temp_old_id, $tempIds))
                        $att_ids_ordered[] = $temp_old_id;
                    else if (!in_array($temp_new_id, $att_ids_ordered))
                        $att_ids_ordered[] = $temp_new_id;
                }
                // Vét nốt những cái mới còn chưa có (cho vào cuối) không biết sắp xếp vậy có đúng không
                for($i = 0; $i < count($tempIds); $i++){
                    $temp_new_id = $tempIds[$i];
                    if (!in_array($temp_new_id, $att_ids_ordered))
                        $att_ids_ordered[] = $temp_new_id;
                }

                $query = "INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) VALUES (" . $id . ", '_product_image_gallery', '" . join(",",$att_ids_ordered) . "')";
                $this->wpdb->get_results($query);
                // echo(join(",",$old_ids) . "<br/>");
                // echo(join(",",$tempIds) . "<br/>");
                // die(join(",",$att_ids_ordered));
                // die($query);
            }
        }
    }

    // get ids from fake attachments
    function get_fake_attachments() {
        return $this->wpdb->get_results("
            SELECT id 
            FROM " . $this->posts . " 
            WHERE post_type = 'attachment' 
            AND post_author = " . $this->author
        );
    }

    // get ids from attachments with gallery
    function get_attachments_with_gallery() {
        return $this->wpdb->get_results("
            SELECT a.post_id 
            FROM " . $this->postmeta . " a 
            WHERE a.meta_key = '_product_image_gallery' 
            AND EXISTS (
                SELECT 1 
                FROM " . $this->postmeta . " b 
                WHERE a.post_id = b.post_id 
                AND b.meta_key LIKE 'fifu_image_url_%'
            )"
        );
    }

    // auto set category image
    function insert_category_images_auto() {
        $this->wpdb->get_results("
            INSERT INTO " . $this->termmeta . " (term_id, meta_key, meta_value) (
                SELECT tm.term_id, 'fifu_image_url', pm.meta_value
                FROM (SELECT DISTINCT term_id FROM " . $this->termmeta . ") tm
                INNER JOIN " . $this->term_taxonomy . " tt ON tm.term_id = tt.term_id AND tt.taxonomy = 'product_cat' AND count > 0 
                INNER JOIN (SELECT term_taxonomy_id, MAX(object_id) AS object_id FROM " . $this->term_relationships . " GROUP BY term_taxonomy_id) rs ON tt.term_taxonomy_id = rs.term_taxonomy_id
                INNER JOIN " . $this->postmeta . " pm ON pm.post_id = rs.object_id and pm.meta_key = 'fifu_image_url' AND pm.meta_value <> ''
                INNER JOIN " . $this->posts . " p ON (p.id = pm.post_id AND p.post_status = 'publish')
                WHERE NOT EXISTS (SELECT 1 FROM " . $this->termmeta . " tm2 WHERE tm2.meta_key = 'fifu_image_url' AND tm2.term_id = tm.term_id)
            )"
        );
    }

    // get category id given post_id
    function get_category_id($post_id) {
        return $this->wpdb->get_results("
            SELECT tm.term_id
            FROM " . $this->termmeta . " tm
            INNER JOIN " . $this->term_taxonomy . " tt ON tm.term_id = tt.term_id
            INNER JOIN " . $this->term_relationships . " rs ON tt.term_taxonomy_id = rs.term_taxonomy_id
            INNER JOIN " . $this->postmeta . " pm ON pm.post_id = rs.object_id
            WHERE pm.post_id = " . $post_id . "
            AND pm.meta_key = 'fifu_image_url'
            AND pm.meta_key = tm.meta_key
            AND pm.meta_value = tm.meta_value
            AND pm.meta_value <> ''
            AND tt.taxonomy = 'product_cat'"
        );
    }

    function get_child_category() {
        return $this->wpdb->get_results("
            SELECT DISTINCT tt.term_id, tt.parent, tt.count
            FROM " . $this->term_taxonomy . " tt
            INNER JOIN " . $this->termmeta . " tm ON tm.term_id = tt.term_id
            WHERE parent <> 0
            AND taxonomy = 'product_cat'
            ORDER BY count DESC"
        );
    }

    function exists_child_with_attachment($term_id, $parent) {
        return $this->wpdb->get_results("
            SELECT 1 
            FROM " . $this->termmeta . "
            WHERE term_id = " . $term_id . "
            AND meta_key = 'thumbnail_id'
            AND meta_value <> 0
            AND NOT EXISTS (
	            SELECT 1 
                FROM " . $this->termmeta . " tm2
                WHERE tm2.term_id = $parent
                AND tm2.meta_key = 'thumbnail_id'
                AND tm2.meta_value <> 0
            )"
                ) != null;
    }

    function delete_duplicated_category_url() {
        return $this->wpdb->get_results("
            DELETE FROM " . $this->termmeta . "
            WHERE meta_key = 'fifu_image_url'
            AND meta_id NOT IN (
                SELECT * FROM (
                    SELECT MAX(tm.meta_id) AS meta_id
                    FROM " . $this->termmeta . " tm
                    WHERE tm.meta_key = 'fifu_image_url'
                    GROUP BY tm.term_id
                ) aux
            )"
        );
    }

    // get _product_image_gallery from product variation
    function get_variation_gallery($product_id, $attributes) {
        $sql = '';
        foreach ($attributes as $attr) {
            $aux = explode("=", $attr);
            if (!$aux[1])
                continue;
            $sql = $sql . "
                AND EXISTS (
                    SELECT 1
                    FROM " . $this->postmeta . "
                    WHERE p.id = post_id
                    AND meta_key = '" . $aux[0] . "'
                    AND meta_value = '" . urldecode($aux[1]) . "'
                ) ";
        }
        $result = $this->wpdb->get_results("
            SELECT GROUP_CONCAT(pm.meta_value) AS ids
            FROM " . $this->posts . " p
            INNER JOIN " . $this->postmeta . " pm ON p.ID = pm.post_id
            WHERE p.post_parent = " . $product_id . "
            AND p.post_type = 'product_variation'
            AND pm.meta_key = '_product_image_gallery' " .
                $sql . "
            GROUP BY pm.post_id"
        );
        return count($result) > 0 ? $result[0]->ids : null;
    }

    // get post types without url
    function get_post_types_without_url() {
        return $this->wpdb->get_results("
            SELECT *
            FROM " . $this->posts . " p
            WHERE p.post_type IN ('$this->types')
            AND post_status NOT IN ('auto-draft', 'trash')
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->postmeta . " pm 
                WHERE p.id = pm.post_id 
                AND pm.meta_key IN ('fifu_image_url', 'fifu_video_url')
            )
            ORDER BY p.ID"
        );
    }

    // get all post types
    function get_all_post_types() {
        return $this->wpdb->get_results("
            SELECT *
            FROM " . $this->posts . " p
            WHERE p.post_type IN ('" . $this->types . "')
            AND post_status NOT IN ('auto-draft', 'trash')
            ORDER BY p.ID"
        );
    }

    // get posts without dimensions
    function get_posts_without_dimensions() {
        return $this->wpdb->get_results("
            SELECT p.*
            FROM " . $this->posts . " p
            INNER JOIN " . $this->posts . " parent ON p.post_parent = parent.id AND parent.post_status NOT IN ('auto-draft', 'trash')
            WHERE p.post_type = 'attachment' 
            AND p.post_author = " . $this->author . "
            AND p.post_status NOT IN ('auto-draft', 'trash')
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->postmeta . " pm 
                WHERE p.id = pm.post_id 
                AND pm.meta_key IN ('fifu_image_dimension')
            )
            ORDER BY p.id DESC"
        );
    }

    // count images without dimensions
    function get_count_posts_without_dimensions() {
        return $this->wpdb->get_results("
            SELECT COUNT(1) AS amount
            FROM " . $this->posts . " p
            INNER JOIN " . $this->posts . " parent ON p.post_parent = parent.id AND parent.post_status NOT IN ('auto-draft', 'trash')
            WHERE p.post_type = 'attachment' 
            AND p.post_author = " . $this->author . "
            AND p.post_status NOT IN ('auto-draft', 'trash')
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->postmeta . " pm 
                WHERE p.id = pm.post_id 
                AND pm.meta_key IN ('fifu_image_dimension')
            )
            ORDER BY p.id DESC"
        );
    }

    // get attachments without post
    function get_attachments_without_post($post_id) {
        $result = $this->wpdb->get_results("
            SELECT GROUP_CONCAT(id) AS ids 
            FROM " . $this->posts . " 
            WHERE post_parent = " . $post_id . " 
            AND post_type = 'attachment' 
            AND post_author = " . $this->author . "
            AND NOT EXISTS (
	            SELECT 1
                FROM " . $this->postmeta . "
                WHERE post_id = post_parent
                AND meta_key = '_thumbnail_id'
                AND meta_value = id
            )
            GROUP BY post_parent"
        );
        return $result ? $result[0]->ids : null;
    }

    function get_ctgr_attachments_without_post($term_id) {
        $result = $this->wpdb->get_results("
            SELECT GROUP_CONCAT(id) AS ids 
            FROM " . $this->posts . " 
            WHERE post_parent = " . $term_id . " 
            AND post_type = 'attachment' 
            AND post_author = " . $this->author . "
            AND NOT EXISTS (
	            SELECT 1
                FROM " . $this->termmeta . "
                WHERE term_id = post_parent
                AND meta_key = 'thumbnail_id'
                AND meta_value = id
            )
            GROUP BY post_parent"
        );
        return $result ? $result[0]->ids : null;
    }

    function get_posts_without_featured_image() {
        return $this->wpdb->get_results("
            SELECT id
            FROM " . $this->posts . " 
            WHERE post_type IN ('$this->types')
            AND post_status = 'publish'
            AND NOT EXISTS (
                SELECT 1
                FROM " . $this->postmeta . " 
                WHERE post_id = id
                AND meta_key IN ('_thumbnail_id', 'fifu_image_url', 'fifu_video_url', 'fifu_slider_image_url_0', 'fifu_shortcode')
            )"
        );
    }

    function get_number_of_posts() {
        return $this->wpdb->get_row("
            SELECT count(1) AS n
            FROM " . $this->posts . " 
            WHERE post_type IN ('$this->types')
            AND post_status = 'publish'"
                )->n;
    }

    function get_category_image_url($term_id) {
        return $this->wpdb->get_results("
            SELECT meta_value 
            FROM " . $this->termmeta . " 
            WHERE meta_key = 'fifu_image_url' 
            AND term_id = " . $term_id
        );
    }

    function get_featured_and_gallery_ids($post_id) {
        return $this->wpdb->get_results("
            SELECT GROUP_CONCAT(meta_value SEPARATOR ',') as 'ids'
            FROM " . $this->postmeta . "
            WHERE post_id = " . $post_id . "
            AND meta_key IN ('_thumbnail_id', '_product_image_gallery')"
        );
    }

    function get_featured_and_gallery_urls($post_id) {
        return $this->wpdb->get_results("
            SELECT GROUP_CONCAT(meta_value SEPARATOR '|') as 'urls'
            FROM " . $this->postmeta . "
            WHERE post_id = " . $post_id . "
            AND meta_key LIKE 'fifu_image_url%'
            ORDER BY meta_key"
        );
    }

    function delete_featured_and_gallery_urls($post_id) {
        return $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . "
            WHERE post_id = " . $post_id . "
            AND meta_key LIKE 'fifu_image_url%'"
        );
    }

    function get_variantion_products($post_id) {
        return $this->wpdb->get_results("
            SELECT id, post_title 
            FROM " . $this->posts . "
            WHERE post_parent = " . $post_id . "
            AND post_type = 'product_variation'
            AND post_status <> 'trash'
            ORDER BY menu_order"
        );
    }

    function insert_default_thumbnail_id($value) {
        $this->wpdb->get_results("
            INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value)
            VALUES " . $value
        );
    }

    // update post_content

    function update_post_content($id, $post_content) {
        $this->wpdb->update($this->posts, array('post_content' => $post_content), array('id' => $id), null, null);
    }

    function update_post_content_arr($arr_post) {
        $query = "
            INSERT INTO " . $this->posts . " (id, post_content) VALUES ";
        $count = 0;
        foreach ($arr_post as $post) {
            if ($count++ != 0)
                $query .= ", ";
            $query .= "(" . $post["id"] . ",'" . addslashes($post["content"]) . "') ";
        }
        $query .= "ON DUPLICATE KEY UPDATE post_content=VALUES(post_content)";
        return $this->wpdb->get_results($query);
    }

    // clean metadata

    function delete_thumbnail_ids($ids) {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key = '_thumbnail_id' 
            AND meta_value IN (" . $ids . ")"
        );
    }

    function delete_thumbnail_ids_category($ids) {
        $this->wpdb->get_results("
            DELETE FROM " . $this->termmeta . " 
            WHERE meta_key = 'thumbnail_id' 
            AND term_id IN (" . $ids . ")"
        );
    }

    function delete_image_url_category($term_id) {
        $this->wpdb->get_results("
            DELETE FROM " . $this->termmeta . " 
            WHERE term_id = " . $term_id . " 
            AND meta_key = 'fifu_image_url'"
        );
    }

    function delete_thumbnail_ids_category_without_attachment() {
        $this->wpdb->get_results("
            DELETE FROM " . $this->termmeta . " 
            WHERE meta_key = 'thumbnail_id' 
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->posts . " p 
                WHERE p.id = meta_value
            )"
        );
    }

    function delete_invalid_thumbnail_ids($ids) {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key = '_thumbnail_id' 
            AND post_id IN (" . $ids . ") 
            AND (
                meta_value = -1 
                OR meta_value IS NULL 
                OR meta_value LIKE 'fifu:%'
            )"
        );
    }

    function delete_fake_thumbnail_id($ids) {
        $att_id = get_option('fifu_fake_attach_id');
        if ($att_id) {
            $this->wpdb->get_results("
                DELETE FROM " . $this->postmeta . "
                WHERE meta_key = '_thumbnail_id' 
                AND post_id IN (" . $ids . ") 
                AND meta_value = " . $att_id
            );
        }
    }

    function delete_attachments($ids, $post_id=null) {

        $this->wpdb->get_results("
            DELETE FROM " . $this->posts . "
            WHERE id IN (" . $ids . ")
            AND post_type = 'attachment'
            AND post_author = " . $this->author
        );
        // giaiphapmmo thêm vào $post_id để xóa ảnh trong gallery
        if($post_id != null) {
            $this->wpdb->get_results("
                DELETE FROM " . $this->posts . "
                WHERE post_type = 'attachment'
                AND post_parent = " . $post_id . " AND post_author = " . $this->author
                . " AND ID NOT IN (SELECT meta_value FROM " .  $this->postmeta . " WHERE post_id = " . $post_id . " AND meta_key = '_thumbnail_id')"
            );
        }
    }

    function delete_attachment_meta_url_and_alt($ids) {
        // giaiphapmmo re-write
        // $this->wpdb->get_results("
        //     DELETE FROM " . $this->postmeta . "
        //     WHERE meta_key IN ('_wp_attached_file','_wp_attachment_image_alt')
        //     AND post_id IN (" . $ids . ")"
        // );
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . "
            WHERE (meta_key = '_wp_attached_file' AND (meta_value like ';http%' OR meta_value like 'http%')) or meta_key = '_wp_attachment_image_alt')
            AND post_id IN (" . $ids . ")"
        );
    }

    function delete_attachment_meta_url($ids) {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key = '_wp_attached_file'
            AND post_id IN (" . $ids . ") /* giaiphapmmo start */ AND (meta_value like ';http%' OR meta_value like 'http%') /* giaiphapmmo end */"
        );
    }

    function delete_thumbnail_id_without_attachment() {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key = '_thumbnail_id' 
            AND NOT EXISTS (
                SELECT 1 
                FROM " . $this->posts . " p 
                WHERE p.id = meta_value
            )"
        );
    }

    function delete_attachment_meta_without_attachment() {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key IN ('_wp_attached_file', '_wp_attachment_image_alt')
            AND NOT EXISTS (
                SELECT 1
                FROM " . $this->posts . " p 
                WHERE p.id = post_id
            )"
        );
    }

    function delete_product_image_gallery($ids) {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . "
            WHERE meta_key = '_product_image_gallery'
            AND post_id IN (" . $ids . ")"
        );
    }

    function delete_empty_urls_category() {
        $this->wpdb->get_results("
            DELETE FROM " . $this->termmeta . " 
            WHERE meta_key = 'fifu_image_url'
            AND (
                meta_value = ''
                OR meta_value is NULL
            )"
        );
    }

    function delete_empty_urls() {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key = 'fifu_image_url'
            AND (
                meta_value = ''
                OR meta_value is NULL
            )"
        );
    }

    function delete_metadata() {
        $fake_attach_id = get_option('fifu_fake_attach_id');
        $default_attach_id = get_option('fifu_default_attach_id');
        $value = '-1';
        $value = $fake_attach_id ? $value . ',' . $fake_attach_id : $value;
        $value = $default_attach_id ? $value . ',' . $default_attach_id : $value;
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key IN ('_thumbnail_id', '_product_image_gallery')
            AND meta_value IN (" . $value . ")"
        );
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key = '_wp_attached_file'
            AND meta_value IN ('Show Image Link', 'fifu.png')"
        );
        $this->wpdb->get_results("
            DELETE FROM " . $this->posts . " 
            WHERE guid = 'http://fifu.png'"
        );
    }

    /* speed up */

    function get_all_urls() {
        return $this->wpdb->get_results("
            SELECT pm.meta_id, pm.post_id, pm.meta_value AS url, p.post_name, p.post_title, p.post_date
            FROM " . $this->postmeta . " pm
            INNER JOIN " . $this->posts . " p ON pm.post_id = p.id
            WHERE pm.meta_key LIKE 'fifu_image_url%' 
            AND pm.meta_value NOT LIKE 'https://storage.googleapis.com/fifu-%'
            AND p.post_status <> 'trash'
            ORDER BY pm.post_id DESC"
        );
    }

    /* insert attachment */

    function insert_attachment_by($value) {
        $this->wpdb->get_results("
            INSERT INTO " . $this->posts . " (post_author, guid, post_title, post_mime_type, post_type, post_status, post_parent, post_date, post_date_gmt, post_modified, post_modified_gmt, post_content, post_excerpt, to_ping, pinged, post_content_filtered)
            VALUES " . $value);
    }

    function get_formatted_value($url, $alt, $post_parent) {
        return "(" . $this->author . ", '" . $url . "', '" . str_replace("'", "", $alt) . "', 'image/jpeg', 'attachment', 'inherit', '" . $post_parent . "', now(), now(), now(), now(), '', '', '', '', '')";
    }

    /* product variation */

    function get_product_image_gallery($post_id) {
        return rtrim(get_post_meta($post_id, '_product_image_gallery', true), ',');
    }

    function get_thumbnail_id($post_id) {
        $result = $this->wpdb->get_results("SELECT MIN(id) AS id FROM " . $this->posts . " WHERE post_parent = " . $post_id . " AND post_type = 'attachment'");
        return $result ? $result[0]->id : null;
    }

    function get_attachments($post_id) {
        $ids = null;
        $i = 1;
        $result = $this->wpdb->get_results("SELECT id FROM " . $this->posts . " WHERE post_parent = " . $post_id . " AND post_type = 'attachment'");
        foreach ($result as $res)
            $ids = ($i++ == 1) ? $res->id : ($ids . "," . $res->id);
        return $ids;
    }

    function insert_attachment_list($post_id, $urls, $alts) {
        $value = null;
        $value_meta_url = null;
        $value_meta_alt = null;
        $i = 0;
        $query_meta = "INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) VALUES ";
        foreach ($urls as $url) {
            $alt = ($alts && count($alts) > $i) ? $alts[$i] : '';

            $aux = $this->get_formatted_value($url, $alt, $post_id);
            $value = ($i == 0) ? $aux : ($value . "," . $aux);

            $aux = "(" . $post_id . ", 'fifu_image_url" . ($i == 0 ? "" : "_" . ($i - 1)) . "', '" . $url . "')";
            $value_meta_url = ($i == 0) ? $aux : ($value_meta_url . "," . $aux);

            $aux = "(" . $post_id . ", 'fifu_image_alt" . ($i == 0 ? "" : "_" . ($i - 1)) . "', '" . $alt . "')";
            $value_meta_alt = ($i == 0) ? $aux : ($value_meta_alt . "," . $aux);

            $i++;
        }
        if (!$value)
            return;
        $this->insert_attachment_by($value);
        $this->wpdb->get_results($query_meta . " " . $value_meta_url);
        $this->wpdb->get_results($query_meta . " " . $value_meta_alt);
        $thumbnail_id = $this->get_thumbnail_id($post_id);
        $this->wpdb->get_results("INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) VALUES (" . $post_id . ", '_thumbnail_id', " . $thumbnail_id . ")");
        $this->wpdb->get_results("INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (SELECT id, '_wp_attached_file', CONCAT(';', guid) FROM " . $this->posts . " WHERE post_parent = " . $post_id . ")");
        $this->wpdb->get_results("INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (SELECT id, '_wp_attachment_image_alt', post_title FROM " . $this->posts . " WHERE post_parent = " . $post_id . ")");
        delete_post_meta($post_id, '_product_image_gallery');
        $this->wpdb->get_results("INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) (SELECT post_parent, '_product_image_gallery', group_concat(id) FROM " . $this->posts . " WHERE post_parent = " . $post_id . " AND id <> " . $thumbnail_id . " AND post_type = 'attachment' GROUP BY post_parent)");
    }

    function update_attachment_list($post_id, $urls, $alts) {
        $attachments = $this->get_attachments($post_id);
        if ($attachments) {
            $this->wpdb->get_results("DELETE FROM " . $this->postmeta . " WHERE post_id IN (" . $attachments . ")");
            $this->wpdb->get_results("DELETE FROM " . $this->posts . " WHERE id IN (" . $attachments . ")");
        }
        $this->wpdb->get_results("DELETE FROM " . $this->postmeta . " WHERE post_id = " . $post_id . " AND meta_key IN ('_product_image_gallery', '_thumbnail_id')");
        $this->wpdb->get_results("DELETE FROM " . $this->postmeta . " WHERE post_id = " . $post_id . " AND meta_key LIKE 'fifu%'");
        if (!empty($urls) && !empty($urls[0]))
            $this->insert_attachment_list($post_id, $urls, $alts);
    }

    function get_image_ids($meta_key, $meta_value, $product_id) {
        $result = $this->wpdb->get_results("SELECT GROUP_CONCAT(p1.meta_value) AS ids FROM " . $this->postmeta . " p1 INNER JOIN " . $this->postmeta . " p2 ON p1.post_id = p2.post_id INNER JOIN " . $this->posts . " p ON p.id = p1.post_id AND p.post_parent = " . $product_id . " AND p1.meta_key IN ('_thumbnail_id','_product_image_gallery')  AND p2.meta_key = '" . $meta_key . "' AND p2.meta_value = '" . $meta_value . "' GROUP BY p1.post_id");
        return $result ? $result[0]->ids : null;
    }

    /* auto set category image */

    function insert_auto_category_image() {
        $this->delete_empty_urls();
        $this->delete_empty_urls_category();
        $this->insert_category_images_auto();
        $this->insert_attachment_category();
        $this->insert_auto_subcategory_image();
    }

    function insert_auto_subcategory_image() {
        foreach ($this->get_child_category() as $i) {
            if ($this->exists_child_with_attachment($i->term_id, $i->parent)) {
                $att_id = get_term_meta($i->term_id, 'thumbnail_id', true);
                update_term_meta($i->parent, 'thumbnail_id', $att_id);
            }
        }
    }

    /* insert dimension */

    function insert_dimension_by($value) {
        $this->wpdb->get_results("
            INSERT INTO " . $this->postmeta . " (post_id, meta_key, meta_value) 
            VALUES " . $value);
    }

    function get_formatted_dimension_value($post_id, $dimension) {
        return "(" . $post_id . ", 'fifu_image_dimension', '" . $dimension . "')";
    }

    /* insert fake internal featured image */

    function insert_attachment_category() {
        $ids = null;
        $value = null;
        $i = 0;
        // insert 1 attachment for each selected category
        foreach ($this->get_categories_without_meta() as $res) {
            $ids = ($i++ == 0) ? $res->term_id : ($ids . "," . $res->term_id);
            $url = get_term_meta($res->term_id, 'fifu_video_url', true);
            $url = $url ? fifu_video_img_large($url) : get_term_meta($res->term_id, 'fifu_image_url', true);
            if (!$url) {
                $result = $this->get_category_image_url($res->term_id);
                $url = $result[0]->meta_value;
            }
            $value = $this->get_formatted_value($url, get_term_meta($res->term_id, 'fifu_image_alt', true), $res->term_id);
            $this->insert_attachment_by($value);
            $att_id = $this->wpdb->insert_id;
            update_term_meta($res->term_id, 'thumbnail_id', $att_id);
        }
        if ($ids) {
            $this->insert_attachment_meta_url($ids);
            $this->insert_attachment_meta_alt($ids);
        }
    }

    function insert_attachment() {
        $ids = null;
        $value = null;
        $i = 1;
        $count = 1;
        // insert 1 attachment for each selected post
        $result = $this->get_posts_without_meta();
        foreach ($result as $res) {
            $ids = ($i == 1) ? $res->post_id : ($ids . "," . $res->post_id);
            $aux = $this->get_formatted_value(fifu_main_image_url($res->post_id), get_post_meta($res->post_id, 'fifu_image_alt', true), $res->post_id);
            $value = ($i == 1) ? $aux : ($value . "," . $aux);
            if ($value && (($i % $this->MAX_INSERT == 0) || ($i % $this->MAX_INSERT != 0 && count($result) == $count))) {
                wp_cache_flush();
                $this->insert_attachment_by($value);
                $this->insert_thumbnail_id($ids);
                $this->insert_attachment_meta_url($ids);
                $this->insert_attachment_meta_alt($ids);
                $ids = null;
                $value = null;
                $i = 1;
            } else
                $i++;
            $count++;
        }
    }

    function insert_attachment_gallery() {
        $ids = null;
        $value = null;
        $i = 1;
        $j = 1;
        $count = 1;
        // insert 1 attachment for each selected url
        $result = $this->get_posts_with_external_gallery();
        foreach ($result as $res) {
            $ids = ($i == 1) ? $res->post_id : ($ids . "," . $res->post_id);
            $result2 = $this->get_gallery_urls($res->post_id);
            foreach ($result2 as $res2) {
                $url = $res2->meta_value;
                $url = fifu_is_video($url) ? fifu_video_img_large($url) : $url;
                $aux = $this->get_formatted_value($url, '', $res->post_id);
                $value = ($j == 1) ? $aux : ($value . "," . $aux);
                $j++;
            }
            if ($value && (($j >= $this->MAX_INSERT) || ($j < $this->MAX_INSERT && count($result) == $count))) {
                wp_cache_flush();
                $this->insert_attachment_by($value);
                $this->insert_thumbnail_id($ids);
                $this->delete_attachment_meta($ids);
                $this->insert_attachment_meta_url($ids);
                $this->insert_attachment_meta_alt($ids);
                $this->delete_product_image_gallery_by($ids);
                $this->insert_product_image_gallery($ids);
                $ids = null;
                $value = null;
                $i = 1;
                $j = 1;
            } else
                $i++;
            $count++;
        }
    }

    /* delete fake internal featured image */

    function delete_attachment() {
        $ids = null;
        $i = 1;
        $count = 1;
        // delete fake attachments and _thumbnail_ids
        $result = $this->get_fake_attachments();
        foreach ($result as $res) {
            $ids = ($i == 1) ? $res->id : ($ids . "," . $res->id);
            if ($ids && (($i % $this->MAX_INSERT == 0) || ($i % $this->MAX_INSERT != 0 && count($result) == $count))) {
                wp_cache_flush();
                $this->delete_thumbnail_ids($ids);
                $this->delete_attachments($ids);
                $ids = null;
                $i = 1;
            } else
                $i++;
            $count++;
        }

        $ids = null;
        $i = 1;
        $count = 1;
        // delete attachment data and more _thumbnail_ids
        $result = $this->get_posts_with_url();
        foreach ($result as $res) {
            $ids = ($i == 1) ? $res->post_id : ($ids . "," . $res->post_id);
            if ($ids && (($i % $this->MAX_INSERT == 0) || ($i % $this->MAX_INSERT != 0 && count($result) == $count))) {
                wp_cache_flush();
                $this->delete_invalid_thumbnail_ids($ids);
                $this->delete_fake_thumbnail_id($ids);
                $this->delete_attachment_meta_url($ids);
                $ids = null;
                $i = 1;
            } else
                $i++;
            $count++;
        }

        // delete data without attachment
        $this->delete_thumbnail_id_without_attachment();
        $this->delete_attachment_meta_without_attachment();

        $ids = null;
        $i = 0;
        // delete _product_image_gallery
        foreach ($this->get_attachments_with_gallery() as $res)
            $ids = ($i++ == 0) ? $res->post_id : ($ids . "," . $res->post_id);
        if ($ids)
            $this->delete_product_image_gallery($ids);

        $this->delete_empty_urls();
    }

    function delete_attachment_category() {
        $ids = null;
        $i = 0;
        foreach ($this->get_terms_with_url() as $res)
            $ids = ($i++ == 0) ? $res->term_id : ($ids . "," . $res->term_id);
        if ($ids) {
            $this->delete_thumbnail_ids_category($ids);
            $this->delete_attachment_meta($ids);
            $this->delete_thumbnail_ids_category_without_attachment();
        }
        $this->delete_empty_urls_category();
    }

    /* auto set: update all */

    function update_all() {
        $ids = null;
        $value = null;
        $i = 1;
        $count = 1;
        $arr_post = array();
        // get all posts or all posts without url
        $result = fifu_is_on('fifu_update_ignore') ? $this->get_post_types_without_url() : $this->get_all_post_types();
        foreach ($result as $res) {
            $post_id = $res->ID;

            // set featured image
            $image_url = fifu_first_url_in_content($post_id, $res->post_content, false);
            $video_url = fifu_first_url_in_content($post_id, $res->post_content, true);

            if (!$image_url && !$video_url) {
                $count++;
                continue;
            }

            if ($image_url)
                fifu_update_or_delete($post_id, 'fifu_image_url', $image_url);
            else if ($video_url)
                fifu_update_or_delete($post_id, 'fifu_video_url', $video_url);

            $url = $image_url ? $image_url : fifu_video_img_large($video_url);

            // hide/show first image
            $img = fifu_first_img_in_content($res->post_content);
            $video = fifu_first_video_in_content($res->post_content);

            $media = $img ? $img : $video;

            if (fifu_is_on('fifu_pop_first'))
                $new_content = str_replace($media, fifu_hide_media($media), $res->post_content);
            else
                $new_content = str_replace($media, fifu_show_media($media), $res->post_content);
            array_push($arr_post, ["id" => $post_id, "content" => $new_content]);

            $ids = ($i == 1) ? $post_id : ($ids . "," . $post_id);
            $aux = $this->get_formatted_value($url, fifu_is_on('fifu_auto_alt') ? $res->post_title : null, $post_id);
            $value = ($i == 1) ? $aux : ($value . "," . $aux);
            if ($value && (($i % $this->MAX_INSERT == 0) || ($i % $this->MAX_INSERT != 0 && count($result) == $count))) {
                wp_cache_flush();
                $this->update_post_content_arr($arr_post);
                $this->insert_attachment_by($value);
                $this->insert_thumbnail_id($ids);
                $this->insert_attachment_meta_url($ids);
                $this->insert_attachment_meta_alt($ids);
                $ids = null;
                $value = null;
                $i = 1;
            } else
                $i++;
            $count++;
        }
    }

    /* dimensions: save all */

    function save_dimensions_all() {
        $value = null;
        // get all posts or all posts without dimensions
        $result = $this->get_posts_without_dimensions();
        foreach ($result as $res) {
            $post_id = $res->ID;

            // set featured image
            $url = $res->guid;

            if (!$url)
                continue;

            // get dimensions
            $dimension = fifu_get_dimension_backend($url);

            if (!$dimension)
                continue;

            $value = $this->get_formatted_dimension_value($post_id, $dimension);
            if ($value) {
                $this->insert_dimension_by($value);
                $value = null;
            }
        }
    }

    /* dimensions: clean all */

    function clean_dimensions_all() {
        $this->wpdb->get_results("
            DELETE FROM " . $this->postmeta . " 
            WHERE meta_key = 'fifu_image_dimension'"
        );
    }

    /* save 1 post */

    function update_fake_attach_id($post_id) {
        $att_id = get_post_thumbnail_id($post_id);
        $url = fifu_main_image_url($post_id);
        $has_fifu_attachment = $att_id ? ($this->is_fifu_attachment($att_id) && get_option('fifu_default_attach_id') != $att_id) : false;
        // delete
        if (!$url) {
            if ($has_fifu_attachment) {
                // die('1'); // test
                // die('att_id = ' . $att_id . ', has_fifu_attachment=' . $this->is_fifu_attachment($att_id));
                wp_delete_attachment($att_id);
                delete_post_thumbnail($post_id);
                if (fifu_get_default_url())
                    set_post_thumbnail($post_id, get_option('fifu_default_attach_id'));
            } else {
                // die('2'); // test
                if (fifu_get_default_url()){
                    // die('2.1');
                    set_post_thumbnail($post_id, get_option('fifu_default_attach_id'));
                }
            }
        } else {
            // update
            $alt = get_post_meta($post_id, 'fifu_image_alt', true);
            if ($has_fifu_attachment) {
                // die('3'); // test
                update_post_meta($att_id, '_wp_attached_file', ';' . $url);
                update_post_meta($att_id, '_wp_attachment_image_alt', $alt);
                $this->wpdb->update($this->posts, $set = array('post_title' => $alt, 'guid' => $url), $where = array('id' => $att_id), null, null);
            }
            // insert
            else {
                // die('4'); // test
                $value = $this->get_formatted_value($url, $alt, $post_id);
                $this->insert_attachment_by($value);
                $att_id = $this->wpdb->insert_id;
                update_post_meta($post_id, '_thumbnail_id', $att_id);
                update_post_meta($att_id, '_wp_attached_file', ';' . $url);
                update_post_meta($att_id, '_wp_attachment_image_alt', $alt);
                update_post_meta($att_id, '_wp_attachment_metadata', 'a:1:{i:0;s:15:"giaiphapmmo.net";}'); // giaiphapmmo add
                $attachments = $this->get_attachments_without_post($post_id);
                if ($attachments) {
                    $this->delete_attachments($attachments);
                    $this->delete_attachment_meta_url_and_alt($attachments);
                }
            }
            if (fifu_is_on('fifu_save_dimensions'))
                fifu_update_or_delete_value($att_id, 'fifu_image_dimension', fifu_get_dimension_backend($url));
        }
    }
    /* save 1 gallery */
    function update_fake_attach_id_gallery($post_id) { // giaiphapmmo ham nay lam loi luu anh upload len site
        $video_enabled = fifu_is_on('fifu_video');
        $value = null;
        $i = 0;
        $attach_ids = rtrim(get_post_meta($post_id, '_product_image_gallery', true), ',');
        $old_attment_with_urls = [];
        if ($attach_ids) {
            $old_attment_with_urls = $this->get_url_of_id_acttachment($attach_ids);
            $this->delete_attachments($attach_ids, $post_id); // Xóa id cũ đi tạo lại id mới cho gallery
            $this->delete_attachment_meta_url_and_alt($attach_ids);
        }
        // die(json_encode($attach_ids));
        // FIXME: Fix bug phải tìm cả id nào không có url (id tạo bởi chế độ upload lên site)
        $urls = $this->get_gallery_urls($post_id); // => giaiphapmmo: Fix bug phải tìm cả id nào không có url (id tạo bởi chế độ upload lên site)
        // die(json_encode($urls));
        while ($i < sizeof($urls)) {
            $url = $urls[$i++]->meta_value;
            if ($video_enabled)
                $url = fifu_is_video($url) ? fifu_video_img_large($url) : $url;
            $aux = $this->get_formatted_value($url, '', $post_id);
            $value = !$value ? $aux : ($value . "," . $aux);
        }
        // die(json_encode($value));

        // TODO: _product_image_gallery chưa sắp xếp chuẩn và trong Product gallery chưa xóa được ảnh upload (lúc tạo _product_image_gallery cần contains attach_ids)
        if ($value) {
            $this->insert_attachment_by($value); // Thêm attach vào bảng posts
            $this->insert_thumbnail_id($post_id);
            $this->insert_attachment_meta_url($post_id);
            $this->insert_attachment_meta_alt($post_id);
        }
        $this->delete_product_image_gallery_by($post_id);
        $this->insert_product_image_gallery($post_id, $attach_ids, $old_attment_with_urls); // FIXME: Chưa thêm được ảnh được upload của sản phẩm khác. Khả năng cầu so sánh $attach_ids trước và sau

        // die($attach_ids);
    }

    /* save 1 category */

    function ctgr_update_fake_attach_id($term_id) {
        $att_id = get_term_meta($term_id, 'thumbnail_id');
        $att_id = $att_id ? $att_id[0] : null;
        $has_fifu_attachment = $att_id ? $this->is_fifu_attachment($att_id) : false;

        $url = null;
        if (fifu_is_on('fifu_video')) {
            $url = get_term_meta($term_id, 'fifu_video_url', true);
            $url = $url ? fifu_video_img_large($url) : null;
        }
        $url = $url ? $url : get_term_meta($term_id, 'fifu_image_url', true);

        // delete
        if (!$url) {
            if ($has_fifu_attachment) {
                wp_delete_attachment($att_id);
                update_term_meta($term_id, 'thumbnail_id', 0);
            }
        } else {
            // update
            $alt = get_term_meta($term_id, 'fifu_image_alt', true);
            if ($has_fifu_attachment) {
                update_post_meta($att_id, '_wp_attached_file', ';' . $url);
                update_post_meta($att_id, '_wp_attachment_image_alt', $alt);
                $this->wpdb->update($this->posts, $set = array('guid' => $url, 'post_title' => $alt), $where = array('id' => $att_id), null, null);
            }
            // insert
            else {
                $value = $this->get_formatted_value($url, $alt, $term_id);
                $this->insert_attachment_by($value);
                $att_id = $this->wpdb->insert_id;
                update_term_meta($term_id, 'thumbnail_id', $att_id);
                update_post_meta($att_id, '_wp_attached_file', ';' . $url);
                update_post_meta($att_id, '_wp_attachment_image_alt', $alt);
                update_post_meta($att_id, '_wp_attachment_metadata', 'a:1:{i:0;s:15:"giaiphapmmo.net";}'); // giaiphapmmo add
                $attachments = $this->get_ctgr_attachments_without_post($term_id);
                if ($attachments) {
                    $this->delete_attachments($attachments);
                    $this->delete_attachment_meta_url_and_alt($attachments);
                }
            }
        }
    }

    /* default url */

    function create_attachment($url) {
        $value = $this->get_formatted_value($url, null, null);
        $this->insert_attachment_by($value);
        return $this->wpdb->insert_id;
    }

    function set_default_url() {
        $att_id = get_option('fifu_default_attach_id');
        if (!$att_id)
            return;
        $value = null;
        foreach ($this->get_posts_without_featured_image() as $res) {
            $aux = "(" . $res->id . ", '_thumbnail_id', " . $att_id . ")";
            $value = $value ? $value . ',' . $aux : $aux;
        }
        if ($value) {
            $this->insert_default_thumbnail_id($value);
            update_post_meta($att_id, '_wp_attached_file', ';' . get_option('fifu_default_url'));
        }
    }

    function update_default_url($url) {
        $att_id = get_option('fifu_default_attach_id');
        if ($url != wp_get_attachment_url($att_id)) {
            $this->wpdb->update($this->posts, $set = array('guid' => $url), $where = array('id' => $att_id), null, null);
            update_post_meta($att_id, '_wp_attached_file', ';' . $url);
        }
    }

    function delete_default_url() {
        $att_id = get_option('fifu_default_attach_id');
        wp_delete_attachment($att_id);
        delete_option('fifu_default_attach_id');
        $this->wpdb->delete($this->postmeta, array('meta_key' => '_thumbnail_id', 'meta_value' => $att_id));
    }

    function add_default_image($post_id) {
        if (fifu_is_off('fifu_enable_default_url'))
            return;
        $att_id = get_option('fifu_default_attach_id');
        $value = "(" . $post_id . ", '_thumbnail_id', " . $att_id . ")";
        $this->insert_default_thumbnail_id($value);
        update_post_meta($att_id, '_wp_attached_file', ';' . get_option('fifu_default_url'));
    }

    /* delete post */

    function before_delete_post($post_id) {
        $default_url_enabled = fifu_is_on('fifu_enable_default_url');
        $default_att_id = $default_url_enabled ? get_option('fifu_default_attach_id') : null;
        $result = $this->get_featured_and_gallery_ids($post_id);
        if ($result) {
            $ids = explode(',', $result[0]->ids);
            $value = null;
            foreach ($ids as $id) {
                if ($id && $id != $default_att_id)
                    $value = ($value == null) ? $id : $value . ',' . $id;
            }
            if ($value) {
                $this->delete_attachments($value);
                $this->delete_attachment_meta_url_and_alt($value);
            }
        }
    }

    function delete_category_image($post_id) {
        if (fifu_is_off('fifu_auto_category'))
            return;

        foreach ($this->get_category_id($post_id) as $i) {
            $term_id = $i->term_id;
            if ($term_id) {
                $this->delete_image_url_category($term_id);
                $aux = $this->get_category_thumbnail_id($term_id);
                $att_id = $aux->meta_value;
                wp_delete_attachment($att_id);
                update_term_meta($term_id, 'thumbnail_id', 0);
            }
        }
    }

    /* clean metadata */

    function enable_clean() {
        $this->delete_metadata();
        $this->delete_duplicated_category_url();
        wp_delete_attachment(get_option('fifu_fake_attach_id'));
        wp_delete_attachment(get_option('fifu_default_attach_id'));
        delete_option('fifu_fake_attach_id');
        fifu_disable_fake();
        update_option('fifu_fake', 'toggleoff', 'no');
        update_option('fifu_fake_created', false, 'no');
    }

}

/* rest api */

function fifu_db_insert($post_id, $urls, $alts) {
    $db = new FifuDb();
    if ($urls[0])
        $db->insert_attachment_list($post_id, $urls, $alts);
    else
        $db->add_default_image($post_id);
}

function fifu_db_update($post_id, $urls, $alts) {
    $db = new FifuDb();
    if ($urls[0])
        $db->update_attachment_list($post_id, $urls, $alts);
    else
        $db->add_default_image($post_id);
}

function fifu_db_image_ids($meta_key, $meta_value, $product_id) {
    $db = new FifuDb();
    if ($meta_key && $meta_value)
        return $db->get_image_ids($meta_key, $meta_value, $product_id);
}

function fifu_db_variantion_products($post_id) {
    $db = new FifuDb();
    return $db->get_variantion_products($post_id);
}

/* auto set category image */

function fifu_db_insert_auto_category_image() {
    $db = new FifuDb();
    $db->insert_auto_category_image();
}

/* fake internal featured image */

function fifu_db_insert_attachment_gallery() {
    $db = new FifuDb();
    // die('fifu_db_insert_attachment_gallery');
    $db->insert_attachment_gallery();
}

function fifu_db_insert_attachment_category() {
    $db = new FifuDb();
    $db->insert_attachment_category();
}

function fifu_db_insert_attachment() {
    $db = new FifuDb();
    $db->insert_attachment();
}

function fifu_db_delete_attachment_category() {
    $db = new FifuDb();
    $db->delete_attachment_category();
}

function fifu_db_delete_attachment() {
    $db = new FifuDb();
    $db->delete_attachment();
}

/* product variation gallery */

function fifu_db_get_variation_gallery($product_id, $attributes) {
    $db = new FifuDb();
    return $db->get_variation_gallery($product_id, $attributes);
}

/* auto set: update all */

function fifu_db_update_all() {
    $db = new FifuDb();
    return $db->update_all();
}

/* change max URL length */

function fifu_db_change_url_length() {
    $db = new FifuDb();
    $db->change_url_length();
}

/* clean depracted data */

function fifu_db_delete_deprecated_data() {
    $db = new FifuDb();
    $db->delete_deprecated_options();
}

/* dimensions: save all */

function fifu_db_save_dimensions_all() {
    $db = new FifuDb();
    return $db->save_dimensions_all();
}

/* dimensions: clean all */

function fifu_db_clean_dimensions_all() {
    $db = new FifuDb();
    return $db->clean_dimensions_all();
}

/* dimensions: amount */

function fifu_db_missing_dimensions() {
    $db = new FifuDb();
    $aux = $db->get_count_posts_without_dimensions()[0];
    return $aux ? $aux->amount : -1;
}

/* clean metadata */

function fifu_db_enable_clean() {
    $db = new FifuDb();
    $db->enable_clean();
}

/* set autoload to no */

function fifu_db_update_autoload() {
    $db = new FifuDb();
    $db->update_autoload();
}

/* save post */

function fifu_db_update_fake_attach_id($post_id) {
    $db = new FifuDb();
    $db->update_fake_attach_id($post_id);
}

function fifu_db_update_fake_attach_id_gallery($post_id) { // giaiphapmmo ham nay lam loi luu anh upload len site
    $db = new FifuDb();
    $db->update_fake_attach_id_gallery($post_id);
}

/* save category */

function fifu_db_ctgr_update_fake_attach_id($term_id) {
    $db = new FifuDb();
    $db->ctgr_update_fake_attach_id($term_id);
}

/* default url */

function fifu_db_create_attachment($url) {
    $db = new FifuDb();
    return $db->create_attachment($url);
}

function fifu_db_set_default_url() {
    $db = new FifuDb();
    return $db->set_default_url();
}

function fifu_db_update_default_url($url) {
    $db = new FifuDb();
    return $db->update_default_url($url);
}

function fifu_db_delete_default_url() {
    $db = new FifuDb();
    return $db->delete_default_url();
}

/* delete post */

function fifu_db_before_delete_post($post_id) {
    $db = new FifuDb();
    $db->before_delete_post($post_id);
}

function fifu_db_delete_category_image($post_id) {
    $db = new FifuDb();
    $db->delete_category_image($post_id);
}

/* number of posts */

function fifu_db_number_of_posts() {
    $db = new FifuDb();
    return $db->get_number_of_posts();
}

/* all urls */

function fifu_db_get_featured_and_gallery_urls($post_id) {
    $db = new FifuDb();
    return $db->get_featured_and_gallery_urls($post_id);
}

function fifu_db_delete_featured_and_gallery_urls($post_id) {
    $db = new FifuDb();
    return $db->delete_featured_and_gallery_urls($post_id);
}

/* speed up */

function fifu_get_all_urls() {
    $db = new FifuDb();
    return $db->get_all_urls();
}

