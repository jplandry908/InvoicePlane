<script>
    $(function () {
        $('#quote_tax_submit').click(function () {
            var tax_rate_id = $('#tax_rate_id').val();
            if ('0' == tax_rate_id) return;
            show_loader(); // Show spinner
            $.post("<?php echo site_url('quotes/ajax/save_quote_tax_rate'); ?>", {
                    quote_id: <?php echo $quote_id; ?>,
                    tax_rate_id: tax_rate_id,
                    include_item_tax: $('#include_item_tax').val()
                },
                function (data) {
                    var response = json_parse(data, <?php echo (int) IP_DEBUG; ?>);
                    if (response.success === 1) {
                        window.location = "<?php echo site_url('quotes/view'); ?>/" + <?php echo $quote_id; ?>;
                    }
                    // close_loader(); No error returned (show go to wiki if not success after 10s)  Todo: else // The validation was not successful
                }
            );
        });
    });
</script>

<div id="add-quote-tax" class="modal modal-lg" role="dialog" aria-labelledby="modal_add_quote_tax" aria-hidden="true">
    <form class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="panel-title"><?php _trans('add_quote_tax'); ?></h4>
        </div>
        <div class="modal-body">

            <div class="form-group">
                <label for="tax_rate_id">
                    <?php _trans('tax_rate'); ?>
                </label>

                <div class="controls">
                    <select name="tax_rate_id" id="tax_rate_id" class="form-control simple-select" required>
                        <option value="0"><?php _trans('none'); ?></option>
                        <?php foreach ($tax_rates as $tax_rate) { ?>
                            <option value="<?php echo $tax_rate->tax_rate_id; ?>">
                                <?php echo format_amount($tax_rate->tax_rate_percent) . '% - ' . htmlsc($tax_rate->tax_rate_name); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="include_item_tax">
                    <?php _trans('tax_rate_placement'); ?>
                </label>

                <div class="controls">
                    <select name="include_item_tax" id="include_item_tax" class="form-control simple-select" required>
                        <option value="0">
                            <?php _trans('apply_before_item_tax'); ?>
                        </option>
                        <option value="1">
                            <?php _trans('apply_after_item_tax'); ?>
                        </option>
                    </select>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <div class="btn-group">
                <button class="btn btn-success" id="quote_tax_submit" type="button">
                    <i class="fa fa-check"></i> <?php _trans('submit'); ?>
                </button>
                <button class="btn btn-danger" type="button" data-dismiss="modal">
                    <i class="fa fa-times"></i> <?php _trans('cancel'); ?>
                </button>
            </div>
        </div>

    </form>

</div>
