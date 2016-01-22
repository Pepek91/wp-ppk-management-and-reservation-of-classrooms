<?php

/*
  Plugin Name: Management and reservation of classrooms
  Plugin URI:  https://github.com/Pepek91
  Description: This describes my plugin in a short sentence
  Version:     0.1
  Author:      Michał Stępień
  Author URI:  https://github.com/Pepek91
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') or die('No script kiddies please!');

require_once (plugin_dir_path(__FILE__) . 'includes/wp-ppk-classrooms-cpt.php');
require_once (plugin_dir_path(__FILE__) . 'includes/wp-ppk-classrooms-init-and-deinit.php');
require_once (plugin_dir_path(__FILE__) . 'includes/wp-ppk-classrooms-fields.php');
require_once (plugin_dir_path(__FILE__) . 'includes/wp-ppk-classrooms-reservation.php');

function wp_ppk_admin_enqueue_scripts() {
    global $pagenow, $typenow;
    $screen = get_current_screen();

    if ($pagenow != 'edit.php' || $typenow != 'ppk_classroom' || $screen->id != 'ppk_classroom_page_add-classroom-reservations-menu') {
        return;
    }
    ppk_include_datetimepicker_multiselect_js();
    ppk_include_datetimepicker_multiselect_css();
}

add_action('admin_enqueue_scripts', 'wp_ppk_admin_enqueue_scripts');

register_activation_hook(__FILE__, 'wp_ppk_classroom_install');
register_deactivation_hook(__FILE__, 'wp_ppk_classroom_uninstall');

function ppk_include_datetimepicker_multiselect_js() {

    wp_register_script('jquery.datetimepicker.full', plugins_url('/js/jquery.datetimepicker.full.js', __FILE__));
    wp_register_script('jquery.multiple.select', plugins_url('/js/jquery.multiple.select.js', __FILE__));

    wp_enqueue_script('jquery.datetimepicker.full');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery.multiple.select');
}

function ppk_include_datetimepicker_multiselect_css() {
    wp_register_style('jquery.datetimepicker.full.css', plugins_url('/css/jquery.datetimepicker.css', __FILE__));
    wp_register_style('jquery.multiple.full.select.css', plugins_url('/css/multiple-select.css', __FILE__));

    wp_enqueue_style('jquery.datetimepicker.full.css');
    wp_enqueue_style('jquery.multiple.full.select.css');
}

function ppk_check_reservation($values) {

    $format = 'Y/m/d H:i:s';

    global $wpdb;
    $reservation_id = $values['id'];

    $from_date = $values['date_from'];
    $to_date = $values['date_to'];
    $date_from_dt = new DateTime($from_date);
    $date_to_dt = new DateTime($to_date);
    $cyclical = $values['cyclical'];
    $date_cyclical_to_dt = new DateTime($values['cyclical_untile']);

    if ($date_from_dt < $date_to_dt || ($cyclical && $date_to_dt < $date_cyclical_to_dt)) {
        $dates = array(
            array('date_from' => $date_from_dt->format($format), 'date_to' => $date_to_dt->format($format))
        );
        if ($values['cyclical']) {
            $lower = true;
            while ($lower) {
                $date_from_dt->add(new DateInterval('P7D'));
                $date_to_dt->add(new DateInterval('P7D'));
                if ($date_to_dt < $date_cyclical_to_dt) {

                    $dates = array_merge($dates, array(array('date_from' => $date_from_dt->format($format), 'date_to' => $date_to_dt->format($format))));
                } else {
                    $lower = false;
                }
            }
        }
        $sql_time = '( ';

        foreach ($dates as $from_to) {
            $from_date = esc_sql($from_to['date_from']);
            $to_date = esc_sql($from_to['date_to']);
            $sql_time .= "((crd.date_from BETWEEN '" . $from_date . "' AND '" . $to_date . "')"
                    . " OR (date_to BETWEEN '" . $from_date . "' AND '" . $to_date . "')"
                    . " OR (date_from < '" . $from_date . "' AND date_to > '" . $to_date . "')) OR";
        }
        $sql_time = trim($sql_time, 'OR');
        $sql_time .= ' )';

        $classroom_id = $values['classroom_id'];

        $sql = '';

        $sql = 'SELECT cr.id FROM ' . $wpdb->prefix . 'ppk_classroom_reservations AS cr
        INNER JOIN ' . $wpdb->prefix . 'ppk_classroom_reservations_dates AS crd ON crd.reservation_id = cr.id
        WHERE (classroom_id = ' . esc_sql($classroom_id) . ') AND ' . $sql_time;
        if (!empty($reservation_id)) {
            $sql .= ' AND cr.id != ' . esc_sql($reservation_id);
        }

        $result = $wpdb->get_results($sql);
        if (count($result) > 0) {
            $result['avalible'] = false;
        } else {
            $result['avalible'] = true;
            $result['dates'] = $dates;
        }
    } else {
        $result['avalible'] = false;
    }
    return $result;
}

function ppk_add_update_reservation() {

    $response = array();

    if (count($_REQUEST) > 0) {
        global $wpdb;

        //@TODO check data

        $reservation_id = $_REQUEST['reservation_id'];
        $classroom_id = $_REQUEST['classroom_id'];
        $from_date = $_REQUEST['date_from'];
        $to_date = $_REQUEST['date_to'];
        $reserved_by = $_REQUEST['reserved_by'];
        $cyclical = $_REQUEST['cyclical'] == 'true';
        $cyclical_to = $_REQUEST['cyclical_to'];

        $values = array(
            'classroom_id' => $classroom_id,
            'date_from' => $from_date,
            'date_to' => $to_date,
            'reserved_by' => $reserved_by,
            'id' => $reservation_id
        );

        if ($cyclical) {
            $values['cyclical'] = 1;
            $values['cyclical_untile'] = $cyclical_to;
        }
        $result = ppk_check_reservation($values);
        unset($values['date_from']);
        unset($values['date_to']);
        unset($values['id']);

        if ($result['avalible']) {
            if (empty($reservation_id)) {
                $wpdb->insert($wpdb->prefix . 'ppk_classroom_reservations', $values);
                $reservation_id = $wpdb->insert_id;
            } else {
                $wpdb->update(
                        $wpdb->prefix . 'ppk_classroom_reservations', $values, array('id' => $reservation_id)
                );
                $wpdb->delete($wpdb->prefix . 'ppk_classroom_reservations_dates', array('reservation_id' => $reservation_id));
            }

            if ($reservation_id !== 0) {
                foreach ($result['dates'] as $from_to) {
                    $values = array_merge($from_to, array('reservation_id' => $reservation_id));
                    $wpdb->insert($wpdb->prefix . 'ppk_classroom_reservations_dates', $values);
                }
            } else {
                $response['msg'] = 'Could not insert!';
            }

            $response['reservation_id'] = $reservation_id;
        } else {
            $response['msg'] = 'Already Reserved!';
        }
    }
    wp_send_json($response);
}

add_action('wp_ajax_ppk_add_update_reservation', 'ppk_add_update_reservation');

function ppk_search_reservation() {
    global $wpdb;

    $search_text = esc_sql($_REQUEST['searchtext']);

    $sql = "SELECT DISTINCT p.post_title AS classroom, crd.date_from, crd.date_to, cr.reserved_by, cr.reserved_at, cr.id AS reservation_id FROM " . $wpdb->prefix . "ppk_classroom_reservations AS cr "
            . "INNER JOIN " . $wpdb->prefix . "ppk_classroom_reservations_dates AS crd ON cr.id = crd.reservation_id "
            . "INNER JOIN " . $wpdb->prefix . 'posts AS p ON cr.classroom_id = p.ID ';

    if (!empty($search_text)) {
        $sql .= 'WHERE p.post_title LIKE \'%' . $search_text . '%\'';
    }

    $sql .= "ORDER BY reserved_at desc ";

    $reservations = $wpdb->get_results($sql);

    $msg = "<div id='content_top'></div>";
    if (count($reservations)) {
        $msg .= '<table class="wp-list-table widefat fixed bookmarks" cellspacing="0">
                      <thead>
                        <tr>
                          <th>Classroom</th>
                          <th>From Date</th>
                          <th>To Date</th>
                          <th>Reserved By</th>
                          <th>Reserved At</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tr>';
        foreach ($reservations as $reservation) {
            $msg .= '<tr class="alternate">
                                <td>' . $reservation->classroom . '</td>
                                <td>' . $reservation->date_from . '</td>
                                <td>' . $reservation->date_to . '</td>
                                <td>' . $reservation->reserved_by . '</td>
                                <td>' . $reservation->reserved_at . '</td>

                                <td>
                                  ';
            $msg .= '<a href="' . site_url() . '/wp-admin/edit.php?post_type=ppk_classroom&page=add-classroom-reservations-menu&calltype=editreservation&id=' . $reservation->reservation_id . '">Edit</a>
                                  &nbsp;&nbsp;&nbsp;<a style="cursor:pointer;" id="delete_reservation">Delete</a>
                                  <input type="hidden" id="reservation_id"  name="reservation_id" value="' . $reservation->reservation_id . '" />
                                </td>
                            </tr>';
        }
        $msg .= '</tr>
                            <tfoot>
                              <tr>
                          <th>Classroom</th>
                          <th>From Date</th>
                          <th>To Date</th>
                          <th>Reserved By</th>
                          <th>Reserved At</th>
                          <th></th>
                        </tr>
                            </tfoot>
                          </table>';
    } else {
        $msg .= '<div style="padding:80px;color:red;">Sorry! No Data Found!</div>';
    }
    $msg = "<div class='data'>" . $msg . "</div>";
    wp_send_json(array('html' => $msg));
}

add_action('wp_ajax_ppk_search_reservation', 'ppk_search_reservation');

function ppk_delete_reservation() {
    $ret = array('success' => true);
    if (count($_POST) > 0) {
        global $table_prefix, $wpdb;
        $reservation_id = $_REQUEST['reservation_id'];

        $ret['aff_res_rows'] = $wpdb->delete($wpdb->prefix . 'ppk_classroom_reservations', array('id' => $reservation_id));
        $ret['aff_time_rows'] = $wpdb->delete($wpdb->prefix . 'ppk_classroom_reservations_dates', array('reservation_id' => $reservation_id));
    } else {
        $ret['success'] = false;
    }
    wp_send_json($ret);
}

add_action('wp_ajax_ppk_delete_reservation', 'ppk_delete_reservation');
