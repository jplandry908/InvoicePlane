<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php _trans('invoices'); ?>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[default_invoice_group]">
                                <?php _trans('default_invoice_group'); ?>
                            </label>
                            <select name="settings[default_invoice_group]" id="settings[default_invoice_group]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($invoice_groups as $invoice_group) {
?>
                                <option value="<?php echo $invoice_group->invoice_group_id; ?>"
                                    <?php check_select(get_setting('default_invoice_group'), $invoice_group->invoice_group_id); ?>>
                                    <?php echo $invoice_group->invoice_group_name; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[default_invoice_terms]">
                                <?php _trans('default_terms'); ?>
                            </label>
                            <textarea name="settings[default_invoice_terms]" id="settings[default_invoice_terms]"
                                      class="form-control" rows="4"
                                ><?php echo get_setting('default_invoice_terms', '', true); ?></textarea>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[invoice_default_payment_method]">
                                <?php _trans('default_payment_method'); ?>
                            </label>
                            <select name="settings[invoice_default_payment_method]" class="form-control simple-select"
                                id="settings[invoice_default_payment_method]" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($payment_methods as $payment_method) {
?>
                                <option value="<?php echo $payment_method->payment_method_id; ?>"
                                    <?php check_select($payment_method->payment_method_id, get_setting('invoice_default_payment_method')) ?>>
                                    <?php echo $payment_method->payment_method_name; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[invoices_due_after]">
                                <?php _trans('invoices_due_after'); ?>
                            </label>
                            <input type="number" name="settings[invoices_due_after]" id="settings[invoices_due_after]"
                                   class="form-control" value="<?php echo get_setting('invoices_due_after'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="settings[generate_invoice_number_for_draft]">
                                <?php _trans('generate_invoice_number_for_draft'); ?>
                            </label>
                            <select name="settings[generate_invoice_number_for_draft]" class="form-control simple-select"
                                    id="settings[generate_invoice_number_for_draft]" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    <?php _trans('no'); ?>
                                </option>
                                <option value="1" <?php check_select(get_setting('generate_invoice_number_for_draft'), '1'); ?>>
                                    <?php _trans('yes'); ?>
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[einvoicing]">
                                <?php _trans('einvoicing_enable'); ?>
                            </label>
                            <select name="settings[einvoicing]" id="settings[einvoicing]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    <?php _trans('no'); ?>
                                </option>
                                <option value="1" <?php check_select(get_setting('einvoicing'), '1'); ?>>
                                    <?php _trans('yes'); ?>
                                </option>
                            </select>
                            <p class="help-block">
                                <?php _trans('einvoicing_enable_help'); ?>
                                <a href="https://github.com/InvoicePlane/InvoicePlane-e-invoices" target="_blank">InvoicePlane-e-invoices</a>
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?php _trans('pdf_settings'); ?>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[mark_invoices_sent_pdf]">
                                <?php _trans('mark_invoices_sent_pdf'); ?>
                            </label>
                            <select name="settings[mark_invoices_sent_pdf]" id="settings[mark_invoices_sent_pdf]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    <?php _trans('no'); ?>
                                </option>
                                <option value="1" <?php check_select(get_setting('mark_invoices_sent_pdf'), '1'); ?>>
                                    <?php _trans('yes'); ?>
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[invoice_pre_password]">
                                <?php _trans('invoice_pre_password'); ?>
                            </label>
                            <input type="text" name="settings[invoice_pre_password]" id="settings[invoice_pre_password]"
                                   class="form-control"
                                   value="<?php echo get_setting('invoice_pre_password', '', true); ?>">
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[pdf_watermark]">
                                <?php _trans('pdf_watermark'); ?>
                            </label>
                            <select name="settings[pdf_watermark]" id="settings[pdf_watermark]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    <?php _trans('no'); ?>
                                </option>
                                <option value="1" <?php check_select(get_setting('pdf_watermark'), '1'); ?>>
                                    <?php _trans('yes'); ?>
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><?php _trans('invoice_logo'); ?></label>
<?php
if (get_setting('invoice_logo')) {
?>
                                <br/>
                                <img class="personal_logo"
                                     src="<?php echo base_url(); ?>uploads/<?php echo get_setting('invoice_logo'); ?>">
                                <br>
                                <?php echo anchor('settings/remove_logo/invoice', trans('remove_logo')); ?><br/>
<?php
}
?>
                            <input type="file" name="invoice_logo" size="40" class="form-control"/>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?php _trans('invoice_templates'); ?>
            </div>
            <div class="panel-body">
                <div class="help-block">
                    <?php _trans('invoice_templates_info'); ?>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[pdf_invoice_template]">
                                <?php _trans('default_pdf_template'); ?>
                            </label>
                            <select name="settings[pdf_invoice_template]" id="settings[pdf_invoice_template]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($pdf_invoice_templates as $invoice_template) {
?>
                                <option value="<?php echo $invoice_template; ?>"
                                    <?php check_select(get_setting('pdf_invoice_template'), $invoice_template); ?>>
                                    <?php echo $invoice_template; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[pdf_invoice_template_paid]">
                                <?php _trans('pdf_template_paid'); ?>
                            </label>
                            <select name="settings[pdf_invoice_template_paid]" id="settings[pdf_invoice_template_paid]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($pdf_invoice_templates as $invoice_template) {
?>
                                <option value="<?php echo $invoice_template; ?>"
                                    <?php check_select(get_setting('pdf_invoice_template_paid'), $invoice_template); ?>>
                                    <?php echo $invoice_template; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[pdf_invoice_template_overdue]">
                                <?php _trans('pdf_template_overdue'); ?>
                            </label>
                            <select name="settings[pdf_invoice_template_overdue]" class="form-control simple-select"
                                    id="settings[pdf_invoice_template_overdue]" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($pdf_invoice_templates as $invoice_template) {
?>
                                    <option value="<?php echo $invoice_template; ?>"
                                        <?php check_select(get_setting('pdf_invoice_template_overdue'), $invoice_template); ?>>
                                        <?php echo $invoice_template; ?>
                                    </option>
<?php
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[public_invoice_template]">
                                <?php _trans('default_public_template'); ?>
                            </label>
                            <select name="settings[public_invoice_template]" id="settings[public_invoice_template]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($public_invoice_templates as $invoice_template) {
?>
                                <option value="<?php echo $invoice_template; ?>"
                                    <?php check_select(get_setting('public_invoice_template'), $invoice_template); ?>>
                                    <?php echo $invoice_template; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[email_invoice_template]">
                                <?php _trans('default_email_template'); ?>
                            </label>
                            <select name="settings[email_invoice_template]" id="settings[email_invoice_template]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($email_templates_invoice as $email_template) {
?>
                                <option value="<?php echo $email_template->email_template_id; ?>"
                                    <?php check_select(get_setting('email_invoice_template'), $email_template->email_template_id); ?>>
                                    <?php echo $email_template->email_template_title; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[email_invoice_template_paid]">
                                <?php _trans('email_template_paid'); ?>
                            </label>
                            <select name="settings[email_invoice_template_paid]" id="settings[email_invoice_template_paid]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($email_templates_invoice as $email_template) {
?>
                                <option value="<?php echo $email_template->email_template_id; ?>"
                                    <?php check_select(get_setting('email_invoice_template_paid'), $email_template->email_template_id); ?>>
                                    <?php echo $email_template->email_template_title; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[email_invoice_template_overdue]">
                                <?php _trans('email_template_overdue'); ?>
                            </label>
                            <select name="settings[email_invoice_template_overdue]" class="form-control simple-select"
                                    id="settings[email_invoice_template_overdue]" data-minimum-results-for-search="Infinity">
                                <option value=""><?php _trans('none'); ?></option>
<?php
foreach ($email_templates_invoice as $email_template) {
?>
                                <option value="<?php echo $email_template->email_template_id; ?>"
                                    <?php check_select(get_setting('email_invoice_template_overdue'), $email_template->email_template_id); ?>>
                                    <?php echo $email_template->email_template_title; ?>
                                </option>
<?php
}
?>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[pdf_invoice_footer]">
                                <?php _trans('pdf_invoice_footer'); ?>
                            </label>
                            <textarea name="settings[pdf_invoice_footer]" id="settings[pdf_invoice_footer]"
                                      class="form-control no-margin"><?php echo get_setting('pdf_invoice_footer', '', true); ?></textarea>
                            <p class="help-block"><?php _trans('pdf_invoice_footer_hint'); ?></p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default" id="panel-qr-code-settings">
            <div class="panel-heading">
                <?php _trans('qr_code_settings'); ?>
            </div>
            <div class="panel-body">

<?php
$qr_code = get_setting('qr_code');
?>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input
                                type="hidden"
                                name="settings[qr_code]"
                                value="0"
                            >
                            <input
                                type="checkbox"
                                name="settings[qr_code]"
                                id="settings[qr_code]"
                                value="1"
                                <?php check_select($qr_code, 1, '==', true) ?>
                            >
                            <?php _trans('qr_code_settings_enable'); ?>
                        </label>
                        <p class="help-block"><?php _trans('qr_code_settings_enable_hint'); ?></p>
                    </div>
                </div>

                <div class="row <?php echo $qr_code ? '' : 'hidden'; ?>">
                    <div class="col-xs-12">
                        <p class="alert alert-info no-padding">
                            <i class="fa fa-info"></i><?php _trans('qr_code_settings_enable_hint_users'); ?>&nbsp;<i class="fa fa-qrcode"></i>
                        </p>
                    </div>
                </div>

                <div class="row <?php echo $qr_code ? '' : 'hidden'; ?>">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[qr_code_recipient]">
                                <?php _trans('qr_code_settings_recipient'); ?>
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_recipient]"
                                id="settings[qr_code_recipient]"
                                class="form-control"
                                placeholder="<?php _htmlsc(trans('company')); ?>"
                                value="<?php echo get_setting('qr_code_recipient'); ?>"
                            >
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[qr_code_iban]">
                                <?php _trans('qr_code_settings_iban'); ?>
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_iban]"
                                id="settings[qr_code_iban]"
                                class="form-control"
                                value="<?php echo get_setting('qr_code_iban'); ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="row <?php echo $qr_code ? '' : 'hidden'; ?>">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[qr_code_bic]">
                                <?php _trans('qr_code_settings_bic'); ?>
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_bic]"
                                id="settings[qr_code_bic]"
                                class="form-control"
                                value="<?php echo get_setting('qr_code_bic'); ?>"
                            >
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[qr_code_remittance_text]">
                                <?php _trans('qr_code_settings_remittance_text'); ?>
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_remittance_text]"
                                id="settings[qr_code_remittance_text]"
                                class="form-control taggable"
                                value="<?php echo get_setting('qr_code_remittance_text'); ?>"
                                placeholder="{{{invoice_number}}}"
                            >
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php _trans('qr_code_settings_remittance_text_tags'); ?>
                            </div>
                            <div class="panel-body">
                                <?php $this->layout->load_view('email_templates/template-tags-invoices'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?php _trans('email_settings'); ?>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[automatic_email_on_recur]">
                                <?php _trans('automatic_email_on_recur'); ?>
                            </label>
                            <select name="settings[automatic_email_on_recur]" id="settings[automatic_email_on_recur]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    <?php _trans('no'); ?>
                                </option>
                                <option value="1" <?php check_select(get_setting('automatic_email_on_recur'), '1'); ?>>
                                    <?php _trans('yes'); ?>
                                </option>
                            </select>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?php _trans('other_settings'); ?>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[read_only_toggle]">
                                <?php _trans('set_to_read_only'); ?>
                            </label>
                            <select name="settings[read_only_toggle]" id="settings[read_only_toggle]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="2" <?php check_select(get_setting('read_only_toggle'), '2'); ?>>
                                    <?php _trans('sent'); ?>
                                </option>
                                <option value="3" <?php check_select(get_setting('read_only_toggle'), '3'); ?>>
                                    <?php _trans('viewed'); ?>
                                </option>
                                <option value="4" <?php check_select(get_setting('read_only_toggle'), '4'); ?>>
                                    <?php _trans('paid'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[no_update_invoice_due_date_mail]">
                                <?php _trans('no_update_invoice_due_date_mail'); ?>
                            </label>
                            <select name="settings[no_update_invoice_due_date_mail]" class="form-control simple-select"
                                id="settings[no_update_invoice_due_date_mail]" data-minimum-results-for-search="Infinity">
                                <option value="1" <?php check_select(get_setting('no_update_invoice_due_date_mail'), '1'); ?>>
                                    <?php _trans('yes'); ?>
                                </option>
                                <option value="0" <?php check_select(get_setting('no_update_invoice_due_date_mail'), '0'); ?>>
                                    <?php _trans('no'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
$sumex = get_setting('sumex');
// Set in ipconfig OR is 1 (in db)
if (SUMEX_SETTINGS || $sumex == '1') {
?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?php _trans('sumex_settings'); ?>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[sumex]">
                                <?php _trans('invoice_sumex'); ?>
                            </label>
                            <select name="settings[sumex]" id="settings[sumex]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    <?php _trans('no'); ?>
                                </option>
                                <option value="1" <?php check_select($sumex, '1'); ?>>
                                    <?php _trans('yes'); ?>
                                </option>
                            </select>
                            <p class="help-block"><?php _trans('invoice_sumex_help'); ?></p>
                        </div>

                        <div class="form-group">
                            <label for="settings[sumex_sliptype]">
                                <?php _trans('invoice_sumex_sliptype'); ?>
                            </label>
                            <select name="settings[sumex_sliptype]" id="settings[sumex_sliptype]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
<?php
    $slipTypes = ['esr9', 'esrRed'];
    foreach ($slipTypes as $k => $v) {
?>
                                <option value="<?php echo $k; ?>" <?php check_select(get_setting('sumex_sliptype'), $k) ?>>
                                    <?php _trans('invoice_sumex_sliptype-' . $v); ?>
                                </option>
<?php
    }
?>
                            </select>
                            <p class="help-block"><?php _trans('invoice_sumex_sliptype_help'); ?></p>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[sumex_role]">
                                <?php _trans('invoice_sumex_role'); ?>
                            </label>
                            <select name="settings[sumex_role]" id="settings[sumex_role]"
                                    class="form-control simple-select">
<?php
    $roles = Sumex::ROLES;
    foreach ($roles as $k => $v) {
?>
                                <option value="<?php echo $k; ?>" <?php check_select(get_setting('sumex_role'), $k) ?>>
                                    <?php _trans('invoice_sumex_role_' . $v); ?>
                                </option>
<?php
    }
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[sumex_place]">
                                <?php _trans('invoice_sumex_place'); ?>
                            </label>
                            <select name="settings[sumex_place]" id="settings[sumex_place]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
<?php
    $places = Sumex::PLACES;
    foreach ($places as $k => $v) {
?>
                                <option value="<?php echo $k; ?>" <?php check_select(get_setting('sumex_place'), $k); ?>>
                                    <?php _trans('invoice_sumex_place_' . $v); ?>
                                </option>
<?php
    }
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[sumex_canton]">
                                <?php _trans('invoice_sumex_canton'); ?>
                            </label>
                            <select name="settings[sumex_canton]" id="settings[sumex_canton]"
                                    class="form-control simple-select">
<?php
    $cantons = Sumex::CANTONS;
    foreach ($cantons as $k => $v) {
?>
                                <option value="<?php echo $k; ?>" <?php check_select(get_setting('sumex_canton'), $k); ?>>
                                    <?php echo $v; ?>
                                </option>
<?php
    }
?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
} // End If Sumex
?>

    </div>
</div>
