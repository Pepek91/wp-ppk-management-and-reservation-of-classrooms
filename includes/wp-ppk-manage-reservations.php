<?php ?>
<script type="text/javascript">

    function ppk_search_for_reservation() {
        var searchtext = jQuery('#txt_search_reservation').val();
        jQuery.ajax({
            method: "POST",
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'ppk_search_reservation',
                searchtext: searchtext
            },
            success: function (data)
            {
            },
            error: function (s, i, error) {
                console.log(error);
            }
        }).done(function (data) {
            jQuery("#inner_content").html(data.html.trim());
        });
    }

    jQuery(document).ready(function () {

        jQuery('#btn_search_reservations').on('click', ppk_search_for_reservation);

        ppk_search_for_reservation();

        jQuery('#inner_content').delegate("#delete_reservation", 'click', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure want to delete')) {
                return false;
            }
            var reservation_id = jQuery(this).parent().children('#reservation_id').val();
            jQuery.ajax({
                method: "POST",
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                data: {
                    action: 'ppk_delete_reservation',
                    reservation_id: reservation_id
                },
                success: function (data) {
                    console.log(data);
                    ppk_search_for_reservation();
                },
                error: function (s, i, error) {
                    console.log(error);
                    alert('Something went wrong!');
                }
            });
        });
        jQuery("#search_reservation").submit(function (event) {
            event.preventDefault();
            ppk_search_for_reservation();
        });
    });

</script>
<div class="wrapper">
    <div class="wrap" style="float:left; width:100%;">
        <div id="icon-options-general" class="icon32"></div>
        <div style="width:70%;float:left;"><h2>Classrooms Reservations</h2></div>
        <div style="width:29%;float:left;margin-top:15px;">
            <form id="search_reservation" method="post" action="">
                <input type="text" name="txt_search_reservation" id="txt_search_reservation" value="" style="width:250px;height:40px;" />
                <input type="button" id="btn_search_reservations" name="btn_search_reservations" value="Search Reservation" />
            </form>
        </div>
        <div class="main_div">
            <div class="metabox-holder" style="width:98%; float:left;">
                <div id="namediv" class="stuffbox" style="width:99%;">
                    <h3 class="top_bar">Manage Reservations</h3>
                    <div id="inner_content">		
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
