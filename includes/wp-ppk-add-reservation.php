<?php
global $wpdb;
?>
<script type="text/javascript">

    function manage_cyclical_disable_state() {
        if (jQuery('#ppk_res_cyclical').is(":checked")) {
            jQuery('#ppk_res_cyclical_to').removeAttr('disabled');
        } else {
            jQuery('#ppk_res_cyclical_to').attr('disabled', 'disabled');
        }
    }
    jQuery(function () {

        jQuery('#ppk_res_date_from').datetimepicker({
            lang: 'en',
            step: 01
        });

        jQuery('#ppk_res_date_to').datetimepicker({
            lang: 'en',
            step: 01
        });

        jQuery("#ppk_res_cyclical_to").datetimepicker({
            timepicker: false,
            lang: 'en',
            format: 'Y/m/d'
        });

        jQuery('#ppk_res_cyclical').change(manage_cyclical_disable_state);
    });
    function gen_getUrlVars()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }
    jQuery(document).ready(function () {
        var calltype = gen_getUrlVars()["calltype"];
        if (calltype) {
            if (calltype == 'editreservation') {
                <?php
                $id = "";
                if (isset($_REQUEST['id'])) {
                    $id = $_REQUEST['id'];

                    global $wpdb;
                    $sql = "SELECT * FROM " . $wpdb->prefix . "ppk_classroom_reservations AS cr "
                            . "INNER JOIN " . $wpdb->prefix . "ppk_classroom_reservations_dates AS crd ON crd.reservation_id = cr.id "
                            . "WHERE cr.id=" . esc_sql($id) . " "
                            . "ORDER BY crd.date_from ASC "
                            . "LIMIT 1";
                    $result = $wpdb->get_results($sql);
                    ?>
                    var reservation = <?php if (count($result)) echo json_encode($result[0]); ?>;
                    jQuery('#reservation_id').val(reservation['id']);
                    jQuery('#classroom_select').val(reservation['classroom_id']);

                    jQuery('#ppk_res_date_from').val(reservation['date_from']);
                    jQuery('#ppk_res_date_to').val(reservation['date_to']);

                    if (reservation['cyclical'])
                    {
                        jQuery('#ppk_res_cyclical').prop('checked', true);
                        jQuery('#ppk_res_cyclical_to').val(reservation['cyclical_untile']);
                        manage_cyclical_disable_state();
                    }
    <?php
            }
?>
            }
        }
        jQuery('#reservation_form').on('submit', function (e) {
            e.preventDefault();
            ppk_save_reservation();
        });
    });
    function ppk_save_reservation() {
        var reservation_id = jQuery('#reservation_id').val();

        var classroom_id = jQuery('#classroom_select').val();

        var from_date = jQuery('#ppk_res_date_from').val();
        var to_date = jQuery('#ppk_res_date_to').val();

        var cyclical = jQuery('#ppk_res_cyclical').is(':checked');
        var cyclical_to = jQuery('#ppk_res_cyclical_to').val();

        var reservationby = jQuery('#txtreservationby').val();
        if (from_date === "") {
            alert('Please choose a from date.');
            return;
        } else if (to_date === "") {
            alert('Please choose a to date.');
            return;
        } else if (cyclical === true) {
            if (cyclical_to === "") {
                alert('Please choose a cyclical to date as you want reservation to be cyclical.');
                return;
            }
        }
        data = {
            action: 'ppk_add_update_reservation',
            reservation_id: reservation_id,
            classroom_id: classroom_id,
            date_from: from_date,
            date_to: to_date,
            reserved_by: reservationby,
            cyclical: cyclical,
            cyclical_to: cyclical_to
        };
        jQuery.ajax({
            method: "POST",
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: data,
            success: function (data) {
                if (typeof data.reservation_id !== 'undefined' && data.reservation_id !== 0)
                {
                    jQuery('#reservation_id').val(data.reservation_id);
                    alert('Successfully added');
                } else
                {
                    alert(data.msg);
                }
            },
            error: function (s, i, e) {
                console.log(s['responseText']);
            }
        });
    }
</script>

<?php $current_user = wp_get_current_user(); ?>	  
<div id="add_reservation" class="wrap">
    <h2>Classroom Reservation</h2>
    <div class="metabox-holder" style="width:50%;">
        <div id="namediv" class="stuffbox" style="width:99%;">
            <h3 class="top_bar">Add Reservation</h3>
            <form id="reservation_form" action="" method="post" novalidate="novalidate">
                <table style="margin:10px;width:100%;">
                    <tr>
                        <td>Classroom</td>
                        <td id="multi_rooms_select">
                            <select id="classroom_select" class="select">
                                <?php
                                $sql_classroom = "SELECT ID, post_title FROM " . $wpdb->prefix . "posts AS p WHERE p.post_status = 'publish' and p.post_type='ppk_classroom'";
                                $classrooms = $wpdb->get_results($sql_classroom);
                                foreach ($classrooms as $classroom) {
                                    ?>
                                    <option value="<?php echo $classroom->ID; ?>"><?php echo $classroom->post_title; ?></option>
                                <?php } ?>
                            </select><span style="color:red;">*</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            From Date:
                        </td>
                        <td>
                            <input type="text" id="ppk_res_date_from" name="ppk_res_date_from" value="" style="width:230px;" /><span style="color:red;">*</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            To Date:
                        </td>
                        <td>
                            <input type="text" id="ppk_res_date_to" name="ppk_res_date_to" value="" style="width:230px;" /><span style="color:red;">*</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Cyclical:
                        </td>
                        <td>
                            <input type="checkbox" id="ppk_res_cyclical" name="ppk_res_cyclical" value="" style="width:230px;" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Cyclical Untile:
                        </td>
                        <td>
                            <input type="text" id="ppk_res_cyclical_to" name="ppk_res_cyclical_to"  value="" style="width:230px;" disabled/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Reserved By:
                        </td>
                        <td>
                            <input type="text" readonly="readonly" id="txtreservationby" name="txtreservationby" value="<?php echo $current_user->display_name; ?>" style="width:230px;"/>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" id="btn_add_reservation" name="btn_add_reservation" value="Add/Update Reservation" style="width:230px;cursor: pointer;"/>
                            <input type="hidden" id="reservation_id" name="reservation_id" value="" style="width:150px;"/>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>